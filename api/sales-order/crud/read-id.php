<?php

use App\model\Sale;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");


ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/


$conn = ApiFunctions::getConnection( $config );

$sales = new Sale( $conn );
$sales->sales_code = $GLOBALS["PARAMS_URI"];

$stmt = $sales->read_by_code();

if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;


/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];

    if ( $stmt->rowCount() == 0 ) {

        $result["result"] = [
            "message" => "Resource Not Found"
        ];

    } else {
        
        $tmp_arr = ApiFunctions::combineBySalesCode( $stmt );

        $key = array_key_first( $tmp_arr );
        $row = $tmp_arr[$key];

    
        $result["result"] = [
            "sales_code" => $row["sales_code"],
            "sales_date" => $row["sales_date"],
            "destination_country" => $row["destination"],
            "sold_products" => $row["name"],
            "product_codes" => $row["product_code"],
            "articles_number" => $row["articles_num"],
            "total_saved_co2" => $row["total_saved_co2"]
        ]; 

        http_response_code(200);
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>