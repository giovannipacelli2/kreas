<?php

use App\model\Sales;
use App\core\ApiFunctions;

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$sales = new Sales( $conn );

$stmt = $sales->read();

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];
    $result["allOrders"] = [];

    $data = $stmt->fetchAll( PDO::FETCH_ASSOC );
        
        foreach ( $data as $row ) {
            
            array_push( $result["allOrders"], $row );
        
        }
    

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}


?>