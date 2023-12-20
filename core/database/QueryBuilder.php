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

    public function selectOrderById($table_name, $id)
    {
        try {

            $id = htmlspecialchars(strip_tags($id));

            $q = 'SELECT
                    so.sales_code, so.sales_date, so.destination, so.product_id, so.n_products,
                    p.name, p.saved_kg_co2' .
                    ' FROM ' . $table_name .
                    ' WHERE so.sales_code=:id;';

            $stmt = $this->pdo->prepare($q);
            $stmt->bindParam(':id', $id, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';

        }
    }
    public function selectProductById($table_name, $id)
    {
        try {

            $id = htmlspecialchars(strip_tags($id));

            $q = 'SELECT * FROM ' . $table_name .
                    ' WHERE product_code=:id;';

            $stmt = $this->pdo->prepare($q);
            $stmt->bindParam(':id', $id, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';

        }
    }
}
