<?php

/*-----------------------INSERT-CONNECTION-HEADER----------------------*/

header("Acces-Control-Allow-Origin: *");
header("Acces-Control-Allow-Methods: *");


/*----------------------------REQUIRE-FILES----------------------------*/
require "../vendor/autoload.php";
require "./file-renderer.php";
$config = require_once "../config/db_config.php";

/*---------------------------REQUIRE-CLASSES---------------------------*/

use App\core\UriBuilder;
use App\core\Message;

/*--------------------------GLOBAL-VARIABLES---------------------------*/

$GLOBALS["PARAMS_URI"] = NULL;


// Assoviative array with:
// "type", "request", "query", "method"

$uri = UriBuilder::requestParts() ;

// Control Uri params

if ($uri) {
    // result = [ file, param ]
    $result = fileManager( $uri );

    if (!$result) {
        Message::writeMessage( "Resource not found" );
        exit();
    }

    if ( $result["param"] ) {
        $GLOBALS["PARAMS_URI"] = $result["param"];
    }
        
    require_once $result["file"];
} 
elseif (!$uri) {    // $uri=FALSE only if not exists "path" & "query"

    Message::writeMessage( "Not valid parameters" );
    exit();
}
elseif ($uri["query"] && !( $uri["type"] && $uri["query"] ) ) {
    Message::writeMessage( "Not valid parameters" );
    exit();
}


?>