<?php

use App\model\Sales;
use App\model\SalesOrder;
use App\core\ApiFunctions;
use App\core\Message;
use App\model\Product;

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "PUT" );


/*---------------------------START-CONNECTION--------------------------*/

$code = isset($GLOBALS["PARAMS_URI"][0]["code"] )
? $GLOBALS["PARAMS_URI"][0]["code"] 
: NULL;

$new_code = NULL;

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

if ( !$code ) exit();

/*-------------------CREATE-SALES-AND-SALES-INSTANCES------------------*/

$conn = ApiFunctions::getConnection( $config );

$sales_order = new SalesOrder( $conn );
$sales = new Sales( $conn );


// Check if searched sales code exists

$check = $sales->readByOrder( $code );

if ( $check->rowCount() == 0 ) {

    Message::writeJsonMessage( "Order not found" );
    exit();

}

/*--------------CREATE-SALES-AND-SALES-ORDER-ARRAYS-BY-DATA------------*/

$order = [];
$products = [];

foreach ( $data as $key=>$value ) {

    if( $key == "products" ) {

        foreach ( $data[$key] as $p ) {
            $p = (array) $p;

            // Check n_products--> can't be ZERO
            if ( $p["n_products"] == 0 ) {
                Message::writeJsonMessage( "n_products can't be ZERO!" );
                http_response_code(400);
                exit();
            }

            array_push( $products, $p );
        }

    } else {
        $order[$key] = $value;
    }
}

/*------------------------------CHECK-ORDER-PARAMS-------------------------------*/

if ( count( $order ) != 0 ) {

    // Necessary fields
    $describe = $sales->describe();
    
    $orderParams = ApiFunctions::updateChecker( $order, $describe );
    
    $old_data = [];
    
    if ( count( $orderParams ) != 0 ){ 
        
        $old_data = $sales->readByOrder( $code );
    
        if ( $old_data->rowCount() == 0 ) {
            Message::writeJsonMessage( "Order not found" );
            exit();
        }
    
        $old_data = $old_data->fetch( PDO::FETCH_ASSOC );
    
    } else {
        
        $orderParams = array_keys( $order );
        
    }
    
    // INSERT data in SALES intance
    
    foreach( $orderParams as $field ) {
    
        if ( array_key_exists( $field, $order ) ){
            $sales->$field = $order[$field];
    
        } else {
            $sales->$field = isset( $old_data[$field] ) 
                                ? $old_data[$field] 
                                : null;
        }
    
    }

    // Update ORDER
    
    if ( !$products ) {
        $sales->update( $code );
        $new_code = $sales->sales_code;
    }

} else {
    $new_code = $code;
}


/*-----------------------------CHECK-PRODUCT-PARAMS------------------------------*/

if ( count( $products ) != 0 ) {

    // Necessary fields
    $describe = [ "product_id", "n_products" ];
    
    foreach ( $products as $p ) {
    
        validate( $p, $describe );
    }

    $stmt = $sales_order->read_id( $code );

    if ( $stmt->rowCount() == 0 ) {
        Message::writeJsonMessage( "Server Error" );
        http_response_code(500);
        exit();
    }

    $old_data = $stmt->fetchAll( PDO::FETCH_ASSOC );

    $already_exists = array_column( $old_data, "product_id" );
    $new_products = array_column( $products, "product_id" );

    $product = new Product( $conn );
    $check = $product->checkIds( $new_products );
    
    if ( $check->rowCount() != count( $new_products ) ) {
        Message::writeJsonMessage( "Inserted Product not exists" );
        exit();
    }

    $to_update = [];
    $to_insert = [];

    foreach ( $new_products as $key=>$value ) {

        if ( in_array( $value, $already_exists ) ) {
            array_push( $to_update, $value );
        } else {
            array_push( $to_insert, $value );
        }
    }

    if ( $order ) {
            // Do the update of ORDER
        $sales->update( $code );

        $new_code = $sales->sales_code;
    }

    
    if ( $to_update ) {

        foreach ( $products as $p ) {
            
            if ( in_array( $p["product_id"], $to_update ) ) {
                
                $sales_order->n_products = $p["n_products"];

                $sales_order->updateProduct( $p["product_id"], $new_code );
            }    
        }

    }

    if ( $to_insert ) {

        $sales_order->sales_id = $new_code;

        foreach ( $products as $p ) {

            if ( in_array( $p["product_id"], $to_insert ) ) {

                $sales_order->product_id = $p["product_id"];
                $sales_order->n_products = $p["n_products"];

                $sales_order->insert();
            }            
        }

    }

    if ( $products ) {
        
        $sales_order->notInDelete( $new_products, $new_code );
    }


}



    

exit();


writeApi( $res );


$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( mixed $res ) {

    $result = [];
    
    if ( $res ){
        
        $result["result"] = $res;

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "Update unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

function validate( $data, $fields ) {

    // Check the correctness of data
    $validation = ApiFunctions::existsAllParams( $data, $fields );

    if ( !$validation ) {

        Message::writeJsonMessage( "Bad request" );
        http_response_code(400);
        exit();
    }

    return $validation;
}

?>