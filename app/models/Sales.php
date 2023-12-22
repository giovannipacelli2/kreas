<?php

namespace App\models;

use App\core\App;

class Sales
{
    private static $table = 'sales';

    // CHECK METHODS

    public static function describe()
    {
        return App::get('database')->describe(static::$table);
    }

    public static function checkId($data)
    {
        $field = 'sales_code';
        $value = $data[$field];

        return App::get('database')->checkField(static::$table, $field, $value);
    }

    // POST METHODS

    public static function insert($data)
    {
        return App::get('database')->insert(static::$table, $data);
    }

    // PUT METHODS

    public static function update($data, $sales_code)
    {
        return App::get('database')->update(static::$table, $data, 'sales_code', $sales_code);
    }
}
