<?php

namespace App\core;

use App\core\Connection;
use App\core\Message;
use DateTime, PDO, PDOStatement;
use Exception;
use stdClass;

class ApiFunctions {

    private function __construct(){}

    /*------------------------------------------CONNECTION------------------------------------------*/

    // Create Database connection

    public static function getConnection( array $config ) : PDO {

        $db = new Connection( $config["database"] );
        $conn = $db->getConnection();

        if ( !$conn ) {
            exit();
        }

        return $conn;
    }

    // Check connection methods

    public static function checkMethod( string $method ) {

        if ( $_SERVER["REQUEST_METHOD"] !== $method ) {
            http_response_code(405);
        
            Message::writeJsonMessage( "Method Not Allowed" );
        
            exit();
        }
    }

    /*-----------------------------------------RECEIVED-DATA----------------------------------------*/

    public static function getInput() : stdClass {
        $data = file_get_contents("php://input") 
                    ? file_get_contents("php://input") 
                    : $_POST;

        if (!$data) {

            Message::writeJsonMessage("No data");
            exit();
        }

        return json_decode($data);
    }

    /*----------------------------------CHECKING-THE-DATA-RECEIVED----------------------------------*/


    /*---------------------CHECK-INPUT------------------------*/

    public static function inputChecker( $data, $stmt ) {

        if ( !$stmt ) exit(); 

        $describe = $stmt->fetchAll( PDO::FETCH_ASSOC );

        if ( !ApiFunctions::dataController( $data, $describe ) ) {
            
            Message::writeJsonMessage("Uncomplete data!");
            exit();
        }

    }

    // check NOT NULL fields

    public static function dataController( $data, $describe ) {

        // array containing the list of the NOT NULL fields
        $data_checker= [];

        // Push in $data_checker all NOT NULL fields
        foreach( $describe as $row ){

            $extra = isset($row["Extra"]) ? $row["Extra"] : "";

            if ( $row["Null"] == "NO" && !preg_match( "/auto_increment/", $extra ) ){
                array_push( $data_checker, $row["Field"] );
            }

        }

        //cast data in associative array;
        $data = (array) $data;

        $check = TRUE;

        // check input data integrity

        foreach( $data_checker as $param ){

            // $param = a NOT NULL field from existing table
            // $data = associative array with sended data
            $exists = array_key_exists( $param, $data );

            // if param NOT EXISTS or an param has empty string

            if( !$exists || $data[$param] == "" ) {
                $check = false;
            }

        }

        return $check;
    }

    /*--------------------CHECK-UPDATE------------------------*/
    
    public static function updateChecker( $data, $stmt ) {

        if ( !$stmt ) exit(); 

        $result = [];

        $describe = $stmt->fetchAll( PDO::FETCH_ASSOC );
        
        $fields = array_map( function($elem){
            return $elem["Field"];
        }, $describe );

        foreach( $data as $key=>$value ) {
            if ( in_array( $key, $fields ) ){
                array_push( $result, $key );
            }
        }

        if ( !$result ) {
            Message::writeJsonMessage("Wrong data");
            exit();
        }

        return $result;

    }


    /*----------------------CHECK-DATE------------------------*/

    public static function checkCorrectDates( array $date_arr ) : mixed {
        $now = new DateTime("now");

        $date = [
            "start" => "",
            "end" => ""
        ];

        $count = 0;

        // Creates datatime objects from query data
        try{
            foreach( $date_arr as $key=>$value ) {
    
                if ( $value != "" ){
                    $date[$key] = new DateTime( $value );
                    $count++;
                }
            }
        } catch (Exception $e ) {
            Message::writeJsonMessage("Error in date format!");
            return FALSE;
        }

        // if the data is not there
        if ( $count === 0 ) {

            Message::writeJsonMessage("The 'START' and 'END' values ​​cannot be both empty!");
            return FALSE;

        } 
        
        // If there is only one of the two data, it assigns today's date to the missing one

        elseif ( $count < count( $date ) ) {

            if ( $date["start"] != "" && $date["start"] >= $now  ) {

                Message::writeJsonMessage("the 'START' date cannot be greater than today's date");
                return FALSE;

            } elseif ( $date["end"] != "" && $date["end"] <= $now ) {

                Message::writeJsonMessage("the 'END' date cannot be less than today's date");
                return FALSE;

            }

            if ( $date["start"] == "" ) {
                $date["start"] = $now;
            }
            if ( $date["end"] == "" ) {
                $date["end"] = $now;
            }

        }

        elseif ( $count == count( $date ) ) {

            if ( $date["start"] > $date["end"] ) {
                Message::writeJsonMessage("the 'END' date cannot be less than 'START' date");
                return FALSE;
            }
            elseif ( $date["start"] == $date["end"] ) {
                Message::writeJsonMessage("The 'START' date can't be the same as the 'END' date");
                return FALSE;
            }

        }

        return $date;

    }

    /*----------------------------------CREATE-NEW-ARRAY-FROM-DATA----------------------------------*/

    // Merge data by sales code

    public static function combineBySalesCode( PDOStatement $stmt ) : array {

        $tmp_arr = [];

        // EXAMPLE OF DATA IN $tmp_arr

        /*---------------------------------------------------------------
        |    ..... ,                                                    |
        |    AA1015' => [                                               |
        |        'name' => string 'hamburger, pork meat',               |
        |        'product_code' => string '6476, 0100',                 |
        |        'sales_code' => string 'AA1015',                       |
        |        'sales_date' => string '2023-10-20 15:20:00',          |
        |        'destination' => string 'China',                       |
        |        'saved_kg_co2' => int 11,                              |
        |        'total_saved_co2' => string '16',                      |
        |        'articles_num' => int 2                                |
        |    ],                                                         |
        |    .....                                                      |
        ---------------------------------------------------------------*/
        
        while( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {

            if ( !isset( $tmp_arr[$row["sales_code"]] ) ){
                $tmp_arr[$row["sales_code"]] = $row;
            } else {
                $tmp_arr[$row["sales_code"]] = [
                    ...$tmp_arr[$row["sales_code"]],
                    "name" => $tmp_arr[$row["sales_code"]]["name"] . ", " . $row["name"],
                    "product_code" => $tmp_arr[$row["sales_code"]]["product_code"] . ", " . $row["product_code"]
                ];
            }
        }

        return $tmp_arr;
    }
}

?>