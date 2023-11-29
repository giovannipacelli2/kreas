<?php

namespace App\model;

use App\core\Message;
use PDO;
use PDOException;

class Sales{

    public $sales_code, $sales_date, $destination, $product_id, $n_products;

    protected $conn;
    protected $table_name = "sales_orders" ;

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

    
/*--------------------------------------------CRUD-METHODS--------------------------------------------*/
    
    /*--------------------------------READ---------------------------------*/

    function readByProduct( string $code, string $product ){
        // code by uri
        $code = htmlspecialchars( strip_tags( $code ) );
        
        try{

            $q = "SELECT * FROM " . $this->table_name .
            " WHERE sales_code = :code " . 
            "AND product_id = :product" .
            " ORDER BY sales_code;";

            $stmt = $this->conn->prepare( $q );

            $stmt->bindParam( ":code", $code, PDO::PARAM_STR );
            $stmt->bindParam( ":product", $product, PDO::PARAM_STR );

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
    }     

    /*--------------------------READ-BY-SALES-CODE-------------------------*/
        
    public function checkSale( $sales_code ) {

        try{
            $q_check = "SELECT * FROM " . $this->table_name . " " .
                            "WHERE sales_code=:sales_code";
    
            $check = $this->conn->prepare($q_check);
                            
            $check->bindParam( ":sales_code", $sales_code, PDO::PARAM_STR );
                            
            $check->execute();

            return $check;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }


    }

    /*-------------------------------INSERT--------------------------------*/

    function insert( ){

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );
        $this->product_id = htmlspecialchars( strip_tags( $this->product_id ) );
        $this->n_products = htmlspecialchars( strip_tags( $this->n_products ) );
        
        try{

            $q = "INSERT INTO " . $this->table_name . " " .
                    "( sales_code, sales_date, destination, product_id, n_products ) VALUES(
                        :sales_code, :sales_date, :destination, :product_id, :n_products
                    )";
    
            /*----------------------Query-Insert-------------------------*/
            
  
            $stmt = $this->conn->prepare($q);
        
            $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
            $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
            $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );
            $stmt->bindParam( ":product_id", $this->product_id, PDO::PARAM_STR );
            $stmt->bindParam( ":n_products", $this->n_products, PDO::PARAM_INT );
        
            $stmt->execute();
                    

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            if ( $e->getCode() == 23000 ) {

                $specific_message = "Inserted product not exists in PRODUCTS LIST!";

                Message::errorMessage( $e, $specific_message );
                exit();
            }


            Message::errorMessage( $e );
            exit();

        }
    }
 

    /*-------------------------------UPDATE--------------------------------*/

    function updateByProduct( $sales_to_update, $product_to_update ){

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );
        $this->product_id = htmlspecialchars( strip_tags( $this->product_id ) );
        $this->n_products = htmlspecialchars( strip_tags( $this->n_products ) );

        $sales_to_update = htmlspecialchars( strip_tags( $sales_to_update ) );
        $product_to_update = htmlspecialchars( strip_tags( $product_to_update ) );
        
        try{

            $q = "UPDATE " . $this->table_name . " " .
                    "SET sales_code=:sales_code,
                        sales_date=:sales_date,
                        destination=:destination,
                        product_id=:product_id,
                        n_products=:n_products
                    WHERE sales_code=:old_code
                    AND product_id=:old_prod;";
    
            /*----------------------Query-Update-------------------------*/
            
  
            $stmt = $this->conn->prepare($q);

            $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
            $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
            $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );

            $stmt->bindParam( ":product_id", $this->product_id, PDO::PARAM_STR );
            $stmt->bindParam( ":n_products", $this->n_products, PDO::PARAM_INT );

            $stmt->bindParam( ":old_code", $sales_to_update, PDO::PARAM_STR );
            $stmt->bindParam( ":old_prod", $product_to_update, PDO::PARAM_STR );
                    
            $stmt->execute();
                    

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );
            exit();

        }
    }

    
    /*-------------------------------DELETE--------------------------------*/

    function deleteOrder( string $code ){
        // code by uri
        $code = htmlspecialchars( strip_tags( $code ) );
        
        try{
            
            $q = "DELETE FROM " . $this->table_name .
            " WHERE sales_code IN ( :code );";
            
            $stmt = $this->conn->prepare( $q );
            
            $stmt->bindParam( ":code", $code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;
            
        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
    }     

    function deleteByProduct( string $code, string $product ){

        // code by uri
        $code = htmlspecialchars( strip_tags( $code ) );
        $product = htmlspecialchars( strip_tags( $product ) );

        $check = $this->checkSale( $code );

        if ( $check->rowCount() > 1 ) {
            Message::writeJsonMessage( 
                "Unable to delete the product: \n"
                ."The sales order can't be without products" 
            );

            exit();
        }
        
        try{
            
            $q = "DELETE FROM " . $this->table_name .
            " WHERE sales_code = :code " . 
            "AND product_id = :product;";
            
            $stmt = $this->conn->prepare( $q );
            
            $stmt->bindParam( ":code", $code, PDO::PARAM_STR );
            $stmt->bindParam( ":product", $product, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;
            
        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
            
    }

}

?>