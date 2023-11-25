<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

extract($GLOBALS["PARAMS_URI"][0]);
extract($GLOBALS["PARAMS_URI"][1]);

$params = [
    "start" => $start,
    "end" => $end
];

$date = ApiFunctions::checkCorrectDates( $params );

if ( !$date ) exit();

$conn = ApiFunctions::getConnection( $config );

$sales = new SalesOrder( $conn );

$stmt = $sales->getCo2FromOrdersDate( ...$date );

if ( $stmt ) {
    writeApi($stmt, $date);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;


/*-------------------------------FUNCTIONS-----------------------------*/



function writeApi( PDOStatement $stmt, array $date ) {

    $result = [];

    $res = $stmt->fetch( PDO::FETCH_ASSOC );
    
    $res = isset( $res["co2_saved"] ) ? $res : false;

    if ($res) {
        
        $result["result"] = [
            "start_date" => $date["start"]->format( "Y-m-d" ),
            "end_date" => $date["end"]->format( "Y-m-d" ), 
            "co2_saved" => round( (float) $res["co2_saved"], 2 )
        ];
    } else {
        $result["result"] = [
            "message" => "Data not found!"
        ];
    }


    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}

?>