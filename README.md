# PDO Storage for thephpleague oauth2 server
[![Travis branch](https://img.shields.io/travis/DavidWiesner/oauth2-server-pdo/master.svg?style=flat-square)](https://travis-ci.org/DavidWiesner/oauth2-server-pdo) [![Codecov](https://img.shields.io/codecov/c/github/DavidWiesner/oauth2-server-pdo.svg?style=flat-square)](https://codecov.io/github/DavidWiesner/oauth2-server-pdo?branch=master)

This is an Implentation of the [thephpleague/oauth2-server](/thephpleague/oauth2-server/) 
storage interfaces for PDO Storage.

## Usage

```php
$pdo = new PDO('sqlite:oauth2.db');

$sessionStorage = new Storage\SessionStorage($pdo);
$accessTokenStorage = new Storage\AccessTokenStorage($pdo);
$clientStorage = new Storage\ClientStorage($pdo);
$scopeStorage = new Storage\ScopeStorage($pdo);

$server = new ResourceServer(
    $sessionStorage,
    $accessTokenStorage,
    $clientStorage,
    $scopeStorage
);
//…
```
