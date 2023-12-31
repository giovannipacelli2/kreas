<?php

namespace App\models;

use App\core\ApiFunctions;
use App\core\App;

class Product
{
    private static $table = 'products';

    // CHECK METHODS

    public static function describe()
    {
        return App::get('database')->describe(static::$table);
    }

    public static function checkId($data)
    {
        $field = 'product_code';
        $values = array_column($data, 'product_id');

        // Duplicate checking
        ApiFunctions::checkDuplicate($values, 'product in order');

        return App::get('database')->checkField(static::$table, $field, $values);
    }

    // GET METHODS

    public static function readAll()
    {
        return App::get('database')->selectAll(static::$table);
    }

    public static function readId($id)
    {
        return App::get('database')->selectProductById(static::$table, $id);
    }

    // POST METHODS

    public static function insert($data)
    {
        return App::get('database')->insert(static::$table, $data);
    }

    // PUT METHODS

    public static function update($data, $product_code)
    {
        return App::get('database')->update(static::$table, $data, 'product_code', $product_code);
    }

    // DELETE METHODS

    public static function delete($product_code)
    {
        return App::get('database')->deleteField(static::$table, 'product_code', $product_code);
    }
}
