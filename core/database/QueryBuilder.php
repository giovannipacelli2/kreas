<?php

namespace App\core\database;

class QueryBuilder
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function describe($table_name)
    {
        try {

            $q = 'DESCRIBE ' . $table_name . ';';
            $stmt = $this->pdo->prepare($q);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';
            exit();

        }
    }

    /*-----------------------------------------------------GET-METHODS-----------------------------------------------------*/

    public function selectAll($table_name)
    {
        try {

            $q = 'SELECT * FROM ' . $table_name . ';';
            $stmt = $this->pdo->prepare($q);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';
            exit();

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
            exit();

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
            exit();

        }
    }

    /*--------------------------------------------------------QUERY--------------------------------------------------------*/

    public function getCo2FromOrders($table_name)
    {
        try {

            $q = 'SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    FROM (
                            SELECT ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM  ' . $table_name . ') AS j;';

            $stmt = $this->pdo->prepare($q);
            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';
            exit();

        }
    }

    public function getCo2FromDataInterval($table_name, $date)
    {
        try {

            $start = htmlspecialchars(strip_tags($date['start']->format('Y-m-d H:i:s')));
            $end = htmlspecialchars(strip_tags($date['end']->format('Y-m-d H:i:s')));

            $q = 'SELECT SUM(j.tot_co2_prod) AS `co2_saved`
                    
                    FROM (
                            SELECT so.sales_date, ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM ' . $table_name .
                        ") AS j
                    WHERE STR_TO_DATE(j.sales_date, '%Y-%m-%d %H:%i:%s') > STR_TO_DATE(:start, '%Y-%m-%d %H:%i:%s')
                    AND STR_TO_DATE(j.sales_date, '%Y-%m-%d %H:%i:%s') < STR_TO_DATE(:end, '%Y-%m-%d %H:%i:%s');";

            $stmt = $this->pdo->prepare($q);

            $stmt->bindParam(':start', $start, \PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;
        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';
            exit();

        }
    }

    public function getCo2FromDestination($table_name, $destination)
    {

        $destination = htmlspecialchars(strip_tags($destination));

        try {

            $q = 'SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    FROM (
                            SELECT ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM ' . $table_name .
                            ' WHERE so.destination = :destination
                        ) AS j;';

            $stmt = $this->pdo->prepare($q);

            $stmt->bindParam(':destination', $destination, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            echo 'An error occurred while executing the query. Try later.';
            exit();

        }

    }
}
