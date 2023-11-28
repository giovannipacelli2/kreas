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

// $GLOBALS["PARAMS_URI"] = [ query => value ]

$conn = ApiFunctions::getConnection( $config );

$product = new Product( $conn );

// QUERY PARAM - OLD CODE
$product_code = $GLOBALS["PARAMS_URI"][0]["code"];

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

$stmt = $product->describe();

// Check the correctness of data

$data_keys = array_keys( $data );
$data_fields = [ "product_code", "name", "saved_kg_co2" ];

$validation = ApiFunctions::validateParams( $data_keys, $data_fields );

if ( !$validation ) {
    Message::writeJsonMessage( "Bad request!" );
    http_response_code(400);
    exit();
}



$old_data = [];

if ( count( $data ) < count( $data_fields ) ) {

    $old_data = $product->read_by_code( $product_code );
    
    if ( $old_data->rowCount() == 0 ) {
    
        Message::writeJsonMessage( "Product code not found" );
        exit();
    }

    $old_data = $old_data->fetch( PDO::FETCH_ASSOC );

}

// Inserting input data into new "product" instance


foreach( $data_fields as $key ) {

    if ( array_key_exists( $key, $data ) ){
        $product->$key = $data[$key];

    } else {
        $product->$key = isset( $old_data[$key] ) 
                            ? $old_data[$key] 
                            : null;
    }

}

$stmt = $product->update( $product_code );

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