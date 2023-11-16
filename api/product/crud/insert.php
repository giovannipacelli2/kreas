<?php

use App\model\Product;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "POST" );


/*---------------------------START-CONNECTION--------------------------*/





$conn = ApiFunctions::getConnection( $config );

$product = new Product( $conn );

// GET DATA FROM REQUEST
$data = ApiFunctions::getInput();

$stmt = $product->describe();

// Check the correctness of data
ApiFunctions::inputChecker( $data, $stmt );

// inserting input data into new "product" instance

foreach( $data as $key=>$value ) {
    $product->$key = $value;
}

$stmt = $product->insert();

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
            "message" => "inserted successfully!"
        ]; 

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "Insert unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>