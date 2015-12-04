# Naiemoji

Emoji REST API

## Installation

***Requirement***

1. Run ```composer install```
2. Install PHPUnit [found here](https://phpunit.de/)
- To install globally on mac [go to](https://allisterantosik.com/2014/01/08/installing-phpunit-on-osx-mavericks-via-composer/)
and on windows add the bin directory to the path
3. POSTMAN [found here](https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=en) or CURL (which I will be using in the how to) [found here](http://curl.haxx.se/libcurl/php/)

## How to use the REST API

***DATABASE CONNECTION***

For this project I am using homestead. Installation resource [found here](http://www.easylaravelbook.com/blog/2015/01/08/installing-and-configuring-homestead-2-dot-0-for-laravel-5/)
For the purpose of this project I have named the app in homestead edit as nairo.app


Import the sql found in naiemoji

Inside the naiemoji folder there exists a file called index.php . Change the values of

```php
// Database Configuration
$dbhost = 'localhost';
$dbuser = 'homestead';
$dbpass = 'secret';
$dbname = 'naiemoji';
```
to the those that your system uses.

To register a new user

```curl -X POST -d "name=yourname&password=yourpassword&email=youremail" http://nairo.app/register```

To login a user

```curl -X POST -d "password=12345&email=bibi@bibi.com" http://nairo.app/login```

To logout a user

```curl -X POST -d "email=youremail" http://nairo.app/logout```

To create a new emoji

```curl -H "token:yourToken" -X POST -d "emojidetail" http://nairo.app/emoji```

To get all emojis

```curl -i -X GET http://nairo.app/emojis```

To get an emoji

```curl -i -X GET http://nairo.app/emojis/emojiid```

To update an emoji

```curl -X PUT -d "fieldsyouwantchanged" http://nairo.app/emoji/emojiid/nameyouusedtoregister```

#####OR

```curl -H "token:yourToken" -X PATCH -d "fieldsyouwantchanged" http://nairo.app/emoji/emojiid/nameyouusedtoregister```

To delete an emoji

```curl -i -H "token:yourToken" -X DELETE http://nairo.app/emoji/emojiid/nameyouusedtoregister```

***How to run tests***

Assuming you have installed PHPUnit globally all you have
to do is:

- Go to the naiemoji directory
- In your terminal run
```
php phpunit
```
###NOTE
The user has public access in getting all the emojis, getting emoji by id, registering as a user and login in.
To do the rest the user first has to have logged in
