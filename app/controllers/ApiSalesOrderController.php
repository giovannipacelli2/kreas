<?php

namespace App\controllers;

use App\core\ApiFunctions;
use App\core\Response;
use App\models\SalesOrder;

class ApiSalesOrderController
{
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
}
