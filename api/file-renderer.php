<?php

use App\core\Message;

function fileRenderer( $uri ) {
    
    $list = include_once("../config/api_config/routes.php");
    

    if (!$list) {
        Message::writeJsonMessage( "Error loading routes file" );
        exit();
    }

    // Create variables:
    // $method, $type, $request, $query
    extract( $uri );

    if (!$request && $method=="GET" ) return FALSE;
    
    if ($request && $method=="POST" ) return FALSE;

    // example: GET -> /products/all

    if ( isset( $list[$method][$type][$request] ) ){

        $res = [];

        $find_file = $list[$method][$type][$request];

        // examples: 
        
        // PUT -> /products/query?code=0100
        //   or
        // GET -> /sales-orders/destination-co2?country=USA

        if ( $query && isset($find_file["query"]) ) {

            // Check how many parameters are entered and 
            // if they are the same of the routes.php file

            $count = 0;

            foreach ( $query as $q_row ){

                // Check if the request keys are correct
                if ( in_array( array_key_first($q_row), $find_file["query"] ) ) {
                    
                    $count++;
                } else {
                    $count = 0;
                    return;
                }
            }
            
            // checks if the number of parameters matches those of the routes.php file

            if ( count( $find_file["query"] ) == $count ) {
                $res = [
                    "file" => $find_file["file"],
                    "param" => $query
                ];

            } else {
                $res = FALSE;
            }
        } 

        // Check the compliance of the request
        elseif ( $query && !isset($find_file["query"]) ){
            $res = FALSE;
        }

        else {

            $res = [
                "file" => $find_file["file"],
                "param" => ""
            ];
        }

        return $res;


    } 

    // When "request" isn't present
    elseif ( isset( $list[$method][$type] ) ) {

        if( $query ) return FALSE;

        $file = NULL;

        if ( $method == "GET" ) {

            // example GET->  /products/0100
            $file = $list[$method][$type]["{id}"];

        } else {

            // example POST ->  /sales-orders/
            $file = $list[$method][$type];
        }

        return [
            "file" => $file["file"],
            "param" => $request ? $request : ""
        ];
    }
    
}



?>