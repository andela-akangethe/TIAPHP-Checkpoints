<?php

require_once 'vendor/autoload.php';
require 'Orm/NotORM.php';

use Slim\Slim;

// Register autoloader and instantiate Slim
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// Database Configuration
$dbhost = 'localhost';
$dbuser = 'homestead';
$dbpass = 'secret';
// $dbname = 'naiemoji';
$dbname = 'testorm';
$dbmethod = 'mysql:dbname=';

$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db = new NotORM($pdo);

// Home Route
$app->get('/', function () use ($app) {
    $app->response->setStatus(200);
    $app->render('../templates/homepage.html');
});

// Register a user
$app->post('/register', function () use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');

    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $passwordEncryption = md5($password);

    if ($email === $db->users()->where('email', $email)->fetch('email')) {
        echo json_encode(['message' => 'That email address is already in use. Please use another email address']);
    } else {
        $user = [
            'name' => "$name",
            'email' => "$email",
            'password' => "$passwordEncryption",
        ];
        $result = $db->users->insert($user);
        $users = array();
        foreach ($db->users() as $user) {
            $users[] = array(
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            );
        }
        echo json_encode($users, JSON_FORCE_OBJECT);
    }
});

// Login a user
$app->post('/login', function () use ($app, $db) {
    $email = $app->request->post('email');
    $password = $app->request->post('password');

    $new = md5($password);

    if ($email === $db->users()->where('email', $email)->fetch('email')
        &&
        $new === $db->users->where('email', $email)->fetch('password')
        ) {
        $timeNow = new DateTime();
        $user = $db->users()->where('email', $email)->fetch();
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $data = [
          'token' => $token,
          'time'  => $timeNow,
        ];
        $result = $user->update($data);
        echo $token;
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please check your email and password combo',
        ));
    }
});

// Logout a user
$app->post('/logout', function () use ($app, $db) {
    $email = $app->request->post('email');
    $user = $db->users()->where('email', $email);
    if ($user->fetch()) {
        $token = '';
        $data = ['token' => $token];
        $result = $user->update($data);
        echo "You have logged off successfully";
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please verify your email',
        ));
    }
});

// Get all emojis
$app->get('/emojis', function () use ($app, $db) {
    $emojis = array();
    foreach ($db->emojis() as $emoji) {
        $emojis[] = array(
            'id' => $emoji['id'],
            'name' => $emoji['name'],
            'keywords' => $emoji['keywords'],
            'emoji' => $emoji['emoji'],
            'category' => $emoji['category'],
            'created_at' => $emoji['created_at'],
            'updated_at' => $emoji['updated_at'],
            'user' => $emoji['user'],
        );
    }
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($emojis, JSON_FORCE_OBJECT);
});

// Get a single emoji
$app->get('/emojis/:id', function ($id) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $emoji = $db->emojis()->where('id', $id);
    if ($data = $emoji->fetch()) {
        echo json_encode(array(
            'id' => $data['id'],
            'name' => $data['name'],
            'keywords' => $data['keywords'],
            'emoji' => $data['emoji'],
            'category' => $data['category'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'user' => $data['user'],
        ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Emoji ID $id does not exist",
        ));
    }
});

// Add a new emoji
$app->post('/emoji', function () use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $token = $app->request->headers->get('token');

    $name = $app->request()->post('name');
    $emoji = $app->request()->post('emoji');
    $keywords = $app->request()->post('keywords');
    $category = $app->request()->post('category');

    $data = $db->users()->where('token', $token)->fetch('time');

    $result = date($data);

    /*
     * To calculate the difference in time between when you log in and when
     * you perform this request.
     */

    $userLoginTime = new DateTime(date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $result))));
    $currentRequestTime = new DateTime(date('Y-m-d H:i:s'));
    $timeDifference = $userLoginTime->diff($currentRequestTime);
    $timeDifferenceInMinutes = (($timeDifference->h * 60)+($timeDifference->i));

    if ($token = $db->users()->where('token', $token)->fetch('token')) {
        if ($timeDifferenceInMinutes > 300) {
            $user = $db->users()->where('token', $token)->fetch();
            $token = '';
            $data = ['token' => $token];
            $result = $user->update($data);
            echo "login first";
        } else {
            $user = $db->users()->where('token', $token)->fetch('name');

            $emoji = [
                'name'       => "$name",
                'keywords'   => "$keywords",
                'category'   => "$category",
                'user'       => "$user",
                'emoji'      => "$emoji",
            ];

            $result = $db->emojis->insert($emoji);

            echo json_encode(array(
              'id'        => $result['id'],
              'name'      => $result['name'],
              'keywords'  => $result['keywords'],
              'category'  => $result['category'],
              'user'      => $result['user'],
              'emoji'      => $result['emoji'],
            ));
        }
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please provide a valid token',
        ));
    }
});

// Update a emoji
$app->put('/emoji/:id', function ($id) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $token = $app->request->headers->get('token');
    if ($token == null) {
        echo "Please provide a token";
        return false;
    }

    $data = $db->users()->where('token', $token)->fetch('time');

    $result = date($data);

    /*
     * To calculate the difference in time between when you log in and when
     * you perform this request.
     */

    $userLoginTime = new DateTime(date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $result))));
    $currentRequestTime = new DateTime(date('Y-m-d H:i:s'));
    $timeDifference = $userLoginTime->diff($currentRequestTime);
    $timeDifferenceInMinutes = (($timeDifference->h * 60)+($timeDifference->i));

    if ($token = $db->users()->where('token', $token)->fetch('token')) {
        if ($timeDifferenceInMinutes > 300) {
            $user = $db->users()->where('token', $token)->fetch();
            $token = '';
            $data = ['token' => $token];
            $result = $user->update($data);

            echo "login first";
        } else {
            $user = $db->users()->where('token', $token)->fetch('name');
            $emoji = $db->emojis()->where('id', $id);
            if ($emoji->fetch()) {
                $user = $db->users()->where('token', $token)->fetch('name');
                $name = $app->request()->put('name');
                $keywords = $app->request()->put('keywords');
                $category = $app->request()->put('category');

                $emojiUpdate = [
                    'name' => "$name",
                    'keywords' => "$keywords",
                    'category' => "$category",
                    'user' => "$user",
                ];

                $result = $emoji->update($emojiUpdate);
                $emoji = $db->emojis()->where('id', $id);
                $emojis = array();
                foreach ($db->emojis() as $emoji) {
                    $emojis[] = array(
                        'id' => $emoji['id'],
                        'name' => $emoji['name'],
                        'keywords' => $emoji['keywords'],
                        'emoji' => $emoji['emoji'],
                        'category' => $emoji['category'],
                        'created_at' => $emoji['created_at'],
                        'updated_at' => $emoji['updated_at'],
                        'user' => $emoji['user'],
                    );
                }
                echo json_encode($emojis, JSON_FORCE_OBJECT);
            } else {
                echo json_encode(array(
                        'status' => false,
                        'message' => "Emoji id $id does not exist",
                  ));
            }
        }
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please provide a valid token',
        ));
    }
});

// Update a emoji
$app->patch('/emoji/:id', function ($id) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $token = $app->request->headers->get('token');
    if ($token == null) {
        echo "Please provide a token";
        return false;
    }

    $data = $db->users()->where('token', $token)->fetch('time');

    $result = date($data);

    /*
     * To calculate the difference in time between when you log in and when
     * you perform this request.
     */

    $userLoginTime = new DateTime(date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $result))));
    $currentRequestTime = new DateTime(date('Y-m-d H:i:s'));
    $timeDifference = $userLoginTime->diff($currentRequestTime);
    $timeDifferenceInMinutes = (($timeDifference->h * 60)+($timeDifference->i));

    if ($token = $db->users()->where('token', $token)->fetch('token')) {
        if ($timeDifferenceInMinutes > 300) {
            $user = $db->users()->where('token', $token)->fetch();
            $token = '';
            $data = ['token' => $token];
            $result = $user->update($data);

            echo "login first";
        } else {
            $user = $db->users()->where('token', $token)->fetch('name');
            $emoji = $db->emojis()->where('id', $id);
            if ($emoji->fetch()) {
                $user = $db->users()->where('token', $token)->fetch('name');
                $name = $app->request()->patch('name');
                $keywords = $app->request()->patch('keywords');
                $category = $app->request()->patch('category');

                $emojiUpdate = [
                    'name' => "$name",
                    'keywords' => "$keywords",
                    'category' => "$category",
                    'user' => "$user",
                ];

                $result = $emoji->update($emojiUpdate);
                $emoji = $db->emojis()->where('id', $id);
                $emojis = array();
                foreach ($db->emojis() as $emoji) {
                    $emojis[] = array(
                        'id' => $emoji['id'],
                        'name' => $emoji['name'],
                        'keywords' => $emoji['keywords'],
                        'emoji' => $emoji['emoji'],
                        'category' => $emoji['category'],
                        'created_at' => $emoji['created_at'],
                        'updated_at' => $emoji['updated_at'],
                        'user' => $emoji['user'],
                    );
                }
                echo json_encode($emojis, JSON_FORCE_OBJECT);
            } else {
                echo json_encode(array(
                        'status' => false,
                        'message' => "Emoji id $id does not exist",
                  ));
            }
        }
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please provide a valid token',
        ));
    }
});

// Remove a emoji
$app->delete('/emoji/:id', function ($id) use ($app, $db) {
    $app->response()->header('Content-Type', 'application/json');
    $token = $app->request->headers->get('token');

    $emoji = $db->emojis()->where('id', $id);

    $data = $db->users()->where('token', $token)->fetch('time');

    $result = date($data);

    /*
     * To calculate the difference in time between when you log in and when
     * you perform this request.
     */

    $userLoginTime = new DateTime(date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $result))));
    $currentRequestTime = new DateTime(date('Y-m-d H:i:s'));
    $timeDifference = $userLoginTime->diff($currentRequestTime);
    $timeDifferenceInMinutes = (($timeDifference->h * 60)+($timeDifference->i));

    if ($token !== $db->users()->where('token', $token)->fetch('token')) {
        echo json_encode(array(
            'status' => false,
            'message' => 'Please provide a valid token',
        ));
    } else {
        if ($timeDifferenceInMinutes > 300) {
            $user = $db->users()->where('token', $token)->fetch();
            $token = '';
            $data = ['token' => $token];
            $result = $user->update($data);
            echo "login first";
        } else {
            if ($emoji->fetch()) {
                $result = $emoji->delete();
                $emojis = array();
                foreach ($db->emojis() as $emoji) {
                    $emojis[] = array(
                        'id' => $emoji['id'],
                        'name' => $emoji['name'],
                        'keywords' => $emoji['keywords'],
                        'emoji' => $emoji['emoji'],
                        'category' => $emoji['category'],
                        'created_at' => $emoji['created_at'],
                        'updated_at' => $emoji['updated_at'],
                        'user' => $emoji['user'],
                    );
                }
                echo json_encode($emojis, JSON_FORCE_OBJECT);
            } else {
                echo json_encode(array(
                    'status' => false,
                    'message' => "Emoji id $id does not exist",
                ));
            }
        }
    }
});

/* Run the application */
$app->run();
