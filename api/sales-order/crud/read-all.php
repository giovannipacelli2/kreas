<?php


use App\model\SalesOrder;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");

ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

$conn = ApiFunctions::getConnection( $config );

$sales_orders = new SalesOrder( $conn );

$stmt = $sales_orders->read_all();
    
if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];
    $tmp_arr = ApiFunctions::combineBySalesCode( $stmt );

    $result["result"]["sales_orders"]=[];

    foreach( $tmp_arr as $row ) {

        array_push( $result["result"]["sales_orders"], $row );

    }

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}


?>