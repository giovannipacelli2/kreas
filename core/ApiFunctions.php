<?php

namespace App\core;

use DateTime;
use Exception;
use PDO;
use PDOStatement;

class ApiFunctions
{
    private function __construct()
    {
    }

    /*------------------------------------------CONNECTION------------------------------------------*/

    // Check connection methods

    public static function checkMethod(string $method)
    {

        if ($_SERVER['REQUEST_METHOD'] !== $method) {

            Response::json([], 405, 'Method Not Allowed');

            exit();
        }
    }

    /*-----------------------------------------RECEIVED-DATA----------------------------------------*/

    public static function getInput()
    {
        $data = file_get_contents('php://input')
                    ? file_get_contents('php://input')
                    : $_POST;

        if (!$data) {

            Message::writeJsonMessage('No data');
            http_response_code(400);
            exit();
        }

        $result = json_decode($data);

        if (!$result) {

            Message::writeJsonMessage('Incorrect data');
            http_response_code(400);
            exit();
        }

        return $result;
    }

    /*----------------------------------CHECKING-THE-DATA-RECEIVED----------------------------------*/

    /*----------------CHECK-DUPLICATE-FIELD-------------------*/

    public static function checkDuplicate($arr, $arr_description = 'data')
    {

        $unique = array_unique($arr);

        if (count($arr) != count($unique)) {

            Message::writeJsonMessage("You cant't duplicate " . $arr_description);
            exit();
        } else {
            return true;
        }
    }

    /*---------------------CHECK-INPUT------------------------*/

    public static function inputChecker($data, $data_fields, $isDescribe = true)
    {

        if (!$data_fields) {
            exit();
        }

        if ($isDescribe) {

            $describe = $data_fields->fetchAll(PDO::FETCH_ASSOC);
            $data_fields = self::getDataFromTable($describe);
        }

        $validation = self::existsAllParams($data, $data_fields);

        if (!$validation) {

            Message::writeJsonMessage('Bad request');
            http_response_code(400);
            exit();
        }

    }

    // Wants a DESCRIBE result statemento from DATABASE
    public static function getDataFromTable($describe)
    {

        // array containing the list of the NOT NULL fields
        $data_fields = [];

        // Push in $data_checker all NOT NULL fields
        foreach ($describe as $row) {

            $extra = isset($row['Extra']) ? $row['Extra'] : '';

            if ($row['Null'] == 'NO' && !preg_match('/auto_increment/', $extra)) {
                array_push($data_fields, $row['Field']);
            }

        }

        return $data_fields;
    }

    // check NOT NULL fields

    // Wants data as key=>value and fields as array of string
    public static function existsAllParams($data, $data_fields)
    {

        //cast sended data in associative array;
        $data = (array) $data;

        $check = true;

        // check input data integrity

        foreach ($data_fields as $param) {

            // $param = a NOT NULL field from existing table
            // $data = array to check
            $exists = array_key_exists($param, $data);

            // if param NOT EXISTS or an param has empty string

            if (!$exists || $data[$param] == '') {
                $check = false;
            }

        }

        return $check;
    }

    /*-------CHECK-IF-THERE-ARE-ANY-INCORRECT-FIELDS----------*/

    // Wants data as associative array and fields as array of string
    public static function validateParams($data, $data_checker)
    {

        //cast sended data in associative array;
        $data = (array) $data;

        $check = true;

        // check input data integrity

        foreach ($data as $field => $value) {

            // $param = key of sended data
            // $data_checker = array with necessary field

            $exists = in_array($field, $data_checker);
            //$exists = in_array( $param, $data_checker );

            // if param NOT EXISTS

            if (!$exists || $value == '') {
                $check = false;
            }

        }

        return $check;
    }

    /*--------------------CHECK-UPDATE------------------------*/

    // Return empty array if there are all fields
    // Return "data_fields" if there are some of necessary fields

    public static function updateChecker($data, $data_fields, $isDescribe = true)
    {

        if (!$data_fields) {
            exit();
        }

        if ($isDescribe) {

            $describe = $data_fields->fetchAll(PDO::FETCH_ASSOC);
            $data_fields = self::getDataFromTable($describe);
        }

        $validation = self::existsAllParams($data, $data_fields);

        if (!$validation) {

            $validation = self::validateParams($data, $data_fields);

            if (!$validation) {
                Message::writeJsonMessage('Bad request');
                http_response_code(400);
                exit();
            }

            return $data_fields;
        }

        return [];

    }

    /*----------------------CHECK-DATE------------------------*/

    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {

        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    // check data format:
    // It can be 'Y-m-d H:i:s' or 'Y-m-d'

    public static function checkDate($date)
    {

        $validation = self::validateDate($date);

        if (!$validation) {

            if (!self::validateDate($date, 'Y-m-d')) {

                Message::writeJsonMessage("Not valid format of 'sales_date'");
                exit();
            }

        }
    }

    public static function checkCorrectDates(array $date_arr): mixed
    {
        $now = new DateTime('now');

        $date = [
            'start' => '',
            'end' => '',
        ];

        $count = 0;

        // Creates datatime objects from query data
        try {
            foreach ($date_arr as $key => $value) {

                if ($value != '') {
                    $date[$key] = new DateTime($value);
                    $count++;
                }
            }
        } catch (Exception $e) {
            Message::writeJsonMessage('Error in date format!');

            return false;
        }

        // if the data is not there
        if ($count === 0) {

            Message::writeJsonMessage("The 'START' and 'END' values ​​cannot be both empty!");

            return false;

        }

        // If there is only one of the two data, it assigns today's date to the missing one

        elseif ($count < count($date)) {

            if ($date['start'] != '' && $date['start'] >= $now) {

                Message::writeJsonMessage("the 'START' date cannot be greater than today's date");

                return false;

            } elseif ($date['end'] != '' && $date['end'] <= $now) {

                Message::writeJsonMessage("the 'END' date cannot be less than today's date");

                return false;

            }

            if ($date['start'] == '') {
                $date['start'] = $now;
            }
            if ($date['end'] == '') {
                $date['end'] = $now;
            }

        } elseif ($count == count($date)) {

            if ($date['start'] > $date['end']) {
                Message::writeJsonMessage("the 'END' date cannot be less than 'START' date");

                return false;
            } elseif ($date['start'] == $date['end']) {
                Message::writeJsonMessage("The 'START' date can't be the same as the 'END' date");

                return false;
            }

        }

        return $date;

    }

    /*----------------------------------CREATE-NEW-ARRAY-FROM-DATA----------------------------------*/

    // Merge data by sales code

    public static function combineBySalesCode(PDOStatement $stmt): array
    {

        $tmp_arr = [];

        // EXAMPLE OF DATA IN $tmp_arr

        /*---------------------------------------------------------------
        |    ..... ,                                                    |
        |    AA1015' => [                                               |
        |        'sales_code' => string 'AA1015',                       |
        |        'sales_date' => string '2023-10-20 15:20:00',          |
        |        'destination' => string 'China',                       |
        |        'total_products' => int 5,                             |
        |        'sold_products' => [                                   |
        |           [                                                   |
        |               'product_id' => string '6476                    |
        |               'n_prod' => int 3,                              |
        |               'prod_name' => string 'Hamburger'               |
        |           ],                                                  |
        |           [                                                   |
        |               'product_id' => string '0100                    |
        |               'n_prod' => int 2,                              |
        |               'prod_name' => string 'Pork meat'               |
        |           ],                                                  |
        |         ],                                                    |
        |        'saved_kg_co2' => float '33.42'                        |
        |    ],                                                         |
        |    .....                                                      |
        ---------------------------------------------------------------*/

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            //var_dump($row);
            $cur_code = $row['sales_code'];

            foreach ($row as $key => $value) {

                if (($key != 'name') &&
                     ($key != 'product_id') &&
                     ($key != 'saved_kg_co2') &&
                     ($key != 'n_products')
                ) {
                    $tmp_arr[$cur_code][$key] = $value;
                }
            }

            // CALCULATE TOTAL PRODUCTS
            if (isset($tmp_arr[$cur_code]['total_products'])) {

                $tmp_arr[$cur_code]['total_products'] += (int) $row['n_products'];
            } else {
                $tmp_arr[$cur_code]['total_products'] = (int) $row['n_products'];
            }

            // MANAGE SOLD PRODUCTS

            $tmp = [

                'product_id' => $row['product_id'],
                'n_prod' => $row['n_products'],
                'prod_name' => $row['name'],
            ];

            if (isset($tmp_arr[$cur_code]['sold_products'])) {

                array_push($tmp_arr[$cur_code]['sold_products'], $tmp);

            } else {
                $tmp_arr[$cur_code]['sold_products'] = [$tmp];
            }

            if (isset($tmp_arr[$cur_code]['saved_kg_co2'])) {

                $tmp_arr[$cur_code]['saved_kg_co2'] += $row['saved_kg_co2'] * $row['n_products'];
            } else {
                $tmp_arr[$cur_code]['saved_kg_co2'] = $row['saved_kg_co2'] * $row['n_products'];
            }

        }

        //header("Content-Type: application/json charset=UTF-8");
        //echo json_encode($tmp_arr);
        //exit();

        return $tmp_arr;
    }
}
