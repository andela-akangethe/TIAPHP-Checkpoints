<?php

namespace Alex;

/**
 * This is a class is a Person class that provides
 * the table name and the database fields
 *
 * @author Alex Kangethe
 */

use Alex\Model;

class Person extends Model
{
    public static $tableName = 'users';

    public static $data = [
        'id' => null,
        'name' => null,
        'birthday' => null,
    ];
}
