<?php


require_once "./api-functions.php";

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: GET");

/*----------------------FOR-DEBUG--------------------------*/
//require_once "../../vendor/autoload.php";
//$config = require_once "../../config/db_config.php";

checkMethod( "GET" );


/*---------------------------START-CONNECTION--------------------------*/

use App\model\Sale;


$conn = getConnection( $config );

$sales_orders = new Sale( $conn );

$stmt = $sales_orders->read();
    
getOutput($stmt);

/*-------------------------------FUNCTIONS-----------------------------*/


function writeApi( PDOStatement $stmt ) {

    $result = [];
    $tmp_arr = combineBySalesCode( $stmt );

    $result["result"]["sales_orders"]=[];

    foreach( $tmp_arr as $row ) {

        $tmp_row = [
            "sales_code" => $row["sales_code"],
            "sales_date" => $row["sales_date"],
            "destination_country" => $row["destination"],
            "sold_products" => $row["name"],
            "articles_number" => $row["articles_num"],
            "total_saved_co2" => $row["total_saved_co2"]
        ];

        array_push( $result["result"]["sales_orders"], $tmp_row );

    }

    header("Content-Type: application/json charset=UTF-8");
    http_response_code(200);
    return json_encode( $result );

}


?>