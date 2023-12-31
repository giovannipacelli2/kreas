<?php

namespace App\models;

use App\core\App;

class SalesOrder
{
    private static $table = 'sales_orders';
    private static $join_table = 'products AS p JOIN (' .
                                        'SELECT * FROM ' .
                                        'sales as s JOIN sales_orders as o ' .
                                        'ON s.sales_code = o.sales_id' .
                                        ') AS so' .
                                        ' ON p.product_code = so.product_id';

    // CHECK METHODS

    public static function checkProductInOrder($sales_id, $product_id)
    {
        return App::get('database')->checkProductInOrder(static::$table, $sales_id, $product_id);
    }

    public static function selectProductsInOrder($sales_id)
    {
        return App::get('database')->selectAllByField(static::$table, 'sales_id', $sales_id);
    }

    // GET METHODS

    public static function readAll()
    {
        $join_table = static::$join_table . ' ORDER BY so.sales_date';

        return App::get('database')->selectAll($join_table);
    }

    public static function readByField($field, $values)
    {
        return App::get('database')->selectAllByField(static::$table, $field, $values);
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

    public static function getDestinationCo2($country)
    {
        return App::get('database')->getCo2FromDestination(static::$join_table, $country);
    }

    public static function getProductCo2($product_id)
    {
        return App::get('database')->getCo2FromProduct(static::$join_table, $product_id);
    }

    // POST METHODS

    public static function insert($data)
    {
        return App::get('database')->insert(static::$table, $data);
    }

    public static function insertProduct($data, $sales_id)
    {
        $data['sales_id'] = $sales_id;

        return App::get('database')->insert(static::$table, $data);
    }

    //PUT METHODS

    public static function update($data, $old_id)
    {
        return App::get('database')->update(static::$table, $data, 'sales_id', $old_id);
    }

    public static function updateProductsInOrder($data, $sales_id, $old_product_id)
    {
        return App::get('database')->updateProducts(static::$table, $data, $sales_id, $old_product_id);
    }

    //DELETE METHODS

    public static function deleteProduct($sales_id, $product_id)
    {
        return App::get('database')->deleteProductsOrder(static::$table, $sales_id, $product_id);
    }

    public static function notInOrderProducts($ids, $code)
    {
        $condition = [
            'field' => 'sales_id',
            'value' => $code,
        ];

        return App::get('database')->notInDelete(static::$table, 'product_id', $ids, $condition);
    }
}
