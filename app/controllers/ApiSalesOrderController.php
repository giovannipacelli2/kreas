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

    public static function getAllCo2()
    {
        $result = SalesOrder::getAllCo2();

        if ($result->rowCount() == 0) {
            Response::json([], 404, 'Sales Orders not found');
            exit();
        }

        $result = $result->fetch(\PDO::FETCH_ASSOC);

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
            Response::json([], 200, 'Products not found');
        }

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

        // Normal case
        if (isset($result['co2_saved']) && $result['co2_saved'] > 0) {
            $data['co2_saved'] = round((float) $result['co2_saved'], 2);

            Response::json($data, 200);
        }
        // If total co2 less than zero
        elseif (isset($result['co2_saved']) && $result['co2_saved'] == 0) {
            Response::json([], 200, 'No CO2 saved');
        }
        // If query result is NULL
        else {
            Response::json([], 200, 'Products not found');
        }

        exit();
    }

    public static function getDestinationCo2($params)
    {
        // Says: "Bad request" if user not insert any params in uri
        $params = ApiFunctions::paramsUri($params);

        $result = SalesOrder::getDestinationCo2($params['country']);

        $result = $result->fetch(\PDO::FETCH_ASSOC);

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
            Response::json([], 200, 'Country not found');
        }

        exit();
    }
}
