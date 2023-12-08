<?php

use App\model\Sales;
use App\model\SalesOrder;
use App\core\ApiFunctions;
use App\core\Message;
use App\model\Product;

/*-----------------------UPDATE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: PUT");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "PUT" );


/*------------------------GET-DATA-AND-URI-PARAMS----------------------*/

$code = isset($GLOBALS["PARAMS_URI"][0]["code"] )
? $GLOBALS["PARAMS_URI"][0]["code"] 
: NULL;

if ( !$code ) exit();

$new_code = NULL;
$res = [];

// GET DATA FROM REQUEST
$data = (array) ApiFunctions::getInput();

/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

/*-------------------CREATE-SALES-AND-SALES-INSTANCES------------------*/

$sales_order = new SalesOrder( $conn );
$sales = new Sales( $conn );


// Check if searched sales code exists

$check = $sales->readByOrder( $code );

if ( $check->rowCount() == 0 ) {

    Message::writeJsonMessage( "Order not found" );
    exit();

}

/*--------------CREATE-SALES-AND-SALES-ORDER-ARRAYS-BY-DATA------------*/

$order = [];
$products = [];

foreach ( $data as $key=>$value ) {

    if( $key == "products" ) {

        foreach ( $data[$key] as $p ) {
            $p = (array) $p;

            // Check n_products--> can't be ZERO
            if ( $p["n_products"] == 0 ) {
                Message::writeJsonMessage( "n_products can't be ZERO!" );
                http_response_code(400);
                exit();
            }

            array_push( $products, $p );
        }

    } else {
        $order[$key] = $value;
    }
}

/*------------------------------CHECK-ORDER-PARAMS-------------------------------*/

if ( count( $order ) != 0 ) {   // Array of order data

    // Necessary fields
    $describe = $sales->describe();

    // returns empty array when there are all params
    
    $orderParams = ApiFunctions::updateChecker( $order, $describe );

    if ( isset( $order["sales_date"] ) ) {
    
        ApiFunctions::checkDate( $order["sales_date"] );
    }
    
    $old_data = [];

    // orderParams contains something only when the request is not complete. 
    
    if ( count( $orderParams ) != 0 ){ 
        
        $old_data = $sales->readByOrder( $code );

        // If the order exists, it recovers the old data so that changes are what is of interest
    
        if ( $old_data->rowCount() == 0 ) {
            Message::writeJsonMessage( "Order not found" );
            exit();
        }
    
        $old_data = $old_data->fetch( PDO::FETCH_ASSOC );
    
    } else {
        
        $orderParams = array_keys( $order );
        
    }
    
    // INSERT data in SALES intance
    
    foreach( $orderParams as $field ) {
    
        if ( array_key_exists( $field, $order ) ){
            $sales->$field = $order[$field];
    
        } else {
            $sales->$field = isset( $old_data[$field] ) 
                                ? $old_data[$field] 
                                : null;
        }
    
    }

/*------------------UPDATE-ORDER-WHEN-PRODUCTS-NOT-ARE-INSERTED------------------*/
    
    if ( !$products ) {

        $stmt = $sales->update( $code );

        $affected_rows = $stmt ? $stmt->rowCount() : FALSE;

        if ( $affected_rows > 0 ) {

            $res["order"] = "Updated " . $affected_rows . " order" . isPlural( $affected_rows );

            // if the code is updated, actions must be taken considering the new code
            $new_code = $sales->sales_code;

        } else if ( $affected_rows === FALSE ) {
            exit();
        }
    }

} else {
    $new_code = $code;
}


/*-----------------------------CHECK-PRODUCT-PARAMS------------------------------*/

if ( count( $products ) != 0 ) {

    // Necessary fields
    $describe = [ "product_id", "n_products" ];
    
    foreach ( $products as $p ) {
    
        validate( $p, $describe );
    }

    // Check if there is order with searched code

    $stmt = $sales_order->read_id( $code );

    if ( $stmt->rowCount() == 0 ) {
        Message::writeJsonMessage( "Server Error" );
        http_response_code(500);
        exit();
    }

    // Get the stored order data with all assoced products
    $old_data = $stmt->fetchAll( PDO::FETCH_ASSOC );

    // Products that already exists in database
    $already_exists = array_column( $old_data, "product_id" );

    // Products send in body request
    $new_products = array_column( $products, "product_id" );

    // Duplicate checking
    ApiFunctions::checkDuplicate( $new_products, "product in order" );


    $product = new Product( $conn );

    // Check if the sended product codes exists in database
    $check = $product->checkIds( $new_products );
    
    if ( $check->rowCount() != count( $new_products ) ) {
        Message::writeJsonMessage( "Inserted Product not exists" );
        exit();
    }

    $to_update = [];
    $to_insert = [];

    foreach ( $new_products as $key=>$value ) {

        if ( in_array( $value, $already_exists ) ) {
            array_push( $to_update, $value );
        } else {
            array_push( $to_insert, $value );
        }
    }

    /*--------------------UPDATE-ORDER-WHEN-PRODUCTS-ARE-INSERT----------------------*/

    if ( $order ) {

        $stmt = $sales->update( $code );

        $affected_rows = $stmt ? $stmt->rowCount() : FALSE;

        if ( $affected_rows > 0 ) {

            $res["order"] = "Updated " . $affected_rows . " order" . isPlural( $affected_rows );

            // if the code is updated, actions must be taken considering the new code
            $new_code = $sales->sales_code;

        } else if ( $affected_rows === FALSE ) {
            exit();
        }

    }

    
    if ( $to_update ) {

        $affected_rows = 0;

        foreach ( $products as $p ) {
            
            if ( in_array( $p["product_id"], $to_update ) ) {

                // Insert data in sales_order instance
                
                $sales_order->product_id = $p["product_id"];
                $sales_order->n_products = $p["n_products"];

    /*----------------------------UPDATE-SALES-ORDER---------------------------------*/

                $stmt = $sales_order->updateProduct( $p["product_id"], $new_code );

                if ( $stmt ) {

                    $affected_rows = $affected_rows + $stmt->rowCount();
                }
            }    

            // Result message
            if ( $affected_rows > 0 ) {

                $res["product"]["updated"]= $affected_rows . " product" . isPlural( $affected_rows );

            }
        }

    }

    if ( $to_insert ) {

        $sales_order->sales_id = $new_code;

        $affected_rows = 0;

        foreach ( $products as $p ) {

            if ( in_array( $p["product_id"], $to_insert ) ) {
              
                // Insert data in sales_order instance

                $sales_order->product_id = $p["product_id"];
                $sales_order->n_products = $p["n_products"];

    /*----------------------INSERT-NEW-PRODUCT-IN-SALES-ORDER------------------------*/

                $stmt = $sales_order->insert();

                if ( $stmt ) {

                    $affected_rows = $affected_rows + $stmt->rowCount();
                }
            }            
        }

        if ( $affected_rows > 0 ) {

            // Result message
            $res["product"]["inserted"]= $affected_rows . " product" . isPlural( $affected_rows );

        }

    }

    if ( $products ) {
        
    /*---------------------DELETE-OLD-PRODUCTS-IN-SALES-ORDER------------------------*/

        $stmt = $sales_order->notInDelete( $new_products, $new_code );

        $affected_rows = $stmt ? $stmt->rowCount() : FALSE;

        if ( $affected_rows > 0 ) {

            $res["product"]["deleted"]= $affected_rows . " product" . isPlural( $affected_rows );

        } else if ( $affected_rows === FALSE ) {
            exit();
        }
    }


}


writeApi( $res );


$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( mixed $res ) {

    $result = [];
    
    if ( $res ){
        
        $result["result"] = $res;

        
    } else {
        $result["result"] = [
            "message" => "Update unsuccessful"
        ]; 
    }
    
    http_response_code(200);
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