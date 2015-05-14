# Slim-PDO

PDO database library for Slim Framework

### Installation

Use [Composer](https://getcomposer.org/)

```json
"require": {
    "slim/pdo": "~1.0"
}
```

### Usage

Simple example fetching all data from `test_table`.

```php
require_once('vendor/autoload.php');

$dsn = 'mysql:host=localhost;dbname=test_db;charset=utf8';
$usr = 'root';
$pwd = 'toor';

$pdo = new \Slim\PDO\Database( $dsn , $usr , $pwd );

$qry = $pdo->prepare("SELECT * FROM test_table");
$qry->execute();

try
{
    var_dump( $qry->fetchAll() );
}
catch( \PDOException $e )
{
    exit( $e->getMessage() );
}
```

### Changelog

See [CHANGELOG](https://github.com/FaaPz/Slim-PDO/blob/master/CHANGELOG.md)

### License

See [LICENSE](https://github.com/FaaPz/Slim-PDO/blob/master/LICENSE)