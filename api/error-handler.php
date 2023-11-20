<?php

    //error_reporting( E_ALL );
    //ini_set( "display_errors", 0 );

    
    function errorHandler( $err_n, $err_str, $err_file, $err_line ){

        $date = new DateTime("now");
        $date = $date->format( "Y-m-d H:i:s" );
        
        $message = "Date: $date" . PHP_EOL . "Error: [$err_n] $err_str - $err_file : $err_line" . PHP_EOL . PHP_EOL;
        error_log( $message, 3, "../error/error-log.txt" );
        
    }
    function exceptionHandler( $e ){

        $code = $e->getCode();
        $msg = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        $date = new DateTime("now");
        $date = $date->format( "Y-m-d H:i:s" );
        
        $message = "Date: $date" . PHP_EOL . "Error: [$code] $msg - $file : line: $line" . PHP_EOL . PHP_EOL;
        error_log( $message, 3, "../error/exception-log.txt" );
        
    }
    
    set_error_handler( "errorHandler" );
    //set_exception_handler("exceptionHandler");

?>