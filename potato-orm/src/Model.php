<?php

namespace Alex;

/**
 * This is a class is a simple ORM that persorms
 * basic crud operations
 *
 * @author Alex Kangethe
 */

use PDO;
use Exception;

abstract class Model
{
     /**
     * Property declaration
     *
     * This are the properties that will be used by the class
     *
     */

    public static $database = null;
    public static $host = 'localhost';
    public static $dbName = 'orm';
    public static $username = 'homestead';
    public static $password = 'secret';
    public static $data = [];
    public static $tableName = 'table';

     /**
     * A constructor
     *
     * It is called every time the an object is instansiated
     * before the object can be manupilated by the methods.
     * It creates the databse connection to be used.
     *
     */

    public function __construct()
    {
        try {
            static::$database = new PDO(
                'mysql:host='. static::$host
                .';dbname='.static::$dbName,
                static::$username,
                static::$password
            );

            static::$database
            ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " .$exception->getMessage();
        }
    }

    /**
     * This method maps name to column
     *
     *
     * @return The the value
     */

    public function __set($name, $value)
    {
        static::$data[$name] = $value;
        return $value;
    }

    /**
     * This method maps name to column
     *
     * @throws Exception if no such value exists
     *
     * @return The data name
     *
     */

    public function __get($name)
    {
        if (array_key_exists($name, static::$data)) {
            return static::$data[$name];
        } else {
            throw new Exception("Sorry no such value exists");
        }
    }

    /**
     * This method saves the data into the database
     *
     * @return The executed statement
     */

    public function save()
    {
        $bind = static::$data;
        $fields = array_keys($bind);
        $fieldlist = implode(',', $fields);
        $qs = str_repeat('?,', count($fields) - 1);
        $table = static::$tableName;
        $sql = "INSERT INTO ". $table ."($fieldlist) values(${qs}?)";
        $stmt = static::$database->prepare($sql);
        return $stmt->execute(array_values($bind));
    }

    /**
     * This method gets all the data from the database
     *
     * @return The result
     */

    public static function getAll()
    {
        $table = static::$tableName;
        $sql = "SELECT * FROM `".$table."`";
        $stmt = static::$database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($results)) {
            throw new Exception('Sorry you have nothing in the database');
        } else {
            return static::instance($results);
        }
    }

    /**
     * This method removes the data with the declared id
     *
     * @param id. The id you would like to destroy it's contents
     *
     * @return The result
     */

    public static function destroy($id)
    {
        $table = static::$tableName;
        $sql = "DELETE FROM " . $table . " WHERE id=$id";
        static::$database->exec($sql);
        $sql = "UPDATE " . $table . " SET ";
        $bind = static::$data;
        $count = 0;
        foreach ($bind as $key => $value) {
            $count++;
            $sql .= "$key = NULL";
            if ($count < count($bind)) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE id = " . $id;
        $stmt = static::$database->prepare($sql);
        return $stmt->execute(array_values($bind));
    }

    public static function instance($results)
    {
        $arr = [];
        for ($i = 0; $i < count($results); $i++) {
            $instance = new static();
            foreach ($results[$i] as $key => $value) {
                $instance->$key = $value;
            }
            array_push($arr, $instance);
        }

        if (count($results) == 1) {
            return $instance;
        } else {
            return $arr;
        }
    }

    /**
     * This method finds the data with the declared id
     *
     * @param id. The id you would like to find it's contents
     *
     * @return The result
     */

    public static function find($id)
    {
        $table = static::$tableName;
        $sql = "SELECT * FROM " . $table . " WHERE id=$id";
        $stmt = static::$database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return static::instance($results);
    }

    /**
     * This method updates the data with the new values
     *
     *
     * @return The result
     */

    public function update()
    {
        $bind = static::$data;
        $table = static::$tableName;
        $sql = "UPDATE " . $table . " SET ";
        $count = 0;
        foreach ($bind as $key => &$value) {
            $count++;
            $sql .= "$key = '$value'";
            if ($count < count($bind)) {
                $sql .= ", ";
            }
        }
        $sql .= " WHERE id = " . static::$data['id'];
        $stmt = static::$database->prepare($sql);
        return $stmt->execute();

    }
    /**
     * This method removes the fields in the database table
     *
     * @return The result
     */

    public function truncate()
    {
        $table = static::$tableName;
        $sql = "TRUNCATE " . $table ;
        $stmt = static::$database->prepare($sql);
        return $stmt->execute();
    }
}
