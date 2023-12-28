<?php

use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;

try {
    $dotenv = Dotenv\Dotenv::createImmutable('./');

    $dotenv->load();

    $dotenv->required([
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'DB_HOST',
    ])->notEmpty();
} catch (InvalidPathException $e) {

    echo '<h2>Invalid credential</h2>';
    http_response_code(401);
    exit();
} catch (ValidationException $e) {

    echo '<h2>Invalid credential</h2>';
    http_response_code(401);
    exit();
}

return [
    'database' => [
        'name' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
        'connection' => 'mysql:host=' . $_ENV['DB_HOST'],
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
    ],
];
