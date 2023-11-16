<?php

use App\model\Product;
use App\core\ApiFunctions;

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

// QUERY PARAM
$product_code = $GLOBALS["PARAMS_URI"]["code"];

// GET DATA FROM REQUEST
$data = ApiFunctions::getInput();

$stmt = $product->describe();

// Check the correctness of data
$data_fields = ApiFunctions::updateChecker( $data, $stmt );

// Inserting input data into new "product" instance

foreach( $data as $key=>$value ) {
    if ( in_array( $key, $data_fields ) ){
        $product->$key = $value;
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