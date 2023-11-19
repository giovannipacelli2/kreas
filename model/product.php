<?php

namespace App\model;

use App\core\Message;
use Exception;
use PDOException;
use PDO;

class Product{

    public $product_code, $name, $saved_kg_co2;

    private $conn;
    private $table_name = "products";

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

            exceptionHandler( $e );

            $this->errorMessage( $e );

        }
            
        }
        
/*--------------------------------------------CRUD-METHODS--------------------------------------------*/
        
    /*--------------------------------READ---------------------------------*/
    
    public function read() {
        
        try{
            
            $q = "SELECT * FROM " . $this->table_name . ";";
            $stmt = $this->conn->prepare( $q );
            
            $stmt->execute();
            
            return $stmt;
            
        } catch( PDOException $e ) {

            exceptionHandler( $e );

            $this->errorMessage( $e );

        }
        
    }
    
    public function read_by_code( $product_code ) {
        
        $product_code = htmlspecialchars( strip_tags( $product_code ) );
        
        try{
            $q = "SELECT * FROM " . $this->table_name . 
            " WHERE product_code=:product_code";
            
            $stmt = $this->conn->prepare( $q );
            $stmt->bindParam( ':product_code', $product_code, PDO::PARAM_STR );
            
            $stmt->execute();
            
            return $stmt;
            
        } catch( PDOException $e ) {

            exceptionHandler( $e );

            $this->errorMessage( $e );

        }
            
        }
        
    /*-------------------------------INSERT--------------------------------*/

    public function insert() {

        $this->product_code = htmlspecialchars( strip_tags( $this->product_code ) );
        $this->name = htmlspecialchars( strip_tags( $this->name ) );
        $this->saved_kg_co2 = htmlspecialchars( strip_tags( $this->saved_kg_co2 ) );

        try{

            $q = "INSERT INTO " . $this->table_name .
                  " ( product_code, name, saved_kg_co2 ) VALUES(
                    :product_code, :name, :saved_kg_co2
                  )"  ;

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":product_code", $this->product_code, PDO::PARAM_STR );
            $stmt->bindParam( ":name", $this->name, PDO::PARAM_STR );
            $stmt->bindParam( ":saved_kg_co2", $this->saved_kg_co2, PDO::PARAM_INT );

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            $this->errorMessage( $e );

        }

    }

    /*-------------------------------UPDATE--------------------------------*/

    public function update( string $code ) {

        
        // Check values we want to change
        
        if ( empty($this->product_code)
                || empty($this->name)
                || empty($this->saved_kg_co2)
            ) {
        
            $old_data = $this->read_by_code( $code );
            
            if ( $old_data->rowCount()>0 ){

                $old_data = $old_data->fetch( PDO::FETCH_ASSOC );
                
                if ( !$this->product_code ) {
                    $this->product_code = $old_data["product_code"];
                }
                if ( !$this->name ) {
                    $this->name = $old_data["name"];
                }
                if ( !$this->saved_kg_co2 ) {
                    $this->saved_kg_co2 = $old_data["saved_kg_co2"];
                }  

            }
            
        }
    
        $code = htmlspecialchars( strip_tags( $code ) );

        $this->product_code = htmlspecialchars( strip_tags( $this->product_code ) );
        $this->name = htmlspecialchars( strip_tags( $this->name ) );
        $this->saved_kg_co2 = htmlspecialchars( strip_tags( $this->saved_kg_co2 ) );

        // code by uri

        try{

            $q = "UPDATE " . $this->table_name .
                " SET product_code = :product_code, 
                        name = :name, saved_kg_co2 = :saved_kg_co2
                    WHERE product_code = :code";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":product_code", $this->product_code, PDO::PARAM_STR );
            $stmt->bindParam( ":name", $this->name, PDO::PARAM_STR );
            $stmt->bindParam( ":saved_kg_co2", $this->saved_kg_co2, PDO::PARAM_INT );
            $stmt->bindParam( ":code", $code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            $this->errorMessage( $e );

        }

    }

    /*-------------------------------DELETE--------------------------------*/
    
    public function delete( string $code ) {
        
        // code by uri
        $code = htmlspecialchars( strip_tags( $code ) );
        
        try{
            
            $q = "DELETE FROM " . $this->table_name .
            " WHERE product_code = :code;";
            
            $stmt = $this->conn->prepare( $q );
            
            $stmt->bindParam( ":code", $code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;
            
        } catch( PDOException $e ) {

            exceptionHandler( $e );

            $this->errorMessage( $e );

        }
            
    }
        
    /*---------------------------OTHER-FUNCTIONS---------------------------*/

    function errorMessage( $e ) {

        $user_message = [
            "error" => [
                "error_type" => "Query Error",
                "error_code" => $e->getCode()
                ]
            ];
                
        header("Content-Type: application/json charset=UTF-8");
        echo json_encode( $user_message );
    
    }
        
        
}
    
?>