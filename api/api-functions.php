<?php

use App\core\Connection;
use App\core\Message;

function getConnection( mixed $config ) {

    $db = new Connection( $config["database"] );
    $conn = $db->getConnection();

    if ( !$conn ) {
        exit();
    }

    return $conn;
}

function checkMethod( $method ) {

    if ( $_SERVER["REQUEST_METHOD"] !== $method ) {
        http_response_code(405);
    
        Message::writeJsonMessage( "Method Not Allowed" );
    
        exit();
    }
}

function getInput() {
    $data = file_get_contents("php://input") ? file_get_contents("php://input") : $_POST;

    if (!$data) {

        Message::writeJsonMessage("No data");
        exit();
        
    }

    return json_decode($data);
}


function getOutput( $stmt ) {

/*     if ( $stmt ) {
        echo writeApi( $stmt );
    } */
    echo writeApi( $stmt );
    
    $GLOBALS["stmt"] = NULL;
    $GLOBALS["db"] = NULL;
    $GLOBALS["conn"] = NULL;
}

function combineBySalesCode( PDOStatement $stmt ) {

    $tmp_arr = [];
    
    while( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {

        if ( !isset( $tmp_arr[$row["sales_code"]] ) ){
            $tmp_arr[$row["sales_code"]] = $row;
        } else {
            $tmp_arr[$row["sales_code"]] = [
                ...$tmp_arr[$row["sales_code"]],
                "name" => $tmp_arr[$row["sales_code"]]["name"] . ", " . $row["name"]
            ];
        }
    }

    return $tmp_arr;
}

// check NOT NULL fields

function dataController( $data, $describe ) {

    $data_checker= [];

    // Push in $data_checker all NOT NULL fields
    foreach( $describe as $row ){

        $extra = isset($row["Extra"]) ? $row["Extra"] : "";

        if ( $row["Null"] == "NO" && !preg_match( "/auto_increment/", $extra ) ){
            array_push( $data_checker, $row["Field"] );
        }

    }

    //cast data in associative array;
    $data = (array) $data;

    $check = TRUE;

    // check input data integrity

    foreach( $data_checker as $param ){

        $exists = array_key_exists( $param, $data );

        // if param NOT EXISTS or an param has empty string

        if( !$exists || $data[$param] == "" ) {
            $check = false;
        }

    }

    return $check;
}

function inputChecker( $data, $stmt ) {

    $describe = $stmt->fetchAll( PDO::FETCH_ASSOC );

    if ( !dataController( $data, $describe ) ) {
        
        Message::writeMessage("Uncomplete data!");
        exit();
    }

}

?>