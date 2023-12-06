<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "PUT" );


/*---------------------------START-CONNECTION--------------------------*/

$product_id = isset($GLOBALS["PARAMS_URI"][0]["product"] )
? $GLOBALS["PARAMS_URI"][0]["product"] 
: NULL;

$sales_id = isset($GLOBALS["PARAMS_URI"][1]["order"] )
? $GLOBALS["PARAMS_URI"][1]["order"] 
: NULL;

if ( !$product_id || !$sales_id  ) exit();


/*-------------------CREATE-SALES-AND-SALES-INSTANCES------------------*/

$conn = ApiFunctions::getConnection( $config );

$sales_order = new SalesOrder( $conn );

$check_old_data = $sales_order->read_product( $product_id, $sales_id );

if ( $check_old_data->rowCount() == 0 ) {
    Message::writeJsonMessage( "Product or order not exists" );
    exit();
}

$res = [];

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

$data_fields = [ "product_id", "n_products" ];

$dataParams = ApiFunctions::updateChecker( $data, $data_fields, FALSE );

// Check if the code do you want to change already exsists in that order

if ( $data["product_id"] ) {

    $verify = $sales_order->read_product( $data["product_id"], $sales_id );

    if ( $verify->rowCount() > 0 ) {
        Message::writeJsonMessage( "This product already exsists in that order" );
        exit();
    }
}

$old_data = [];

if ( count( $dataParams ) != 0 ) {

    $old_data = $check_old_data->fetch( PDO::FETCH_ASSOC );

} else {
    $dataParams = $data_fields;
}

foreach( $dataParams as $field ) {

    if ( array_key_exists( $field, $data ) ) {

        $sales_order->$field = $data[$field];
    } else {

        $sales_order->$field = isset( $old_data[$field] ) 
                            ? $old_data[$field] 
                            : null;
    }
}

//die(var_dump($sales_order));

$stmt = $sales_order->updateProduct( $product_id, $sales_id );

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
            "message" => "Update successfully!"
        ]; 

        
    } else {
        $result["result"] = [
            "message" => "Update unsuccessful"
        ]; 
    }
    
    http_response_code(200);
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