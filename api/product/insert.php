<?php

require_once "./api-functions.php";

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


checkMethod( "POST" );


/*---------------------------START-CONNECTION--------------------------*/


use App\model\Product;



$conn = getConnection( $config );

$product = new Product( $conn );

// GET DATA FROM REQUEST
$data = getInput();

$stmt = $product->describe();

// Check the correctness of data
inputChecker( $data, $stmt );

// inserting input data into new "product" instance

foreach( $data as $key=>$value ) {
    $product->$key = $value;
}

$stmt = $product->insert();

getOutput($stmt);



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( PDOStatement $stmt ) {

    $result = [];
    
    if ( $stmt->rowCount() > 0 ){

        $result["result"] = [
            "message" => "inserted successfully!"
        ]; 

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "Insert unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    return json_encode( $result );

}

?>