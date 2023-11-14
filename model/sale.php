<?php

namespace App\model;

use Exception;
use PDO;
use PDOException;

class Sale{

    public $sales_code, $sales_date, $destination, $product_id, $product_num, $saved_kg_co2;

    private $conn;
    private $table_name = "sales_orders" ;
    private $table_join = "products AS p JOIN sales_orders AS so";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /*------------------------------DESCRIBE-------------------------------*/
        
    public function describe() {
            
        try{
            
            $q = "DESCRIBE " . $this->table_name . ";";
            $stmt = $this->conn->prepare( $q );
            
            $stmt->execute();
            
            return $stmt;
            
        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            
            } catch( Exception $e ) {
                $e->getMessage();
            }
            
        }
    
/*--------------------------------------------CRUD-METHODS--------------------------------------------*/
    
    /*--------------------------------READ---------------------------------*/

    public function read() {
        
        try{

            $q = "SELECT p.name, so.sales_code, so.sales_date, so.destination, p.saved_kg_co2,
            SUM(p.saved_kg_co2) OVER ( PARTITION BY so.sales_code ) AS `total_saved_co2`,
            COUNT(so.sales_code) OVER ( PARTITION BY so.sales_code ) AS `articles_num`
            FROM " . $this->table_join .
            " ON p.product_code = so.product_id
            ORDER BY so.sales_code;";

            $stmt = $this->conn->prepare( $q );

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            
            } catch( Exception $e ) {
                $e->getMessage();
            }

    }

    public function read_by_code() {

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        
        try{
            $q = "SELECT p.name, so.sales_code, so.sales_date, so.destination, p.saved_kg_co2,
                    SUM(p.saved_kg_co2) OVER ( PARTITION BY so.sales_code ) AS `total_saved_co2`,
                    COUNT(so.sales_code) OVER ( PARTITION BY so.sales_code ) AS `articles_num`
                    FROM ". $this->table_join .
                    " ON p.product_code = so.product_id
                    WHERE so.sales_code = :sales_code
                    ORDER BY so.sales_code;";

            $stmt = $this->conn->prepare( $q );
            $stmt->bindParam( ':sales_code', $this->sales_code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            
            } catch( Exception $e ) {
                $e->getMessage();
            }

    }
    /*-------------------------------INSERT--------------------------------*/

    function insert(){

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );
        $this->product_id = htmlspecialchars( strip_tags( $this->product_id ) );

        $products = explode( ",", $this->product_id );

        try{

            $q_check = "SELECT * FROM " . $this->table_name . " " .
                        "WHERE 
                            sales_code=:sales_code AND
                            sales_date=:sales_date AND
                            destination=:destination AND
                            product_id=:product_id;";

            $q = "INSERT INTO " . $this->table_name . " " .
                    "( sales_code, sales_date, destination, product_id ) VALUES(
                        :sales_code, :sales_date, :destination, :product_id
                    )";
    
            $query_ok = TRUE;
    
            for ( $i = 0; $i < count($products); $i++ ){
    
                $product = trim( $products[$i] );

                /*----------------Check-if-row-already-exists----------------*/
                
                $check = $this->conn->prepare($q_check);
                
                $check->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
                $check->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
                $check->bindParam( ":destination", $this->destination, PDO::PARAM_STR );
                $check->bindParam( ":product_id", $product, PDO::PARAM_STR );
                
                $check->execute();
                
                /*----------------It-does-the-insert-normally----------------*/

                if ( $check->rowCount() < 1 ) {
  
                    $stmt = $this->conn->prepare($q);
        
                    $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
                    $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
                    $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );
                    $stmt->bindParam( ":product_id", $product, PDO::PARAM_STR );
        
                    $stmt->execute();
                    
                    if ( $stmt->rowCount() < 1 ) {
                        $query_ok = FALSE;
                    }
                } else {
                    $query_ok = FALSE;
                }
            }

            return $query_ok;

        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            
        } catch( Exception $e ) {
            $this->errorMessage( $e );
        }
    }

    /*---------------------------OTHER-FUNCTIONS---------------------------*/

    function errorMessage( $e ) {

        $message = [
            "error" => [
                "error_type" => "Query Error",
                "error_code" => $e->getCode(),
                "message" => $e->getMessage()
                ]
            ];
                
        header("Content-Type: application/json charset=UTF-8");
        echo json_encode( $message );
    
    }

}

?>