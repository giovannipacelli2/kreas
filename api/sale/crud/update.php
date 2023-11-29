<?php

use App\model\Sales;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "PUT" );


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

// Check if ORDER and PRODUCT of the request exist

$check_product = $sales->readByProduct( $params["code"], $params["prod"] );

if ( $check_product->rowCount() == 0 ) {

    Message::writeJsonMessage( "Product or order not exists!" );
    exit();
}

$old_data = $check_product->fetch( PDO::FETCH_ASSOC );

// Check if INSERTED PRODUCT already exists in that ORDER

$check_product = $sales->readByProduct( $params["code"], $data["product_id"] );

if ( $check_product->rowCount() > 0 ) {

    Message::writeJsonMessage( "Product inserted already exists in that order!" );
    exit();
}

// Inserting input data into new "sales" instance


foreach( $old_data as $key=>$value ) {

    if ( $key != "id" ) {

        if ( array_key_exists( $key, $data ) ){
            
            $sales->$key = $data[$key];
    
        } else {
            $sales->$key = isset( $old_data[$key] ) 
                                ? $old_data[$key] 
                                : null;
        }
    }


}

// RUN UPDATE

$stmt = $sales->updateByProduct( $params["code"], $params["prod"] );

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
            "message" => "Update successfully!"
        ]; 

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "Update unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>