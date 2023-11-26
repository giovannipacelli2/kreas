<?php

use App\model\SalesOrder;
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

$sale = new SalesOrder( $conn );

// QUERY PARAM
$sales_code = $GLOBALS["PARAMS_URI"][0]["code"];

// GET DATA FROM REQUEST
$data = ApiFunctions::getInput();

//$stmt = $sale->describe();

// Check the correctness of data
$data_fields = ApiFunctions::saleUpdateChecker( $data );


// Inserting input data into new "product" instance

foreach( $data as $key=>$value ) {
    if ( in_array( $key, $data_fields ) ){
        $sale->$key = $value;
    } 

}

$res = $sale->updateOrder( $sales_code );

writeApi( $res );


$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( mixed $res ) {

    $result = [];
    
    if ( $res ){
        
        $result["result"] = $res;

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