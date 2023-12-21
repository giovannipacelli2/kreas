<?php

namespace App\models;

use App\core\App;

class Product
{
    private static $table = 'products';

    public static function describe()
    {
        return App::get('database')->describe(static::$table);
    }

    public static function readAll()
    {
        return App::get('database')->selectAll(static::$table);
    }

    public static function readId($id)
    {
        return App::get('database')->selectProductById(static::$table, $id);
    }

    public static function insert($data)
    {
        return App::get('database')->insert(static::$table, $data);
    }

    public static function checkId($data)
    {
        $field = 'product_code';
        $values = array_column($data, 'product_id');

        return App::get('database')->checkField(static::$table, $field, $values);
    }
}
