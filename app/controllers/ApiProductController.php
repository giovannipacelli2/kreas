<?php

namespace App\controllers;

use App\core\Response;
use App\models\Product;
use PDO;

class ApiProductController
{
    public function getAllProducts()
    {
        $result = Product::readAll();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Products not found');
            exit();
        }

        $data = $result->fetchAll(PDO::FETCH_ASSOC);
        Response::json($data, 200);
        exit();

    }

    public function getSingleProduct()
    {
        echo 'Ecco il singolo prodotto';
    }
}
