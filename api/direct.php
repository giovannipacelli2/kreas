<?php


function fileManager( $uri ) {
    
    $list = require_once "../config/api_config/requests.php";

    // type, request, method
    extract( $uri );

    if (!$request && $method=="GET" ) return FALSE;
    
    if ($request && $method=="POST" ) return FALSE;

    // example /products/all

    if ( isset( $list[$method][$type][$request] ) ){

        $res = [];

        $find_file = $list[$method][$type][$request];

        if ( $query ) {

            // check if the inserted query exists in "requests" file

            if ( count($query) == 1 && array_key_exists( $find_file["query"], $query )  ) {
                
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