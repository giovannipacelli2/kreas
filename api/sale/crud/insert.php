<?php

use App\model\Sales;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "POST" );


/*---------------------------START-CONNECTION--------------------------*/


extract($GLOBALS["PARAMS_URI"][0]);

$GLOBALS["PARAMS_URI"] = NULL;

$params = [
    "code" => $code
];


$conn = ApiFunctions::getConnection( $config );

$sales = new Sales( $conn );

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

// Check the correctness of REQUEST

$data_keys = array_keys( $data );
$data_fields = [ "product_id", "n_products" ];

$validation = ApiFunctions::validateParams( $data_keys, $data_fields );

if ( !$validation ) {

    Message::writeJsonMessage( "Bad request" );
    http_response_code(400);
    exit();
}

// Check if the ORDER EXISTS

$check_order = $sales->checkSale( $params["code"] );

if ( $check_order->rowCount() == 0 ) {

    Message::writeJsonMessage( "Order Not Found!" );
    exit();
}

// Check if INSERTED PRODUCT already exists in that ORDER

$check_product = $sales->readByProduct( $params["code"], $data["product_id"] );

if ( $check_product->rowCount() > 0 ) {

    Message::writeJsonMessage( "Product inserted already exists!" );
    exit();
}

// INSERT data in SALES intance

$stmt = $check_order->fetch( PDO::FETCH_ASSOC );

$sales->sales_code = $stmt["sales_code"];
$sales->sales_date = $stmt["sales_date"];
$sales->destination = $stmt["destination"];
$sales->product_id = $data["product_id"];
$sales->n_products = $data["n_prod"];

// RUN INSERT 

$stmt = $sales->insert();

if ( $stmt ) {
    writeApi( $stmt->rowCount() );
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( int $affected_rows ) {

    $result = [];
    
    if ( $affected_rows > 0 ){

        $result["result"] = [
            "message" => "inserted successfully!"
        ]; 

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "Insert unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>