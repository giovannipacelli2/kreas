<?php

namespace App\controllers;

use App\core\ApiFunctions;
use App\core\Response;
use App\models\Product;
use App\models\Sales;
use App\models\SalesOrder;

class ApiSalesOrderController
{
    /*---------------------------------------------------GET-FUNCTIONS---------------------------------------------------*/

    public function getAllSalesOrders()
    {
        $result = SalesOrder::readAll();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
        }

        $data = ApiFunctions::combineBySalesCode($result);

        Response::json($data, 200);

    }

    public function getSingleSalesOrder($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = SalesOrder::readId($params['id']);

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
        }

        $data = ApiFunctions::combineBySalesCode($result);

        Response::json($data, 200);

    }

    public static function getAllCo2()
    {
        $result = SalesOrder::getAllCo2();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
        }

        $result = $result->fetch(\PDO::FETCH_ASSOC);

        self::co2SavedCheck($result, 'Products');

        exit();
    }

    public static function getIntervalCo2($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);
        $date = ApiFunctions::checkCorrectDates($params);

        $result = SalesOrder::getIntervalCo2($date);

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
        }

        $result = $result->fetch(\PDO::FETCH_ASSOC);

        self::co2SavedCheck($result, 'Products');

        exit();
    }

    public static function getDestinationCo2($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = SalesOrder::getDestinationCo2($params['country']);

        $result = $result->fetch(\PDO::FETCH_ASSOC);

        self::co2SavedCheck($result, 'Country');

        exit();
    }

    public static function getProductCo2($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = SalesOrder::getProductCo2($params['product']);

        $result = $result->fetch(\PDO::FETCH_ASSOC);

        self::co2SavedCheck($result, 'Product');

        exit();
    }

    /*--------------------------------------------------POST-FUNCTIONS---------------------------------------------------*/

    public static function insertSalesOrders()
    {
        $data = (array) ApiFunctions::getInput();
        $describe = Sales::describe();

        ApiFunctions::inputChecker($data, $describe);

        self::products_validation($data['products']);

        $verify_order = Sales::checkId($data['sales_code']);
        $verify_product = Product::checkId($data['products']);

        if (!$verify_order && $verify_product) {

            // Insert Order

            $result = [];

            $stmt = Sales::insert([
                'sales_code' => $data['sales_code'],
                'sales_date' => $data['sales_date'],
                'destination' => $data['destination'],
            ]);

            if (!$stmt || $stmt->rowCount() == 0) {
                Response::json([], 200, 'Insert unsuccess');
            }

            $result['Inserted_order'] = $stmt->rowCount();

            // Insert product in order

            $affected_products = 0;

            foreach ($data['products'] as $product) {

                $product = (array) $product;
                $product['sales_id'] = $data['sales_code'];

                $stmt = SalesOrder::insert($product);

                if (!$stmt || $stmt->rowCount() == 0) {
                    Response::json([], 200, 'Insert product unsuccess');
                }

                $affected_products = $affected_products + $stmt->rowCount();
            }

            $result['Inserted_products'] = $affected_products;

            Response::json($result, 200, '');

        } elseif ($verify_order) {

            Response::json([], 200, 'Order already exists');

        } elseif (!$verify_product) {

            Response::json([], 400, 'Inserted products not exists in product table');
        }
        exit();

    }

    public static function insertProductInOrder($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $data = (array) ApiFunctions::getInput();

        self::products_validation([$data]);
        $already_exists = SalesOrder::checkProductInOrder($params['order'], $data['product_id']);

        if ($already_exists) {
            Response::json([], 400, 'Product already exists in that order');
        }

        $stmt = SalesOrder::insertProduct($data, $params['order']);

        if (!$stmt || $stmt->rowCount() == 0) {
            Response::json([], 200, 'Insert unsuccess');
        }

        $result = [
            'Inserted_products' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');

    }

    /*---------------------------------------------------PUT-FUNCTIONS---------------------------------------------------*/

    public static function updateSalesOrders($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $sales_info = [];
        $result = [];

        $total_query_success = 0;

        $data = (array) ApiFunctions::getInput();
        $data_fields = ['sales_code', 'sales_date', 'destination', 'products'];

        /*------------------Check-correctness-of-body-request-data------------------*/

        // Order info:

        $old_id = $params['id'];
        $new_id = null;

        $verify_sales_code = Sales::checkId($old_id);

        if (!$verify_sales_code) {
            Response::json([], 400, 'Sales code not exists');
        }

        if (isset($data['sales_code'])) {
            $new_id = $data['sales_code'];
        }
        if (isset($data['sales_date'])) {
            ApiFunctions::checkDate($data['sales_date']);
        }

        $to_update = [];
        $to_insert = [];

        // Create array with ORDER INFORMATIONS

        foreach ($data as $field=>$value) {
            if ($field != 'products') {
                $sales_info[$field] = $value;
            }
        }

        // Products in order:

        if (isset($data['products']) && !empty($data['products'])) {

            self::products_validation($data['products']);

            $check_products_id = Product::checkId($data['products']);

            if (!$check_products_id) {
                Response::json([], 400, 'Inserted product not exists in PRODUCTS table');
            }

            /*----------------finds-which-data-to-edit-insert-and-delete----------------*/

            $stmt = SalesOrder::readByField('sales_id', $old_id);
            $stmt = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $already_exists = array_column($stmt, 'product_id');

            foreach ($data['products'] as $p) {

                $p = (array) $p;

                if (in_array($p['product_id'], $already_exists)) {
                    array_push($to_update, $p);
                } else {
                    $p['sales_id'] = $new_id ? $new_id : $old_id;
                    array_push($to_insert, $p);
                }
            }
        }

        /*------------------------QUERY-UPDATE-ORDER-INFO---------------------------*/

        if ($sales_info) {

            $stmt = Sales::update($sales_info, $old_id);

            if (!$stmt) {
                Response::json([], 200, 'Update unsuccess');
            }

            $result = [
                'updated_orders' => $stmt->rowCount(),
            ];

            $total_query_success += $stmt->rowCount();
        }
        /*---------------------QUERY-UPDATE-PRODUCT-IN-ORDER------------------------*/

        if ($to_update) {
            $code = $new_id ? $new_id : $old_id;

            $count = 0;

            foreach ($to_update as $p) {
                $stmt = SalesOrder::updateProductsInOrder($p, $code, $p['product_id']);

                if ($stmt->rowCount() > 0) {
                    $count = $count + $stmt->rowCount();
                }
            }

            $result['update_products'] = $count;
            $total_query_success += $count;

        }

        /*---------------------QUERY-INSERT-PRODUCT-IN-ORDER------------------------*/

        if ($to_insert) {

            $count = 0;

            foreach ($to_insert as $p) {
                $stmt = SalesOrder::insert($p);

                if ($stmt && $stmt->rowCount() > 0) {
                    $count = $count + $stmt->rowCount();
                }
            }

            $result['insert_products'] = $count;
            $total_query_success += $count;
        }

        /*-------------------QUERY-DELETE-OLD-PRODUCT-IN-ORDER----------------------*/

        if (isset($data['products']) && !empty($data['products'])) {

            $code = $new_id ? $new_id : $old_id;

            $ids = array_column($data['products'], 'product_id');
            $stmt = SalesOrder::notInOrderProducts($ids, $code);

            if ($stmt && $stmt->rowCount() > 0) {
                $result['delete_products'] = $stmt->rowCount();
            }

            $total_query_success += $stmt->rowCount();
        }

        if ($total_query_success == 0) {
            Response::json([], 200, 'Nothing to update');
        }
        Response::json($result, 200, '');
    }

    public static function updateProductInSalesOrders($params)
    {
        $params = ApiFunctions::paramsUri($params);
        $product = (array) ApiFunctions::getInput();

        $data_fields = ['product_id', 'n_products'];

        ApiFunctions::updateChecker($product, $data_fields, false);

        // check n_product

        if (isset($product['n_products'])) {
            $n_product = (int) $product['n_products'];

            if ($n_product == 0) {
                Response::json([], 400, 'n_products format is not valid');
            }
        }

        // check product_id

        if (isset($product['product_id'])) {

            $verify = SalesOrder::checkProductInOrder($params['order'], $product['product_id']);

            if ($verify && $product['product_id'] != $params['product']) {
                Response::json([], 400, 'Product already exists in that order');
            }
        }

        // if all checks are ok, do the update

        $stmt = SalesOrder::updateProductsInOrder($product, $params['order'], $params['product']);

        if ($stmt->rowCount() == 0) {
            Response::json([], 400, 'Product update unsuccess');
        }

        $result['update_products'] = $stmt->rowCount();

        Response::json($result, 200, '');
    }

    /*--------------------------------------------------DELETE-FUNCTIONS-------------------------------------------------*/

    public static function deleteProductInSalesOrders($params)
    {
        $params = ApiFunctions::paramsUri($params);

        $select_products = SalesOrder::selectProductsInOrder($params['order']);

        if ($select_products->rowCount() == 1) {
            Response::json([], 200, ['Delete unsuccess: the order must contain at least one product']);
        }

        $stmt = SalesOrder::deleteProduct($params['order'], $params['product']);

        if ($stmt->rowCount() == 0) {
            Response::json([], 200, ['Delete unsuccess']);
        }

        $result['deleted'] = true;
        Response::json($result, 200, '');
    }

    /*-------------------------------------------------PRIVATE-FUNCTIONS-------------------------------------------------*/

    private static function co2SavedCheck($result, $type)
    {

        // Normal case
        if (isset($result['total_co2_saved']) && $result['total_co2_saved'] > 0) {
            $data['total_co2_saved'] = round((float) $result['total_co2_saved'], 2);

            Response::json($data, 200);
        }
        // If total co2 less than zero
        elseif (isset($result['total_co2_saved']) && $result['total_co2_saved'] == 0) {
            Response::json([], 200, 'No CO2 saved');
        }
        // If query result is NULL
        else {
            Response::json([], 200, $type . ' not found');
        }
    }

    private static function products_validation($products)
    {
        foreach ($products as $product) {
            $product = (array) $product;
            ApiFunctions::inputChecker($product, ['product_id', 'n_products'], false);

            $n_product = (int) $product['n_products'];

            if ($n_product == 0) {
                Response::json([], 400, 'n_products format is not valid');
            }
        }
    }
}
