<?php

namespace App\controllers;

use App\core\ApiFunctions;
use App\core\Response;
use App\models\Product;

class ApiProductController
{
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
}
