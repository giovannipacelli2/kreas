<?php

require_once "./api-functions.php";

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


checkMethod( "PUT" );


/*---------------------------START-CONNECTION--------------------------*/


use App\model\Product;


// $GLOBALS["PARAMS_URI"] = [ query => value ]

$conn = getConnection( $config );

$product = new Product( $conn );

// QUERY PARAM
$product_code = $GLOBALS["PARAMS_URI"]["code"];

// GET DATA FROM REQUEST
$data = getInput();

$stmt = $product->describe();

// Check the correctness of data
inputChecker( $data, $stmt );

// Inserting input data into new "product" instance

foreach( $data as $key=>$value ) {
    $product->$key = $value;
}

$stmt = $product->update( $product_code );

getOutput($stmt);



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( PDOStatement $stmt ) {

    $result = [];
    
    if ( $stmt->rowCount() > 0 ){

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
    return json_encode( $result );

}

?>