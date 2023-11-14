<?php

    redirect( "./api" );

    
    exit();
    
    function redirect( string $where ) {
        header( "Location: " . $where );
    }

?>