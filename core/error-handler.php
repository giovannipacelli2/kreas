<?php

use App\core\Response;

// In development comment these lines of code

/* -----> */ //error_reporting(E_ALL);
/* -----> */ //ini_set('display_errors', 0);
/* -----> */ //set_error_handler('errorHandler');

function fileCheck($path)
{

    if (file_exists($path)) {
        return $path;
    } else {

        if (!file_exists('error')) {
            mkdir('error');
        }
        fopen($path, 'w');

        return $path;
    }
}

function errorHandler($err_n, $err_str, $err_file, $err_line)
{
    $date = new DateTime('now');
    $date = $date->format('Y-m-d H:i:s');

    $message = "Date: $date" . PHP_EOL . "Error: [$err_n] $err_str - $err_file : $err_line" . PHP_EOL . PHP_EOL;

    $path = fileCheck('error/error-log.txt');

    error_log($message, 3, $path);

    Response::json([], 400, 'Server Error');

}
function exceptionHandler($e)
{

    $code = $e->getCode();
    $msg = $e->getMessage();
    $file = $e->getFile();
    $line = $e->getLine();

    $date = new DateTime('now');
    $date = $date->format('Y-m-d H:i:s');

    $message = "Date: $date" . PHP_EOL . "Error: [$code] $msg - $file : line: $line" . PHP_EOL . PHP_EOL;

    $path = fileCheck('error/exception-log.txt');

    error_log($message, 3, $path);

}

//set_exception_handler("exceptionHandler");
