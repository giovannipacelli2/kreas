<?php

require_once "./api-functions.php";

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


checkMethod( "POST" );


/*---------------------------START-CONNECTION--------------------------*/


use App\model\Sale;



$conn = getConnection( $config );

$sale = new Sale( $conn );

// GET DATA FROM REQUEST
$data = (array) getInput();

$stmt = $sale->describe();

// Check the correctness of data
inputChecker( $data, $stmt );

// inserting input data into new "sale" instance

foreach( $data as $key=>$value ) {
    $sale->$key = $value;
}

$affected_rows = $sale->insert();

if ( !is_null( $affected_rows ) ) {
    writeApi( $affected_rows );
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( int $affected_rows ) {

    $result = [];
    
    if ( $affected_rows > 0 ){

        $s = $affected_rows == 1 ? "" : "s";

        $result["result"] = [
            "message" => "inserted " . $affected_rows . " row" . $s
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