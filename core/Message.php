<?php

namespace App\core;

class Message {
    private $message;

    private function __construct($message)
    {
        $this->message = $message;
    }

    public static function writeMessage( string $message ) {
            
        $message = ["message" => $message];
        echo json_encode( $message );
    }
    public static function writeJsonMessage( string $message ) {

        header("Content-Type: application/json charset=UTF-8");
            
        $message = ["message" => $message];
            echo json_encode( $message );
    }

}

?>