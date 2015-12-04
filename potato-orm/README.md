# Potato ORM

A simple ORM that performs CRUD operations.

## Installation

***Requirement***

1. Run ```composer install```
2. Install PHPUnit [found here](https://phpunit.de/)
- To install globally on mac [go to](https://allisterantosik.com/2014/01/08/installing-phpunit-on-osx-mavericks-via-composer/)
and on windows add the bin directory to the path

***How to run tests***

Assuming you have installed PHPUnit globally all you have
to do is:

- Go to the potato-orm directory
- In your terminal run 
```
php phpunit
```

## How to use the ORM

Import the sql found in potato orm

Inside the src folder there exists a file called Model.php . Change the values of

```php
public $host = 'localhost';
public $dbName = 'orm';
public $username = 'homestead';
public $password = 'secret';
```
to the those that your system uses.


To add a new user 

```php
$user = new Person();
$user->name = "Alex";
$user->birthday = "1990-01-01";
$user->save();
```

To update a new user

```php
$user = new Person();
$user->id = 1;
$user->name = "padawan";
$user->bithday = "1991-01-01";
$user->update();
```

To find a user

```php
// where 1 is the id
$user = Person::find(1);
```

To get all

```php
$user = Person::getAll();
```

To delete a user

```php
$user = Person::destroy();
```
### Creating a new model

To create a new model, create a file with the name of the file also being the class name

```php
<?php

namespace Alex;

use Alex\Model;

class FileName extends Model
{
   
}

```
Inside the FileName class insert

```php

public static $tableName = 'name of the database table';

public static $data = [];
```

Inside the $data associative array insert the database fields and assign them as null . The fields should be the keys
with null values.
Make sure that the table you have stated is present in the database you are using.
