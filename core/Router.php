<?php

namespace App\core;

class Router
{
    public $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public static function load( $file )
    {
        $router = new static;

        $router = require $file;

        return $router;
    }

    public function get( $uri, $controller )
    {
        $this->routes['GET'][$uri] = $controller; 
    }

    public function post( $uri, $controller )
    {
        $this->routes['POST'][$uri] = $controller; 
    }

    public function put( $uri, $controller )
    {
        $this->routes['PUT'][$uri] = $controller; 
    }

    public function delete( $uri, $controller )
    {
        $this->routes['DELETE'][$uri] = $controller; 
    }


    
}
