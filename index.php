<?php

    require './vendor/autoload.php';

    use App\core\{ Request, Router };

    $database = require './core/bootstrap.php';

    $method = Request::method();
    $uri = Request::uri();

    // Contains QUERY
    //die(var_dump($_REQUEST));

    $router = Router::load( './app/routes.php' );

    $router->direct( $uri, $method );

?>