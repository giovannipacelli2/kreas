<?php

namespace App\core\database;

class QueryBuilder
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function selectAll($table_name)
    {
        try {

            $q = 'SELECT * FROM ' . $table_name . ';';
            $stmt = $this->pdo->prepare($q);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';

        }
    }
}
