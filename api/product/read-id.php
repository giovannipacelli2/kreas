<?php

require_once "./api-functions.php";

/*------------------------READ-CONNECTION-HEADER-----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

/*------------Test-Value------------*/
//$PARAMS_URI = "1345";

use App\model\Product;


$conn = getConnection( $config );

$product = new Product( $conn );
$product->product_code = $GLOBALS["PARAMS_URI"];

$stmt = $product->read_by_code();

getOutput($stmt);



/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi ( PDOStatement $stmt ) {

    $result = [];
    $tmp_arr = [];
    $code = null;

    if ( $stmt->rowCount() == 0 ) {

        $result["result"] = [
            "message" => "Resource Not Found"
        ];

    } else {
        
        while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
    
            $code = $row["product_code"];
        
            $array_row = [
                "name" => $row["name"],
                "saved_kg_co2" => $row["saved_kg_co2"]
            ];
        
            array_push( $tmp_arr, $array_row  );
        
        }
    
        $result["result"] = [
            $code => $tmp_arr
        ]; 

        http_response_code(200);
    }
    header("Content-Type: application/json charset=UTF-8");
    return json_encode( $result );

}

?>