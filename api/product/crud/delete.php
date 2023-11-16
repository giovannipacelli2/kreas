<?php

use App\model\Product;
use App\core\ApiFunctions;

/*-----------------------DELETE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: DELETE");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "DELETE" );


/*---------------------------START-CONNECTION--------------------------*/


// $GLOBALS["PARAMS_URI"] = [ query => value ]

$conn = ApiFunctions::getConnection( $config );

$product = new Product( $conn );

// QUERY PARAM
$product_code = $GLOBALS["PARAMS_URI"]["code"];

$stmt = $product->delete( $product_code );

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
            "message" => "Deleting successfully!"
        ]; 

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "DELETE unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>