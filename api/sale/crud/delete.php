<?php

use App\model\Sales;
use App\core\ApiFunctions;

/*-----------------------DELETE-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: DELETE");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ApiFunctions::checkMethod( "DELETE" );


/*------------------------GET-DATA-AND-URI-PARAMS----------------------*/

$code = isset($GLOBALS["PARAMS_URI"][0]["code"] )
? $GLOBALS["PARAMS_URI"][0]["code"] 
: NULL;

if ( !$code ) exit();

/*---------------------------START-CONNECTION--------------------------*/
$conn = ApiFunctions::getConnection( $config );

$sales = new Sales( $conn );

// QUERY PARAM
$code = $GLOBALS["PARAMS_URI"][0]["code"];

$stmt = $sales->delete( $code );

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