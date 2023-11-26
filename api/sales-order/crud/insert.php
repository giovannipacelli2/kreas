<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "POST" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$sale = new SalesOrder( $conn );

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

//$stmt = $sale->describe();

// Check the correctness of data
$check = ApiFunctions::saleInsertChecker( $data );

if ( !$check ) {            
    Message::writeJsonMessage("Uncomplete or wrong data!");
    exit();
}
// inserting input data into new "sale" instance
foreach( $data as $key=>$value ) {
    $sale->$key = $value;
}

$affected_rows = $sale->insertOrder();

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