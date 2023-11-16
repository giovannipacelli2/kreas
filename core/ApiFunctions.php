<?php

namespace App\core;

use App\core\Connection;
use App\core\Message;
use PDO;
use PDOStatement;

class ApiFunctions {

    private function __construct(){}


    public static function getConnection( mixed $config ) {

        $db = new Connection( $config["database"] );
        $conn = $db->getConnection();

        if ( !$conn ) {
            exit();
        }

        return $conn;
    }

    public static function checkMethod( $method ) {

        if ( $_SERVER["REQUEST_METHOD"] !== $method ) {
            http_response_code(405);
        
            Message::writeJsonMessage( "Method Not Allowed" );
        
            exit();
        }
    }

    public static function getInput() {
        $data = file_get_contents("php://input") ? file_get_contents("php://input") : $_POST;

        if (!$data) {

            Message::writeJsonMessage("No data");
            exit();
            
        }

        return json_decode($data);
    }

    public static function combineBySalesCode( PDOStatement $stmt ) {

        $tmp_arr = [];
        
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

    // check NOT NULL fields

    public static function dataController( $data, $describe ) {

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

            $exists = array_key_exists( $param, $data );

            // if param NOT EXISTS or an param has empty string

            if( !$exists || $data[$param] == "" ) {
                $check = false;
            }

        }

        return $check;
    }

    public static function inputChecker( $data, $stmt ) {

        if ( !$stmt ) exit(); 

        $describe = $stmt->fetchAll( PDO::FETCH_ASSOC );

        if ( !ApiFunctions::dataController( $data, $describe ) ) {
            
            Message::writeJsonMessage("Uncomplete data!");
            exit();
        }

    }

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
}

?>