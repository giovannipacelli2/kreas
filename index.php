<?php

require './vendor/autoload.php';
require './core/error-handler.php';

use App\core\Request;
use App\core\Router;

$database = require './core/bootstrap.php';

$method = Request::method();
$uri = Request::uri();

// Contains QUERY
//die(var_dump($_REQUEST));

$router = Router::load( './app/routes.php' );

$router->direct( $uri, $method );
