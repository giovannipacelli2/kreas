<?php

use App\model\Sale;
use App\core\ApiFunctions;

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$sale = new Sale( $conn );

$stmt = $sale->getCo2FromOrders();

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $data = $stmt->fetch( PDO::FETCH_ASSOC );
    $co2 = round((float)$data["total_co2_saved"], 2);

    $result = [];
    $result["result"] = [
        "total_co2_saved" => $co2
    ];

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}


?>