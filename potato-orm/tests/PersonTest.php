<?php
namespace Alex;

/**
 * This is a test for Person.php
 *
 * @author Alex Kangethe
 */

use Alex\Model;
use PHPUnit_Framework_TestCase;
use PDO;
use Exception;

class PersonTest extends PHPUnit_Framework_TestCase
{
    /**
     * This is a method is used to setup the database connection for the tests
     *
     */

    public function setUp()
    {
        try {
            Model::$database = new PDO(
                'mysql:host='. Model::$host
                .';dbname='. Model::$dbName,
                Model::$username,
                Model::$password
            );
            Model::$database
            ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " .$exception->getMessage();
        }

        Model::$database->exec("DROP TABLE IF EXISTS `users`");
        Model::$database->exec(
            "CREATE TABLE `users` (" .
            "`id` INTEGER NULL AUTO_INCREMENT," .
            "`name` VARCHAR(250) NULL DEFAULT NULL," .
            "`birthday` VARCHAR(250) NULL DEFAULT NULL," .
            "PRIMARY KEY (`id`) )"
        );
        $user = new Person();
        $user->name = "thepadawan";
        $user->birthday = '100-100-1890';
        $user->save();
    }

    /**
     * This is a method is used to close the database
     *
     */

    public function tearDown()
    {
        Model::$database = null;
    }

    /**
     * This is a method is used to test save method
     *
     */

    public function testSave()
    {
        $user2 = new Person();
        $user2->name = "thepadawan";
        $user2->birthday = '100-100-1890';
        $user2->save();
        $stmt = Model::$database->query("SELECT * FROM users");
        $this->assertCount(2, $stmt->fetchAll());
        $stmt = Model::$database->query("TRUNCATE users");
    }

    /**
     * This is a method is used to test the getAll method
     *
     */

    public function testGetAll()
    {
        $stmt = Model::$database->query("SELECT * FROM users");
        $this->assertCount(1, $stmt->fetchAll());
        $stmt = Model::$database->query("TRUNCATE users");
    }

    /**
     * This is a method is used to test the find method
     *
     */

    public function testFind()
    {
        $user3 = new Person();
        $user3->name = "thepadawan";
        $user3->birthday = '100-100-1890';
        $user3->save();
        $stmt = Model::$database->query("SELECT * FROM users WHERE id=1");
        $result = $stmt->fetchAll();
        $id = $result[0]['id'];
        $user = Person::find(1);
        $this->assertEquals($id, $user->id);
    }

    /**
     * This is a method is used to test the update method
     *
     */

    public function testUpdate()
    {
        $user = new Person();
        $user->id = 1;
        $user->name = "thepadawan";
        $user->birthday = "1990-01-01";
        $user->update();
        $stmt = Model::$database->query("SELECT * FROM users");
        $result = $stmt->fetchAll();
        $this->assertTrue("thepadawan" === $result[0]['name']);
    }

    /**
     * This is a method is used to test the destroy method
     *
     */

    public function testDestroy()
    {
        $stmt = Model::$database->query("SELECT * FROM users");
        $result = $stmt->fetchAll();
        Person::destroy(1);
        $this->assertCount(0, $stmt->fetchAll());
    }
}
