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

    public static function errorMessage( $e, string $specific_message="" ) {

        $user_message = [
            "error" => [
                "error_type" => "Query Error",
                "error_code" => $e->getCode()
                ]
            ];

        if ( $specific_message != "" ) {

            $user_message["error"] = [
                ...$user_message["error"],
                "error_message" => $specific_message
            ];
        }
                
        header("Content-Type: application/json charset=UTF-8");
        echo json_encode( $user_message );
    
    }

}

?>