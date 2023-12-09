<?php

use App\model\SalesOrder;
use App\model\Sales;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "POST" );

/*------------------------GET-DATA-AND-URI-PARAMS----------------------*/

$sales_id = isset($GLOBALS["PARAMS_URI"][0]["order"] )
? $GLOBALS["PARAMS_URI"][0]["order"] 
: NULL;

if ( !$sales_id  ) {

    Message::writeJsonMessage( "Error in searched code" );
    http_response_code(400);
    exit();
}

/*---------------------------START-CONNECTION--------------------------*/

$conn = ApiFunctions::getConnection( $config );

/*---------------------CREATE-SALES-ORDER-INSTANCES--------------------*/


$sales = new Sales( $conn );
$sales_order = new SalesOrder( $conn );

$check_order = $sales->readByOrder( $sales_id );

if ( $check_order->rowCount() == 0 ) {
    Message::writeJsonMessage( "Order not exists" );
    exit();
}

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

$data_fields = [ "product_id", "n_products" ];

$dataParams = ApiFunctions::inputChecker( $data, $data_fields, FALSE );

// Check if the code do you want to change already exists in that order

if ( $data["product_id"] ) {

    $verify = $sales_order->read_product( $data["product_id"], $sales_id );

    if ( $verify->rowCount() > 0 ) {
        Message::writeJsonMessage( "This product already exists in that order" );
        exit();
    }
}

// Insert data in "sales_order" instance

foreach( $data_fields as $field ) {

    if ( array_key_exists( $field, $data ) ) {

        $sales_order->$field = $data[$field];
    }
}

$sales_order->sales_id = $sales_id;

$stmt = $sales_order->insert( $sales_id );

if ( $stmt ) {

    writeApi( $stmt->rowCount() );
}


$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( int $affected_rows ) {

    $result = [];
    
    if ( $affected_rows > 0 ){

        $result["result"] = [
            "message" => "Insert successfully!"
        ]; 

        http_response_code(201);
        
    } else {
        $result["result"] = [
            "message" => "Insert unsuccessful"
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

function isPlural( int $num ) {

    $s = $num == 1 ? "" : "s";
    return $s;
}

?>