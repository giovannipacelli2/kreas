<?php

namespace App\controllers;

use App\core\ApiFunctions;
use App\core\Response;
use App\models\Sales;

class ApiSalesController
{
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
        }

        $result = [
            'updated_orders' => $stmt->rowCount(),
        ];

        Response::json($result, 200, '');
    }

    /*--------------------------------------------------DELETE-FUNCTIONS-------------------------------------------------*/

    public static function deleteSales($params)
    {
        $params = ApiFunctions::paramsUri($params);

        $stmt = Sales::delete($params['id']);

        if (!$stmt) {
            Response::json([], 200, 'delete unsuccess');
        }

        $result['deleted'] = true;
        Response::json($result, 200, '');
    }
}
