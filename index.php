<?php

    require './vendor/autoload.php';

    use App\core\{ Request, Router };

    $database = require './core/bootstrap.php';

    $method = Request::method();
    $uri = Request::uri();
    $query = Request::query();

    $router = Router::load( './app/routes.php' );

?>