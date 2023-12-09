<?php

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: *");


/*----------------------------REQUIRE-FILES----------------------------*/
require "../vendor/autoload.php";
require "./file-renderer.php";
require "./error-handler.php";
$config = require_once "../config/db_config.php";

/*---------------------------REQUIRE-CLASSES---------------------------*/

use App\core\UriBuilder;
use App\core\Message;

/*--------------------------GLOBAL-VARIABLES---------------------------*/

$GLOBALS["PARAMS_URI"] = NULL;


// Assoviative array with:
// "method", "type", "request", "query"

$uri = UriBuilder::requestParts() ;

// Control Uri params

if ($uri) {
    // result = [ file, param ]
    $result = fileRenderer( $uri );

    if (!$result) {
        Message::writeJsonMessage( "Resource not found" );
        http_response_code(404);
        exit();
    }
    
    if ( $result["param"] ) {
        $GLOBALS["PARAMS_URI"] = $result["param"];
    }
        
    require_once $result["file"];
} 
elseif (!$uri) {    // $uri=FALSE only if not exists "path" & "query"

    Message::writeJsonMessage( "Not valid parameters" );
    exit();
}
elseif ( $uri["query"] && !$uri["type"] && !$uri["query"] ) {
    Message::writeJsonMessage( "Not valid parameters" );
    exit();
}


?>