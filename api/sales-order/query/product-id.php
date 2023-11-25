<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

$product = $GLOBALS["PARAMS_URI"][0]["product"];

if ( !$product ) exit();

$conn = ApiFunctions::getConnection( $config );

$sales = new SalesOrder( $conn );

$stmt = $sales->getCo2FromProduct( $product );

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;


/*-------------------------------FUNCTIONS-----------------------------*/



function writeApi( PDOStatement $stmt ) {
    
    $result = [];

    $data = $stmt->fetch( PDO::FETCH_ASSOC );
    
    $data = isset( $data["total_co2_saved"] ) ? $data : false;

    if ( $data ) {
        
        $result["result"] = [
            $GLOBALS["product"] => [
                "total_co2_saved" => round( (float) $data["total_co2_saved"], 2 )
            ]
        ];

    } else {
        $result["result"] = [
            "message" => "Product not found!"
        ];
    }

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}

?>