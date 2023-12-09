<?php

    namespace App\core;

    /*---------------------------------------------------------------------------------------
    |                                                                                       |
    |    UriBuilder make a associative array starting by URI.                               |
    |    Example:                                                                           |
    |                                                                                       |
    |    GET -> https://{domain}/api/sales-orders/interval-date?start={start}&end={end}     |
    |                                                                                       |
    |     [                                                                                 |
    |        "method" => "GET",                                                             |
    |        "type" => "sales-orders",                                                      |
    |        "request" => "interval-date",                                                  |
    |        "query" => [                                                                   |
    |            [ "start" => "value" ],                                                    |
    |            [ "end" => "value" ]                                                       |
    |        ]                                                                              |
    |    ]                                                                                  |
    |                                                                                       |
    ---------------------------------------------------------------------------------------*/

    class UriBuilder {


        private function __construct(){}

        public static function requestParts(){

            $uri = null;

            // FIND TYPE

            if ( isset($_SERVER['PATH_INFO']) ) {

                $path = trim( $_SERVER['PATH_INFO'], "/" );

                $arr_path = explode( "/", $path );

                if( count( $arr_path ) <= 2  && count( $arr_path ) > 0 ) {

                    $uri["type"] = $arr_path[0];

                    $uri["request"] = isset($arr_path[1]) ? $arr_path[1] : FALSE ;
                } else {
                    $uri["type"] = FALSE;
                    $uri["request"] = FALSE;
                }
            } else {
                $uri["type"] = FALSE;
                $uri["request"] = FALSE;
            }

            // FIND QUERY


            if ( isset($_SERVER['QUERY_STRING']) ) {

                $query = trim( $_SERVER['QUERY_STRING'] );

                $arr_query = explode( "&", $query );

                if ( count($arr_query) > 0 ) {

                    $uri["query"] = [];

                    foreach( $arr_query as $q ) {

                        $q_arr = explode( "=", $q );
                        
                        $key = isset( $q_arr[0] ) ? $q_arr[0] : "";
                        $value = isset( $q_arr[1] ) ? $q_arr[1] : "";
                        
                        $data = [ $key => $value ];
                        
                        array_push( $uri["query"], $data );
                        
                    }
                }
                
            } else {
                $uri["query"] = FALSE;
            }

            // FIND METHOD

            if ( isset($_SERVER['REQUEST_METHOD']) ) {
                $uri["method"] = $_SERVER['REQUEST_METHOD'];
            } else {
                $uri["method"] = FALSE;
            }

            // FINAL CHECK

            if ( !$uri["type"] && !$uri["request"] && !$uri["query"] ){
                return FALSE;
            }


            return $uri;

        }
    }

?>