<?php

require_once "./api-functions.php";

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

use App\model\Product;


$conn = getConnection( $config );

$product = new Product( $conn );

$stmt = $product->read();

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];
    $result["allProducts"] = [];
    
    while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
    
        $array_row = [
            "product_code" => $row["product_code"],
            "name" => $row["name"],
            "saved_kg_co2" => $row["saved_kg_co2"]
        ];
    
        array_push( $result["allProducts"], $array_row );
    
    }

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}


?>