<?php

namespace App\core;

class Response
{
    public static function json($data, $statusCode, $message = '')
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $result['message'] = $message;

        if (!$data) {
            $result['data'] = [];
        } else {
            $result['data'] = $data;
        }
        echo json_encode($result);
        exit();
    }
}
