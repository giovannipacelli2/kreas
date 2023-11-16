<?php

require_once "./api-functions.php";

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


checkMethod( "PUT" );


/*---------------------------START-CONNECTION--------------------------*/


use App\model\Sale;


// $GLOBALS["PARAMS_URI"] = [ query => value ]

$conn = getConnection( $config );

$sale = new Sale( $conn );

// QUERY PARAM
$sales_code = $GLOBALS["PARAMS_URI"]["code"];

// GET DATA FROM REQUEST
$data = getInput();

$stmt = $sale->describe();

// Check the correctness of data
inputChecker( $data, $stmt );

// Inserting input data into new "sale" instance

foreach( $data as $key=>$value ) {
    $sale->$key = $value;
    
}

$res = $sale->update( $sales_code );

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