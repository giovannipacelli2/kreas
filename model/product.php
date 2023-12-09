<?php

namespace App\model;

use App\core\Message;
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

            Message::errorMessage( $e );

        }
            
    }

    
    /*-----------------------------CHECK-IDs-------------------------------*/

    // Check if all of "$arr_products" ids exists in the DB table "product"

    function checkIds( $arr_products ) {

        // products in body of request
        $arr_products = array_map( function( $prod ){

            $res = htmlspecialchars( strip_tags( $prod ) );

            $res = filter_var( $res, FILTER_SANITIZE_NUMBER_INT);

            if (!$res) {
                Message::writeJsonMessage("Error in product_code validation!");
                exit();
            }

            return $res;

        }, $arr_products );


        $ids = "'" . implode( "','", $arr_products ) . "'";

        try{
    
            $q = "SELECT * FROM " . $this->table_name .
            " WHERE product_code IN ( " . $ids . " );";
            
            $stmt = $this->conn->prepare( $q );
            
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
    }
        
/*--------------------------------------------CRUD-METHODS--------------------------------------------*/
        
    /*--------------------------------READ---------------------------------*/

    // Read all data
    
    public function read() {
        
        try{
            
            $q = "SELECT * FROM " . $this->table_name . ";";
            $stmt = $this->conn->prepare( $q );
            
            $stmt->execute();
            
            return $stmt;
            
        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
        
    }

    // Reads data of a specific product by his product code
    
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

            Message::errorMessage( $e );

        }
            
        }
        
    /*-------------------------------INSERT--------------------------------*/

    public function insert() {

        $this->product_code = htmlspecialchars( strip_tags( $this->product_code ) );
        $this->name = htmlspecialchars( strip_tags( $this->name ) );
        $this->saved_kg_co2 = htmlspecialchars( strip_tags( (float) $this->saved_kg_co2 ) );

        if ( $this->saved_kg_co2 == 0 ) {
            Message::writeJsonMessage("Saved kg co2 format isn't valid!");
            exit();
        }

        try{

            $q = "INSERT INTO " . $this->table_name .
                  " ( product_code, name, saved_kg_co2 ) VALUES(
                    :product_code, :name, :saved_kg_co2
                  )"  ;

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":product_code", $this->product_code, PDO::PARAM_STR );
            $stmt->bindParam( ":name", $this->name, PDO::PARAM_STR );
            $stmt->bindParam( ":saved_kg_co2", $this->saved_kg_co2, PDO::PARAM_STR );

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            if ( $e->getCode() == "23000" ) {
                Message::errorMessage( $e, "Inserted key already exists!!" );
            } else {
                Message::errorMessage( $e );
            }


        }

    }

    /*-------------------------------UPDATE--------------------------------*/

    public function update( string $code ) {

        
        // This ensures that when you make the request, 
        // you can change one or more values leaving the old values unchanged.
        
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

        // code by uri
    
        $code = htmlspecialchars( strip_tags( $code ) );

        $this->product_code = htmlspecialchars( strip_tags( $this->product_code ) );
        $this->name = htmlspecialchars( strip_tags( $this->name ) );
        $this->saved_kg_co2 = htmlspecialchars( strip_tags( (float) $this->saved_kg_co2 ) );

        if ( $this->saved_kg_co2 == 0 ) {
            Message::writeJsonMessage("Saved kg co2 format isn't valid!");
            exit();
        }

        try{

            $q = "UPDATE " . $this->table_name .
                " SET product_code = :product_code, 
                        name = :name, saved_kg_co2 = :saved_kg_co2
                    WHERE product_code = :code";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":product_code", $this->product_code, PDO::PARAM_STR );
            $stmt->bindParam( ":name", $this->name, PDO::PARAM_STR );
            $stmt->bindParam( ":saved_kg_co2", $this->saved_kg_co2, PDO::PARAM_STR );
            $stmt->bindParam( ":code", $code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

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

            Message::errorMessage( $e );

        }
            
    }
        
    /*---------------------------OTHER-FUNCTIONS---------------------------*/

        
        
}
    
?>