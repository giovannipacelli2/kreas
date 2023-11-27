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

// Check the correctness of data

$data_keys = array_keys( $data );

foreach ( $data_keys as $key ) {

    if( $key != "product_id" && $key != "n_prod" ){
        Message::writeJsonMessage( "bad request" );
        http_response_code(400);
        exit();
    }
}

$check = $sales->checkSale( $params["code"] );

if ( $check->rowCount() == 0 ) {

    Message::writeJsonMessage( "Order Not Found!" );
    exit();
}
exit();

$stmt = $check->fetch( PDO::FETCH_ASSOC );

$sales->sales_code = $stmt["sales_code"];
$sales->sales_date = $stmt["sales_date"];
$sales->destination = $stmt["destination"];
$sales->product_id = $stmt[""];
$sales->n_products = $stmt[""];

var_dump($data_keys);
exit();

// inserting input data into new "sales" instance

foreach( $data as $key=>$value ) {
    $sales->$key = $value;
}

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