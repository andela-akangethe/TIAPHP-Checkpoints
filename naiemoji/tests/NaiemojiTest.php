<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

class NaiemojiTest extends PHPUnit_Framework_TestCase
{
    public $database;
    public $host = 'localhost';
    public $dbName = 'testorm';
    public $username = 'homestead';
    public $password = 'secret';
    public $theToken = null;
    public $client;

    public function setUp()
    {
        $this->client = new Client();
    }

    public function testSetUp()
    {
        try {
            $this->database = new PDO(
                'mysql:host='. $this->host
                .';dbname='. $this->dbName,
                $this->username,
                $this->password
            );
            $this->database
            ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " .$exception->getMessage();
        }

        $this->database->exec("DROP TABLE IF EXISTS `users`");
        $this->database->exec(
            "CREATE TABLE IF NOT EXISTS `users` (".
            "`id` int(11) NOT NULL AUTO_INCREMENT,".
            "`name` varchar(250) DEFAULT NULL,".
            "`email` varchar(255) NOT NULL,".
            "`password` text NOT NULL,".
            "`token` varchar(32) NOT NULL,".
            "`created_at` timestamp not null default current_timestamp,".
            "`updated_at` timestamp not null default current_timestamp on update current_timestamp,".
            "`time` varchar(50) DEFAULT NULL,".
            "PRIMARY KEY (`id`) )"
        );
        $this->database->exec("DROP TABLE IF EXISTS `emojis`");
        $this->database->exec(
            "CREATE TABLE IF NOT EXISTS `emojis` (".
            "`id` int(11) NOT NULL AUTO_INCREMENT,".
            "`name` varchar(32) NOT NULL,".
            "`keywords` varchar(32) NOT NULL,".
            "`emoji` varchar(32) NOT NULL,".
            "`category` varchar(32) NOT NULL,".
            "`user` varchar(32) NOT NULL,".
            "`created_at` timestamp not null default current_timestamp,".
            "`updated_at` timestamp not null default current_timestamp on update current_timestamp,".
            "PRIMARY KEY (`id`) )"
        );
    }



    public function testRegister()
    {
        $userOne = $this->client->request(
            'POST',
            'http://nairo.app/register',
            [
              'form_params' => ['name' =>'yourname','password'=>'yourpassword','email'=>'youremail']
            ]
        );

        $userOneResult = $userOne->getBody();

        $json_array  = json_decode($userOneResult, true);

        $this->assertCount(1, $json_array);

        $userTwo = $this->client->request(
            'POST',
            'http://nairo.app/register',
            [
              'form_params' => ['name' =>'eyourname','password'=>'eyourpassword','email'=>'eyouremail']
            ]
        );

        $userTwoResult = $userTwo->getBody();

        $json_array  = json_decode($userTwoResult, true);

        $this->assertCount(2, $json_array);

        $response = $this->client->request(
            'POST',
            'http://nairo.app/register',
            [
              'form_params' => ['name' =>'leyourname','password'=>'leyourpassword','email'=>'leyouremail']
            ]
        );

        $result = $response->getBody();

        $json_array  = json_decode($result, true);

        $this->assertCount(3, $json_array);
    }

    /**
     * @depends testRegister
     */

    public function testLogin()
    {
        $response = $this->client->request(
            'POST',
            'http://nairo.app/login',
            [
                'form_params' => ['password'=>'yourpassword','email'=>'youremail'],
            ]
        );
        $response = $response->getBody();

        return $response;
    }

    /*
     * @depends testLogin
     */

    public function testAddEmoji()
    {
        $this->theToken = $this->testLogin();

        $response = $this->client->request(
            'POST',
            'http://nairo.app/emoji',
            [
                'headers' => ['token'=> $this->theToken],
                'form_params' => ['keywords'=>'anunty', 'emoji' => 'aa'],
            ]
        );

        $response = $response->getBody();
        $json_array  = json_decode($response, true);
        $result = $json_array['id'];

        $this->assertEquals(1, $result);

        $response = $this->client->request(
            'POST',
            'http://nairo.app/emoji',
            [
                'headers' => ['token'=> $this->theToken],
                'form_params' => ['keywords'=>'bunny', 'emoji' => '\:000'],
            ]
        );

        $response = $response->getBody();
        $json_array  = json_decode($response, true);
        $result = $json_array['id'];

        $this->assertEquals(2, $result);
    }

    /**
     * @depends testAddEmoji
     */

    public function testGetEmojis()
    {
        $this->theToken = $this->testLogin();

        $getAll = $this->client->request(
            'GET',
            'http://nairo.app/emojis'
        );

        $result = $getAll->getBody();
        $json_array  = json_decode($result, true);

        $this->assertCount(2, $json_array);

        $response = $this->client->request(
            'POST',
            'http://nairo.app/emoji',
            [
                'headers' => ['token'=> $this->theToken],
                'form_params' => ['keywords'=>'new', 'emoji' => 'emoji'],
            ]
        );

        $response = $response->getBody();
        $json_array  = json_decode($response, true);

        $getAll = $this->client->request(
            'GET',
            'http://nairo.app/emojis'
        );

        $result = $getAll->getBody();
        $json_array  = json_decode($result, true);

        $this->assertCount(3, $json_array);
    }

    /*
     * @depends testGetEmojis
     */
    public function testUpdate()
    {
        $this->theToken = $this->testLogin();

        $response = $this->client->request(
            'PUT',
            'http://nairo.app/emoji/1',
            [
                'headers' => ['token'=> $this->theToken],
                'form_params' => ['keywords'=>'changed', 'emoji' => 'emoji'],
            ]
        );

        $response = $response->getBody();

        $json_array  = json_decode($response, true);

        $changedValue = $json_array[0]['keywords'];

        $this->assertEquals('changed', $changedValue);

    }

    /**
     * @depends testUpdate
     */

    public function testPatch()
    {
        $this->theToken = $this->testLogin();

        $response = $this->client->patch(
            'http://nairo.app/emoji/1',
            [
                'headers' => ['token'=> $this->theToken],
                'form_params' => ['name' => 'changedName'],
            ]
        );

        $response = $response->getBody();

        $json_array  = json_decode($response, true);

        $changedValue = $json_array[0]['name'];

        $this->assertEquals('changedName', $changedValue);
    }

    /**
     * @depends testUpdate
     */

    public function testDelete()
    {
        $this->theToken = $this->testLogin();

        $response =  $this->client->request(
            'DELETE',
            'http://nairo.app/emoji/1',
            [
                'headers' => ['token'=> $this->theToken],
            ]
        );

        $result = $response->getBody();
        $json_array  = json_decode($result, true);

        $this->assertCount(2, $json_array);
    }

    /**
     * @depends testDelete
     */

    public function testLogout()
    {
        $response = $this->client->request(
            'POST',
            'http://nairo.app/logout',
            [
                'form_params' => ['email' => 'youremail'],
            ]
        );

        $result = $response->getBody();

        $this->assertEquals("You have logged off successfully", $result);
    }
}
