<?php

namespace App\controllers;

use App\core\ApiFunctions;
use App\core\Response;
use App\models\Product;

class ApiProductController
{
    /*---------------------------------------------------GET-FUNCTIONS---------------------------------------------------*/

    public function getAllProducts()
    {
        $result = Product::readAll();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Products not found');
            exit();
        }

        $data = $result->fetchAll(\PDO::FETCH_ASSOC);
        Response::json($data, 200);
    }

    public function getSingleProduct($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = Product::readId($params['id']);

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Product not found');
        }

        $data = $result->fetchAll(\PDO::FETCH_ASSOC);

        Response::json($data, 200);

    }

    /*---------------------------------------------------POST-FUNCTIONS--------------------------------------------------*/

    public static function insertProduct()
    {
        $data = (array) ApiFunctions::getInput();
        $describe = Product::describe();

        ApiFunctions::inputChecker($data, $describe);
        self::product_validation($data);

        $stmt = Product::insert($data);

        if (!$stmt || $stmt->rowCount() == 0) {
            Response::json([], 200, 'Insert unsuccess');
        }

        $result = [
            'inserted_products' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');
    }

    /*---------------------------------------------------PUT-FUNCTIONS---------------------------------------------------*/

    public static function updateProduct($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $data = (array) ApiFunctions::getInput();
        $describe = Product::describe();

        ApiFunctions::updateChecker($data, $describe);

        if (isset($data['saved_kg_co2'])) {
            self::product_validation($data);
        }

        $stmt = Product::update($data, $params['id']);

        if (!$stmt || $stmt->rowCount() == 0) {
            Response::json([], 200, 'Update unsuccess');
        }

        $result = [
            'updated_products' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');
    }

    /*-------------------------------------------------PRIVATE-FUNCTIONS-------------------------------------------------*/

    private static function product_validation($product)
    {

        $n_product = (float) $product['saved_kg_co2'];

        if ($n_product == 0) {
            Response::json([], 400, 'n_products format is not valid');
        }
    }
}
