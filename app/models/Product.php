<?php

namespace App\models;

use App\core\App;

class Product
{
    protected static $table = 'products';

    public static function readAll()
    {
        return App::get('database')->selectAll(static::$table);
    }

    public static function readId($id)
    {
        return App::get('database')->selectProductById(static::$table, $id);
    }
}
