<?php

require_once "./api-functions.php";

/*-----------------------DELETE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: DELETE");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


checkMethod( "DELETE" );


/*---------------------------START-CONNECTION--------------------------*/


use App\model\Sale;


// $GLOBALS["PARAMS_URI"] = [ query => value ]

$conn = getConnection( $config );

$sales = new Sale( $conn );

// QUERY PARAM
$sales_code = $GLOBALS["PARAMS_URI"]["code"];

$stmt = $sales->delete( $sales_code );

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

        http_response_code(200);

    } else {
        $result["result"] = [
            "message" => "DELETE unsuccessful"
        ]; 
    }
    
    header("Content-Type: application/json charset=UTF-8");
    echo json_encode( $result );

}

?>