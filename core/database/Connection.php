<?php

namespace App\core\database;

use PDO;
use PDOException;

class Connection
{
    public static function make($config)
    {

        try {

            $pdo = new PDO(
                $config['connection'] . ';dbname=' . $config['name'],
                $config['username'],
                $config['password'],
                $config['options']
            );

            $pdo->exec('set names utf8');

            return $pdo;

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}
