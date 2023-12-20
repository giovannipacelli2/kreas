<?php

namespace App\models;

use App\core\App;

class SalesOrder
{
    protected static $join_table = 'products AS p JOIN (' .
                                        'SELECT * FROM ' .
                                        'sales as s JOIN sales_orders as o ' .
                                        'ON s.sales_code = o.sales_id' .
                                        ') AS so' .
                                        ' ON p.product_code = so.product_id';

    public static function readAll()
    {
        $join_table = static::$join_table . ' ORDER BY so.sales_code';

        return App::get('database')->selectAll($join_table);
    }

    public static function readId($id)
    {
        return App::get('database')->selectOrderById(static::$join_table, $id);
    }
    
    public static function getAllCo2()
    {
        return App::get('database')->getCo2FromOrders(static::$join_table);
    }

    public static function getIntervalCo2($date)
    {
        return App::get('database')->getCo2FromDataInterval(static::$join_table, $date);
    }
}
