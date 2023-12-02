<?php

namespace App\model;

use App\core\Message;
use DateTime;
use PDO;
use PDOException;

class SalesOrder {
    
    public $conn;
    public $product_id, $n_products, $sales_id;

    protected $table_name = "sales_orders";
    
    protected $table_join = "products AS p JOIN (".
        "SELECT * FROM ".
        "sales as s JOIN sales_orders as o ".
        "ON s.sales_code = o.sales_id".
    ") AS so";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }
    
/*--------------------------------------------CRUD-METHODS--------------------------------------------*/
    
    /*------------------------------READ-ALL-------------------------------*/

    function read_all(){

        try{
    
            $q = " SELECT
                    so.sales_code, so.sales_date, so.destination, so.product_id, so.n_products,
                    p.name, p.saved_kg_co2
                    FROM " . $this->table_join .
                    " ON p.product_code = so.product_id
                    ORDER BY so.sales_code;";

            $stmt = $this->conn->prepare( $q );

            $stmt->execute();

            return $stmt;
    
        } catch( PDOException $e ) {
    
            exceptionHandler( $e );
    
            Message::errorMessage( $e );
    
        }
    
    }

    /*-------------------------------READ-ID-------------------------------*/

    function read_id( string $sales_code ){

        $sales_code = htmlspecialchars( strip_tags( $sales_code ) );

        try{
    
            $q = " SELECT
                    so.sales_code, so.sales_date, so.destination, so.product_id, so.n_products,
                    p.name, p.saved_kg_co2
                    FROM " . $this->table_join .
                    " ON p.product_code = so.product_id
                    WHERE so.sales_code = :sales_code;";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":sales_code", $sales_code, PDO::PARAM_STR );

            $stmt->execute();

            return $stmt;
    
        } catch( PDOException $e ) {
    
            exceptionHandler( $e );
    
            Message::errorMessage( $e );
    
        }
    
    }
    
    /*-------------------------------INSERT--------------------------------*/

    function insert(){

        $this->product_id = htmlspecialchars( strip_tags( $this->product_id ) );
        $this->n_products = htmlspecialchars( strip_tags( $this->n_products ) );
        $this->sales_id = htmlspecialchars( strip_tags( $this->sales_id ) );

        try{
    
            $q = "INSERT INTO 
            " . $this->table_name . " ( product_id, n_products, sales_id ) VALUES( ".
            ":product_id, :n_products, :sales_id )";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":product_id", $this->product_id, PDO::PARAM_STR );
            $stmt->bindParam( ":n_products", $this->n_products, PDO::PARAM_INT );
            $stmt->bindParam( ":sales_id", $this->sales_id, PDO::PARAM_STR );

            $stmt->execute();

            return $stmt;
    
        } catch( PDOException $e ) {
    
            exceptionHandler( $e );
    
            Message::errorMessage( $e );
    
        }
    
    }


    /*-------------------------------DELETE--------------------------------*/

/*     function notInDelete( $old_sales_code ) {

        $old_sales_code = htmlspecialchars( strip_tags( $old_sales_code ) );
        
        // products in body of request
        $products = array_map( function( $prod ){

            $prod = (array) $prod;
            $res = htmlspecialchars( strip_tags( $prod["product_code"] ) );

            $res = filter_var( $res, FILTER_SANITIZE_NUMBER_INT);

            if (!$res) {
                Message::writeJsonMessage("Error in product_code validation!");
                exit();
            }

            return $res;

        }, (array) $this->products );

        $del_id = "'" . implode( "','", $products ) . "'";

        
        try{
            
            // deletes all products with the "old" sales_code 
            // that don't have the IDs just entered
    
            $q_delete = "DELETE FROM " . $this->table_name .
            " WHERE sales_code = :sales_code 
            AND product_id NOT IN ( " . $del_id . " );";
            
            $stmt = $this->conn->prepare( $q_delete );
            
            $stmt->bindParam( ":sales_code", $old_sales_code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
    } */
    
    /*-----------------------------------------------QUERY------------------------------------------------*/

    /*------------------------------TOTAL-CO2------------------------------*/

    function getCo2FromOrders() {

        try{

            $q = "SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    FROM (
                            SELECT ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM  " . $this->table_join .
                            " ON p.product_code = so.product_id
                        ) AS j;";

            $stmt = $this->conn->prepare( $q );
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }

    }

    /*----------------------TOTAL-CO2-IN-DATE-INTERVAL---------------------*/

    function getCo2FromOrdersDate( DateTime $start, DateTime $end ) {

        $start = htmlspecialchars( strip_tags( $start->format( "Y-m-d H:i:s" ) ) );
        $end = htmlspecialchars( strip_tags( $end->format( "Y-m-d H:i:s" ) ) );
        

        try{

            $q = "SELECT SUM(j.tot_co2_prod) AS `co2_saved`
                    
                    FROM (
                            SELECT so.sales_date, ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM " . $this->table_join .
                            " ON p.product_code = so.product_id
                        ) AS j
                    WHERE STR_TO_DATE(j.sales_date, '%Y-%m-%d %H:%i:%s') > STR_TO_DATE(:start, '%Y-%m-%d %H:%i:%s')
                    AND STR_TO_DATE(j.sales_date, '%Y-%m-%d %H:%i:%s') < STR_TO_DATE(:end, '%Y-%m-%d %H:%i:%s');";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam(":start", $start, PDO::PARAM_STR);
            $stmt->bindParam(":end", $end, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }

    }
    
    /*-----------------------TOTAL-CO2-BY-DESTINATION----------------------*/

    function getCo2FromDestination( string $destination ) {

        $destination = htmlspecialchars( strip_tags( $destination ) );
        

        try{

            $q = "SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    FROM (
                            SELECT ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM " . $this->table_join . 
                            " ON p.product_code = so.product_id
                            WHERE so.destination = :destination
                        ) AS j;";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam(":destination", $destination, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }

    }
    /*-------------------------TOTAL-CO2-BY-PRODUCT------------------------*/

    function getCo2FromProduct( string $product_id ) {

        $product_id = htmlspecialchars( strip_tags( $product_id ) );
        
        try{

            $q = "SELECT SUM(j.tot_co2_prod) AS `total_co2_saved`
                    FROM (
                            SELECT ( p.saved_kg_co2 * so.n_products ) as `tot_co2_prod`
                            FROM " . $this->table_join .
                            " ON p.product_code = so.product_id
                            WHERE p.product_code = :product_id
                        ) AS j;";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam(":product_id", $product_id, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }

    }

    /*-------------------------------------------OTHER-FUNCTIONS------------------------------------------*/


    function operationMessage( int $n_rows ) : string {

        if ( $n_rows > 0 ) {
            $s = $n_rows == 1 ? "" : "s";

            return $n_rows . " row" . $s;

        }
        return "";
    }

}

?>