<?php

use App\model\Product;
use App\core\ApiFunctions;

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$product = new Product( $conn );

$stmt = $product->read_by_code( $GLOBALS["PARAMS_URI"] );

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
            "message" => "Resource Not Found"
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