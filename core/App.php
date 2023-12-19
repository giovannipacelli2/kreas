<?php

namespace App\core;

use Exception;

class App
{
    protected static $registry = [];

    public static function bind($key, $value)
    {

        static::$registry[$key] = $value;
    }

    public static function get($key)
    {

        if (!array_key_exists($key, static::$registry)) {
            throw new Exception("No {$key} found in container");
            exit();
        }

        return static::$registry[$key];
    }
}
