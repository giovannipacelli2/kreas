<?php

require_once "./api-functions.php";

/*-----------------------DELETE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: DELETE");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


checkMethod( "DELETE" );


/*---------------------------START-CONNECTION--------------------------*/


use App\model\Product;
use App\core\Message;


// $GLOBALS["PARAMS_URI"] = [ query => value ]

$conn = getConnection( $config );

$product = new Product( $conn );

// QUERY PARAM
$product_code = $GLOBALS["PARAMS_URI"]["code"];

$stmt = $product->delete( $product_code );

getOutput($stmt);



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( PDOStatement $stmt ) {

    $result = [];
    
    if ( $stmt->rowCount() > 0 ){

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
    return json_encode( $result );

}

?>