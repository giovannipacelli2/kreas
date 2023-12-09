<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$sale = new SalesOrder( $conn );

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
    $result = [];

    if ( !isset($data["total_co2_saved"]) || is_null($data["total_co2_saved"]) ) {

        $result["result"] = [
            "message" => "Error finding co2 in sales_order"
        ];
    } else {

        $co2 = round((float)$data["total_co2_saved"], 2);
    
        $result["result"] = [
            "total_co2_saved" => $co2
        ];

        http_response_code(200);
    }


    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}


?>