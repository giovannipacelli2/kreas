<?php

    $dotenv = Dotenv\Dotenv::createImmutable( "../" );
    $dotenv->load();

    $dotenv->required([
        'DB_USER',
        'DB_PASSWORD',
        'DB_HOST'
    ])->notEmpty();

    return [
        "database" => [
            "name" => "kreas",
            "username" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"],
            "connection" => "mysql:host=" . $_ENV["DB_HOST"],
            "options" => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ]
    ]

?>