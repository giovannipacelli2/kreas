<?php

use App\core\Message;

function fileRenderer( $uri ) {
    
    $list = require_once "../config/api_config/routes.php";

    // type, request, method, query
    extract( $uri );

    if (!$request && $method=="GET" ) return FALSE;
    
    if ($request && $method=="POST" ) return FALSE;

    // example /products/all

    if ( isset( $list[$method][$type][$request] ) ){

        $res = [];

        $find_file = $list[$method][$type][$request];

        if ( $query ) {

            // Check if the entered parameters are correct

            $count = 0;
            
            foreach ( $query as $key=>$value ) {
                
                if ( in_array( $key, $find_file["query"] ) ) {
                    
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

        else {

            $res = [
                "file" => $find_file["file"],
                "param" => ""
            ];
        }

        return $res;


    } 

    elseif ( isset( $list[$method][$type] ) ) {

        $file = NULL;

        if ( $method == "GET" ) {

            // example /products/0100
            $file = $list[$method][$type]["{id}"];

        } else {

            // example /products
            $file = $list[$method][$type];
        }

        return [
            "file" => $file["file"],
            "param" => $request ? $request : ""
        ];
    }
    
}



?>