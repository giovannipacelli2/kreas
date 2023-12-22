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
            exit();
        }

        $data = ApiFunctions::combineBySalesCode($result);

        Response::json($data, 200);
        exit();

    }

    public function getSingleSalesOrder($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = SalesOrder::readId($params['id']);

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
            exit();
        }

        $data = ApiFunctions::combineBySalesCode($result);

        Response::json($data, 200);
        exit();

    }

    public static function getAllCo2()
    {
        $result = SalesOrder::getAllCo2();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
            exit();
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
            exit();
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

        $verify_order = Sales::checkId($data);
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
                exit();
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
                    exit();
                }

                $affected_products = $affected_products + $stmt->rowCount();
            }

            $result['Inserted_products'] = $affected_products;

            Response::json($result, 200, '');
            exit();

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
            exit();
        }

        $stmt = SalesOrder::insertProduct($data, $params['order']);

        if (!$stmt || $stmt->rowCount() == 0) {
            Response::json([], 200, 'Insert unsuccess');
            exit();
        }

        $result = [
            'Inserted_products' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');
        exit();

    }

    /*---------------------------------------------------PUT-FUNCTIONS---------------------------------------------------*/

    public static function updateSales($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $data = (array) ApiFunctions::getInput();
        $describe = Sales::describe();

        ApiFunctions::updateChecker($data, $describe);

        if (isset($data['sales_date'])) {
            ApiFunctions::checkDate($data['sales_date']);
        }

        $stmt = Sales::update($data, $params['id']);

        if (!$stmt || $stmt->rowCount() == 0) {
            Response::json([], 200, 'Update unsuccess');
            exit();
        }

        $result = [
            'updated_orders' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');
        exit();
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
                exit();
            }
        }
    }
}
