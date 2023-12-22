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
        exit();

    }

    public function getSingleProduct($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = Product::readId($params['id']);

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Product not found');
            exit();
        }

        $data = $result->fetchAll(\PDO::FETCH_ASSOC);

        Response::json($data, 200);
        exit();

    }

    /*---------------------------------------------------POST-FUNCTIONS--------------------------------------------------*/

    public static function insertProduct()
    {
        $data = (array) ApiFunctions::getInput();
        $describe = Product::describe();

        ApiFunctions::inputChecker($data, $describe);

        $stmt = Product::insert($data);

        if (!$stmt || $stmt->rowCount() == 0) {
            Response::json([], 200, 'Insert unsuccess');
            exit();
        }

        $result = [
            'affected_rows' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');
        exit();
    }

    /*---------------------------------------------------PUT-FUNCTIONS---------------------------------------------------*/
}
