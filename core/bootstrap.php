<?php

use App\core\App;
use App\core\database\Connection;
use App\core\database\QueryBuilder;

App::bind('config', require './config.php');

$pdo = Connection::make(App::get('config')['database']);

App::bind('database', new QueryBuilder($pdo));
