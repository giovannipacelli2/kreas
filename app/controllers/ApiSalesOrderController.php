<?php

namespace App\controllers;

use App\core\Response;
use App\models\SalesOrder;
use PDO;

class ApiSalesOrderController
{
    public function getAllSalesOrders()
    {
        $result = SalesOrder::readAll();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
            exit();
        }

        $data = $result->fetchAll(PDO::FETCH_ASSOC);
        Response::json($data, 200);
        exit();

    }
}
