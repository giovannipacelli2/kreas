<?php

use App\core\Message;
use App\model\Sales;
use App\core\ApiFunctions;

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );

/*------------------------GET-DATA-AND-URI-PARAMS----------------------*/

$code = isset($GLOBALS["PARAMS_URI"] )
? $GLOBALS["PARAMS_URI"] 
: NULL;

if ( !$code ) {

    Message::writeJsonMessage( "Error in searched code" );
    http_response_code(400);
    exit();
}

/*---------------------------START-CONNECTION--------------------------*/

$conn = ApiFunctions::getConnection( $config );

$sales = new Sales( $conn );

$stmt = $sales->readByOrder( $code );

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( PDOStatement $stmt ) {

    $result = [];

    if ( $stmt->rowCount() == 0 ) {

        $result["result"] = [
            "message" => "Order Not Found"
        ];

    } else {

        $data = $stmt->fetchAll( PDO::FETCH_ASSOC );
    
        $result["result"] = $data;

        http_response_code(200);
    }
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}


?>