<?php

use App\core\ApiFunctions;
use App\model\Sales;

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

extract($GLOBALS["PARAMS_URI"][0]);
extract($GLOBALS["PARAMS_URI"][1]);

$GLOBALS["PARAMS_URI"] = NULL;

$params = [
    "code" => $code,
    "prod" => $prod
];

$conn = ApiFunctions::getConnection( $config );

$sales = new Sales( $conn );

$stmt = $sales->readByProduct( $params["code"], $params["prod"] );

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
            "message" => "Product order not Found"
        ];

    } else {

        $data = $stmt->fetch( PDO::FETCH_ASSOC );
    
        $result["result"] = [
            "sales_code" => $data["sales_code"],
            "sales_date" => $data["sales_date"],
            "destination" => $data["destination"],
            "product_id" => $data["product_id"],
            "n_products" => $data["n_products"]
        ];

        http_response_code(200);
    }
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>