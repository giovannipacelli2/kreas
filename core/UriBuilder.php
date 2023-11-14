<?php

    namespace App\core;

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
    
                        $uri["query"] = [
                            ...$uri["query"],
                            $q_arr[0] => $q_arr[1]
                        ];

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