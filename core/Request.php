<?php

namespace App\core;

class Request
{
    public static function uri()
    {

        return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    }

    public static function query()
    {

        return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), '/');
    }

    public static function method()
    {

        return $_SERVER['REQUEST_METHOD'];
    }
}
