<?php

use App\model\Product;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "PUT" );


/*---------------------------START-CONNECTION--------------------------*/

// QUERY PARAM - OLD CODE

$code = isset($GLOBALS["PARAMS_URI"][0]["code"] )
            ? $GLOBALS["PARAMS_URI"][0]["code"] 
            : NULL;

if ( !$code ) exit();

$conn = ApiFunctions::getConnection( $config );

$product = new Product( $conn );


// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

// Check the correctness of data

$describe = $product->describe();

// Check the correctness of REQUEST
$allParams = (array) ApiFunctions::updateChecker( $data, $describe );

$old_data = [];

if ( count( $allParams ) != 0 ){ 
    
    $old_data = $product->read_by_code( $code );

    if ( $old_data->rowCount() == 0 ) {
        Message::writeJsonMessage( "Order not found" );
        exit();
    }

    $old_data = $old_data->fetch( PDO::FETCH_ASSOC );

} else {
    
    $allParams = array_keys( $data );
    
}

// Inserting input data into new "product" instance

foreach( $allParams as $field ) {

    if ( array_key_exists( $field, $data ) ){
        $product->$field = $data[$field];

    } else {
        $product->$field = isset( $old_data[$field] ) 
                            ? $old_data[$field] 
                            : null;
    }

}

$stmt = $product->update( $code );

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
            "message" => "Updated successfully!"
        ]; 

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "Update unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>