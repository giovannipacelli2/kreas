<?php

    namespace App\core\database;

    class QueryBuilder {
        protected $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }
    }

?>