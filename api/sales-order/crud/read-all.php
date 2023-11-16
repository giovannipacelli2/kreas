<?php


use App\model\Sale;
use App\core\ApiFunctions;

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");

ApiFunctions::checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

$conn = ApiFunctions::getConnection( $config );

$sales_orders = new Sale( $conn );

$stmt = $sales_orders->read();
    
if ( $stmt ) {
    writeApi($stmt);
}

$GLOBALS["stmt"] = NULL;
$GLOBALS["db"] = NULL;
$GLOBALS["conn"] = NULL;

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];
    $tmp_arr = ApiFunctions::combineBySalesCode( $stmt );

    $result["result"]["sales_orders"]=[];

    foreach( $tmp_arr as $row ) {

        $tmp_row = [
            "sales_code" => $row["sales_code"],
            "sales_date" => $row["sales_date"],
            "destination_country" => $row["destination"],
            "sold_products" => $row["name"],
            "product_codes" => $row["product_code"],
            "articles_number" => $row["articles_num"],
            "total_saved_co2" => $row["total_saved_co2"]
        ];

        array_push( $result["result"]["sales_orders"], $tmp_row );

    }

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    echo json_encode( $result );

}


?>