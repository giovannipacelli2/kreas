<?php

namespace App\core\database;

use App\core\Response;

class QueryBuilder
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /*----------------------------------------------------CHECK-METHODS----------------------------------------------------*/

    public function describe($table_name)
    {
        try {

            $q = 'DESCRIBE ' . $table_name . ';';
            $stmt = $this->pdo->prepare($q);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

        }
    }

    public function checkField($table_name, $field, $values)
    {

        $values = self::queryValuesBuilder($values);

        try {

            $q = 'SELECT * FROM ' . $table_name .
            ' WHERE ' . $field . ' IN (' . implode(', ', array_column($values, 'placeholder')) . ');';

            $stmt = $this->pdo->prepare($q);

            foreach ($values as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }

            $stmt->execute();

            if (!$stmt || $stmt->rowCount() == 0 || $stmt->rowCount() != count($values)) {
                return false;
            }
            if ($stmt->rowCount() == count($values)) {
                return true;
            }

        } catch (\Exception $e) {

            self::manageError($e);

        }
    }

    public function checkProductInOrder($table_name, $sales_id, $product_id)
    {

        try {

            $q = 'SELECT * FROM ' . $table_name .
            ' WHERE sales_id=:sales_id' .
            ' AND product_id=:product_id';

            $stmt = $this->pdo->prepare($q);

            $stmt->bindParam(':sales_id', $sales_id, \PDO::PARAM_STR);
            $stmt->bindParam(':product_id', $product_id, \PDO::PARAM_STR);

            $stmt->execute();

            if (!$stmt || $stmt->rowCount() == 0) {
                return false;
            }

            return true;

        } catch (\Exception $e) {

            self::manageError($e);

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

            self::manageError($e);

        }
    }

    public function selectAllByField($table_name, $field, $values)
    {

        $values = self::queryValuesBuilder($values);

        try {

            $q = 'SELECT * FROM ' . $table_name .
            ' WHERE ' . $field . ' IN (' . implode(', ', array_column($values, 'placeholder')) . ');';

            $stmt = $this->pdo->prepare($q);

            foreach ($values as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

        }
    }

    public function selectOrderById($table_name, $id)
    {
        try {

            $id = htmlspecialchars(strip_tags($id));

            $q = 'SELECT * FROM ' . $table_name .
                    ' WHERE so.sales_code=:id;';

            $stmt = $this->pdo->prepare($q);
            $stmt->bindParam(':id', $id, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

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

            self::manageError($e);

        }
    }

    /*-----------------------------------------------------POST-METHODS----------------------------------------------------*/

    public function insert($table_name, $data)
    {
        // wants $data like this:
        // (array) :
        //      'product_id' => '0010',
        //      'n_products' => '3'

        $params = [];

        foreach ($data as $key=>$value) {

            $tmp = [
                'field' => htmlspecialchars(strip_tags($key)),
                'placeholder' => ':' . htmlspecialchars(strip_tags($key)),
                'value' => htmlspecialchars(strip_tags($value)),
            ];

            array_push($params, $tmp);
        }

        try {

            $q = 'INSERT INTO ' . $table_name . ' (' . implode(', ', array_column($params, 'field')) . ') '
                    . 'VALUES(' . implode(', ', array_column($params, 'placeholder')) . ')';

            $stmt = $this->pdo->prepare($q);

            foreach ($params as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            if ($e->getCode() != 23000) {
                self::manageError($e);
            }

            exceptionHandler($e);

            return false;

        }
    }

    /*------------------------------------------------------PUT-METHODS----------------------------------------------------*/

    public function update($table_name, $data, $field, $old_id)
    {
        // wants $data like this:
        // (array) :
        //      'product_id' => '0010',
        //      'n_products' => '3'

        $params = [];

        $field = htmlspecialchars(strip_tags($field));
        $old_id = htmlspecialchars(strip_tags($old_id));

        foreach ($data as $key=>$value) {

            $tmp = [
                'field' => htmlspecialchars(strip_tags($key)),
                'placeholder' => ':' . htmlspecialchars(strip_tags($key)),
                'value' => htmlspecialchars(strip_tags($value)),
                'set' => htmlspecialchars(strip_tags($key)) . '=:' . htmlspecialchars(strip_tags($key)),
            ];

            array_push($params, $tmp);
        }

        try {

            $q = 'UPDATE ' . $table_name . ' SET ' . implode(', ', array_column($params, 'set')) .
                    ' WHERE ' . $field . '=:code' . ';';

            $stmt = $this->pdo->prepare($q);

            foreach ($params as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }
            $stmt->bindParam(':code', $old_id);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            if ($e->getCode() != 23000) {
                self::manageError($e);
            }

            exceptionHandler($e);

            return false;

        }
    }

    public function updateProducts($table_name, $data, $sales_id, $old_product_id)
    {

        $params = [];

        foreach ($data as $key=>$value) {

            $tmp = [
                'field' => htmlspecialchars(strip_tags($key)),
                'placeholder' => ':' . htmlspecialchars(strip_tags($key)),
                'value' => htmlspecialchars(strip_tags($value)),
                'set' => htmlspecialchars(strip_tags($key)) . '=:' . htmlspecialchars(strip_tags($key)),
            ];

            array_push($params, $tmp);
        }

        $sales_id = htmlspecialchars(strip_tags($sales_id));
        $old_product_id = htmlspecialchars(strip_tags($old_product_id));

        try {

            $q = 'UPDATE ' . $table_name . ' SET ' . implode(', ', array_column($params, 'set')) .
                    ' WHERE sales_id=:sales_id AND product_id=:old_product_id;';

            $stmt = $this->pdo->prepare($q);

            foreach ($params as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }

            $stmt->bindParam(':sales_id', $sales_id, \PDO::PARAM_STR);
            $stmt->bindParam(':old_product_id', $old_product_id, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

        }
    }

    /*---------------------------------------------------DELETE-METHODS----------------------------------------------------*/

    public function deleteField($table_name, $field, $values)
    {
        $values = self::queryValuesBuilder($values);

        try {

            $q = 'DELETE FROM ' . $table_name .
            ' WHERE ' . $field . ' IN (' . implode(', ', array_column($values, 'placeholder')) . ');';

            $stmt = $this->pdo->prepare($q);

            foreach ($values as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }

            $stmt->execute();

            if (!$stmt || $stmt->rowCount() == 0 || $stmt->rowCount() != count($values)) {
                return false;
            }
            if ($stmt->rowCount() == count($values)) {
                return true;
            }

        } catch (\Exception $e) {

            self::manageError($e);

        }
    }

    public function deleteProductsOrder($table_name, $sales_id, $product_id)
    {
        try {

            $sales_id = htmlspecialchars(strip_tags($sales_id));
            $product_id = htmlspecialchars(strip_tags($product_id));

            $q = 'DELETE FROM ' . $table_name .
                    ' WHERE product_id=:product_id' .
                    ' AND sales_id=:sales_id;';

            $stmt = $this->pdo->prepare($q);
            $stmt->bindParam(':sales_id', $sales_id, \PDO::PARAM_STR);
            $stmt->bindParam(':product_id', $product_id, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

        }
    }

    // wants condition as [ 'field' => 'field_name', 'value' => value ]
    public function notInDelete($table_name, $field, $values, $condition)
    {

        $values = self::queryValuesBuilder($values);

        try {

            $q = 'DELETE FROM ' . $table_name .
            ' WHERE ' . $field . ' NOT IN (' . implode(', ', array_column($values, 'placeholder')) . ')'
            . ' AND ' . $condition['field'] . '=:code;';

            $stmt = $this->pdo->prepare($q);

            foreach ($values as $param) {
                $stmt->bindParam($param['placeholder'], $param['value']);
            }

            $stmt->bindParam(':code', $condition['value'], \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

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

            self::manageError($e);

        }
    }

    public function getCo2FromDataInterval($table_name, $date)
    {
        try {

            $start = htmlspecialchars(strip_tags($date['start']->format('Y-m-d H:i:s')));
            $end = htmlspecialchars(strip_tags($date['end']->format('Y-m-d H:i:s')));

            $q = 'SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    
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

            self::manageError($e);

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

            self::manageError($e);

        }

    }

    public function getCo2FromProduct($table_name, $product_id)
    {

        $product_id = htmlspecialchars(strip_tags($product_id));

        try {

            $q = 'SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    FROM (
                            SELECT ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM ' . $table_name .
                            ' WHERE p.product_code = :product_id
                        ) AS j;';

            $stmt = $this->pdo->prepare($q);

            $stmt->bindParam(':product_id', $product_id, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch (\Exception $e) {

            self::manageError($e);

        }

    }

    /*---------------------------------------------------PRIVATE-FUNCTIONS-------------------------------------------------*/

    // Wants $values as array:  [value1, value2]
    // or single value as string: 'value'

    private function queryValuesBuilder($values)
    {
        if (is_array($values)) {
            $tmp = [];

            for ($i = 0; $i < count($values); $i++) {
                array_push($tmp, [
                    'placeholder'=> ':id' . $i,
                    'value'=> $values[$i],
                ]);
            }

            $values = $tmp;
            $tmp = null;
        } else {
            $tmp = [
                'placeholder'=> ':id',
                'value'=> $values,
            ];
            $values = [];
            array_push($values, $tmp);
        }

        return $values;
    }

    private function manageError($e)
    {
        exceptionHandler($e);

        Response::json([], 500, 'An error occurred while executing the query. Try later.');
    }
}
