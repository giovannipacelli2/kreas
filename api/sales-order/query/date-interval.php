<?php

use App\model\Sale;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

$date = ApiFunctions::checkCorrectDates( $GLOBALS["PARAMS_URI"] );

if ( !$date ) exit();

$conn = ApiFunctions::getConnection( $config );

$sales = new Sale( $conn );

$stmt = $sales->getCo2FromOrdersDate( ...$date );

if ( $stmt ) {
    writeApi($stmt, $date);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;


/*-------------------------------FUNCTIONS-----------------------------*/



function writeApi( PDOStatement $stmt, array $date ) {

    $res = $stmt->fetch( PDO::FETCH_ASSOC );

    $result = [];
    $result["result"] = [
        "start_date" => $date["start"]->format( "Y-m-d" ),
        "end_date" => $date["end"]->format( "Y-m-d" ), 
        ...$res
        
    ];

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}

?>