<?php

namespace App\models;

use App\core\App;

class Product
{
    public static function readAll()
    {
        return App::get("database")->selectAll('products');
    }
}
