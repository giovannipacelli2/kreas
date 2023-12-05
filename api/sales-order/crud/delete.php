<?php

use App\model\SalesOrder;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------DELETE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: DELETE");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "DELETE" );


/*---------------------------START-CONNECTION--------------------------*/


$product_id = isset($GLOBALS["PARAMS_URI"][0]["product"] )
? $GLOBALS["PARAMS_URI"][0]["product"] 
: NULL;

$sales_id = isset($GLOBALS["PARAMS_URI"][1]["order"] )
? $GLOBALS["PARAMS_URI"][1]["order"] 
: NULL;

if ( !$product_id || !$sales_id  ) exit();

$conn = ApiFunctions::getConnection( $config );

$sales_order = new SalesOrder( $conn );


$check_order = $sales_order->read_id( $sales_id );

$check_product = $sales_order->read_product( $product_id, $sales_id );

if ( !$check_order || !$check_product ) exit();


if ( $check_order->rowCount() == 0 ) {

    Message::writeJsonMessage( "The searched order not exists" );
    exit();

}

else if ( $check_order->rowCount() == 1 && $check_product->rowCount() == 1 ) {

    Message::writeJsonMessage( "DELETE UNSUCCESSFUL: The order MUST contain at least one product" );
    exit();

} else if ( $check_product->rowCount() == 0 ) {

    Message::writeJsonMessage( "The searched product not exists" );
    exit();
}

$stmt = $sales_order->deleteProduct( $product_id, $sales_id );

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
            "message" => "Deleting successfully!"
        ]; 

        
    } else {
        $result["result"] = [
            "message" => "DELETE unsuccessful"
        ]; 
    }
    
    http_response_code(200);
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>