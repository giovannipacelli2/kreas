<?php

use App\model\Sales;
use App\model\SalesOrder;
use App\core\ApiFunctions;
use App\core\Message;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: POST");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "POST" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$sales = new Sales( $conn );

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();


/*-------------------------VALIDATION-INPUT-DATA-----------------------*/

// Necessary fields
$data_fields = [ "sales_code", "sales_date", "destination", "products" ];

validate( $data, $data_fields );

if ( isset( $data["sales_date"] ) ) {
    
    ApiFunctions::checkDate( $data["sales_date"] );
}

// check that product isn't empty array

if ( count( $data["products"] ) == 0 ) {
    
    Message::writeJsonMessage( "Products can't be empty!" );
    exit();
}

// Necessary fields in "products"
$product_fields = [ "product_id", "n_products" ];

// array of product_id
$new_products = [];

foreach( $data["products"] as $product ) {

    $product = (array) $product;
    
    validate( $product, $product_fields );

    // Check n_products
    if ( $product["n_products"] == 0 ) {
                
        Message::writeJsonMessage( "n_products can't be ZERO!" );
        http_response_code(400);
        exit();

    } else if ( (int) $product["n_products"] == 0 ) {
        Message::writeJsonMessage("n_products format isn't valid");
        http_response_code(400);
        exit();
    }

    array_push( $new_products, $product["product_id"] );
    

}


// Duplicate checking
ApiFunctions::checkDuplicate( $new_products, "product in order" );


$res = [];
// Inserting data in sales instance

$sales->sales_code = $data["sales_code"];
$sales->sales_date = $data["sales_date"];
$sales->destination = $data["destination"];

$stmt = $sales->insert();

if ( $stmt->rowCount() > 0 ) {

    $sales_order = new SalesOrder( $conn );

    $res["order"] = "Inserted " . $stmt->rowCount() . " order" . isPlural( $stmt->rowCount() );
    $inserted_product = 0;

    foreach ( $data["products"] as $product ) {

        $product = (array) $product;

        // Inserting data in sales_order instance

        $sales_order->sales_id = $data["sales_code"];
        $sales_order->product_id = $product["product_id"];
        $sales_order->n_products = $product["n_products"];

        $stmt = $sales_order->insert();

        if( $stmt->rowCount() == 0 ) {
            Message::writeJsonMessage( "Error!" );
            exit();
        } else {
            $inserted_product++;
        }

    }

    $res["product"] = "Inserted " . $inserted_product . " product" . isPlural( $inserted_product );
}


if ( $stmt ) {
    writeApi( $res );
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( $res ) {

    $result = [];
    
    if ( count( $res ) > 0 ){


        $result["result"] = $res;

        http_response_code(201);

    }
    else {
        
        $result["result"] = [
            "message" => "Insert unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

function validate( $data, $fields ) {

    // Check the correctness of data
    $validation = ApiFunctions::existsAllParams( $data, $fields );

    if ( !$validation ) {

        Message::writeJsonMessage( "Bad request" );
        http_response_code(400);
        exit();
    }

    return $validation;
}

function isPlural( int $num ) {

    $s = $num == 1 ? "" : "s";
    return $s;
}

?>