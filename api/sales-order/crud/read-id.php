<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

$code = isset($GLOBALS["PARAMS_URI"] )
            ? $GLOBALS["PARAMS_URI"] 
            : NULL;

if ( !$code ) exit();


$conn = ApiFunctions::getConnection( $config );

$sales = new SalesOrder( $conn );

$stmt = $sales->read_id( $code );

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;


/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];

    if ( $stmt->rowCount() == 0 ) {

        $result["result"] = [
            "message" => "Resource Not Found"
        ];

    } else {
        
        $tmp_arr = ApiFunctions::combineBySalesCode( $stmt );
        $key = array_key_first( $tmp_arr );
        $row = $tmp_arr[$key];

    
        $result["result"] = $row;

        http_response_code(200);
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>