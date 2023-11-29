<?php

namespace App\core;

use App\core\Connection;
use App\core\Message;
use DateTime, PDO, PDOStatement;
use Exception;

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

    public static function getInput() {
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

        $data_checker = ApiFunctions::getDataFromTable( $describe );

        if ( !ApiFunctions::existsAllParams( $data, $data_checker ) ) {
            
            Message::writeJsonMessage("Uncomplete data!");
            exit();
        }

    }

    public static function getDataFromTable( $describe ) {

        // array containing the list of the NOT NULL fields
        $data_fields= [];
    
        // Push in $data_checker all NOT NULL fields
        foreach( $describe as $row ){
    
            $extra = isset($row["Extra"]) ? $row["Extra"] : "";
    
            if ( $row["Null"] == "NO" && !preg_match( "/auto_increment/", $extra ) ){
                array_push( $data_fields, $row["Field"] );
            }
    
        }

        return $data_fields;
    }

    // check NOT NULL fields

    public static function existsAllParams( $data, $data_checker ) {

        //cast sended data in associative array;
        $data = (array) $data;

        $check = TRUE;

        // check input data integrity

        foreach( $data_checker as $param ){

            // $param = a NOT NULL field from existing table
            // $data_checker = array with exists field
            $exists = array_key_exists( $param, $data );

            // if param NOT EXISTS or an param has empty string

            if( !$exists || $data[$param] == "" ) {
                $check = false;
            }

        }

        return $check;
    }

    public static function validateParams( $data, $data_checker ) {

        //cast sended data in associative array;
        $data = (array) $data;

        $check = TRUE;

        // check input data integrity

        foreach( $data as $param ){

            // $param = key of sended data
            // $data_checker = array with necessary field
            $exists = in_array( $param, $data_checker );

            // if param NOT EXISTS

            if( !$exists ) {
                $check = false;
            }

        }

        return $check;
    }

     /*------------------CHECK-SALE-INSERT---------------------*/
    
     public static function saleInsertChecker( $data ) {

        //cast data in associative array;
        $data = (array) $data;

        $fields = [ "sales_code", "sales_date", "destination", "products" ];

        $check = TRUE;

        // check input data integrity

        foreach( $fields as $field ){

            // $field = a NOT NULL field from existing table
            // $data = associative array with sended data
            $exists = array_key_exists( $field, $data );

            // Check the array "products"

            if ( $exists && $field == "products" ) {

                if ( !ApiFunctions::productField( $data[$field] ) ) return FALSE;
            }

            // if param NOT EXISTS or an param has empty string

            if( !$exists || $data[$field] == "" ) {
                return FALSE;
            }

        }

        return $check;
     }

    /*----------------CHECK-PRODUCT-FIELDs--------------------*/
    
    private static function productField( $products ) {

        $product_fields = [ "product_code", "n_prod" ];

        if ( !is_array( $products ) || count( $products ) == 0 ) return FALSE;
        
                    
        foreach( $products as $product ) {

            $product = (array) $product;

            foreach ( $product_fields as $f ) {
                $exts = array_key_exists( $f, $product );

                if( !$exts || $product[$f] == "" ) {
                    return FALSE;
                } 
                        
                // Check n_prod not be text or less than zero
                else if ( $exts && (!is_int( $product["n_prod"] ) || !$product["n_prod"]>0 ) ) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /*--------------------CHECK-UPDATE------------------------*/
    
    public static function saleUpdateChecker( $data ) {

        //cast data in associative array;
        $data = (array) $data;

        $result = [];
        $fields = [ "sales_code", "sales_date", "destination", "products" ];

        foreach( $data as $key=>$value ) {
            if ( in_array( $key, $fields ) ){
                array_push( $result, $key );
            }
        }

        if ( isset( $data["products"] ) ){
            $check_products = ApiFunctions::productField( $data["products"] );

            if ( !$check_products ) {
                Message::writeJsonMessage("Wrong data in products array!");
                exit();
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
        |        'sales_code' => string 'AA1015',                       |
        |        'sales_date' => string '2023-10-20 15:20:00',          |
        |        'destination' => string 'China',                       |
        |        'n_products' => int 5,                                 |
        |        'sold_products' => [                                   |
        |           [                                                   |
        |               'product_code' => string '6476                  |
        |               'n_prod' => int 3,                              |
        |               'prod_name' => string 'Hamburger'               |
        |           ],                                                  |
        |           [                                                   |
        |               'product_code' => string '0100                  |
        |               'n_prod' => int 2,                              |
        |               'prod_name' => string 'Pork meat'               |
        |           ],                                                  |
        |         ],                                                    |                                                   
        |        'total_saved_co2' => float '33.42'                     |
        |    ],                                                         |
        |    .....                                                      |
        ---------------------------------------------------------------*/
        
        while( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {

            //var_dump($row);
            $cur_code = $row['sales_code'];

            foreach ( $row as $key=>$value ) {
                
                if ( ( $key != "name" ) && 
                     ( $key != "product_code" ) && 
                     ( $key != "saved_kg_co2" ) &&
                     ( $key != "n_products" )
                ) {
                    $tmp_arr[$cur_code][ $key ] = $value;
                }
            }

            // CALCULATE TOTAL PRODUCTS
            if ( isset( $tmp_arr[$cur_code]["total_products"] ) ) {

                $tmp_arr[$cur_code]["total_products"] += (int) $row["n_products"];
            } else {
                $tmp_arr[$cur_code]["total_products"] = (int) $row["n_products"];
            }

            // MANAGE SOLD PRODUCTS

            $tmp = [

                "product_code" => $row["product_code"],
                "n_prod" => $row["n_products"],
                "prod_name" => $row["name"]
            ];
            
            if ( isset( $tmp_arr[$cur_code]["sold_products"] ) ) {

                array_push( $tmp_arr[$cur_code]["sold_products"], $tmp );

            } else {
                $tmp_arr[$cur_code]["sold_products"] = [$tmp] ;
            }

            if ( isset( $tmp_arr[$cur_code]["saved_kg_co2"] ) ) {

                $tmp_arr[$cur_code]["saved_kg_co2"] += $row["saved_kg_co2"] * $row["n_products"] ;
            } else {
                $tmp_arr[$cur_code]["saved_kg_co2"] = $row["saved_kg_co2"] * $row["n_products"] ;
            }

        }
        //echo json_encode($tmp_arr);
        //var_dump($tmp_arr);

        return $tmp_arr;
    }
}

?>