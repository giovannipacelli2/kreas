<?php

namespace App\model;

use App\core\Message;
use PDO;
use PDOException;

class Sales{

    public $sales_code, $sales_date, $destination;

    protected $conn;
    protected $table_name = "sales" ;

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

    function read(){
        
        try{

            $q = "SELECT * FROM " . $this->table_name .";";

            $stmt = $this->conn->prepare( $q );

            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
    }     

    /*--------------------------READ-BY-SALES-CODE-------------------------*/
        
    public function readByOrder( $sales_code ) {

        $sales_code = htmlspecialchars( strip_tags( $sales_code ) );

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
        
        try{

            $q = "INSERT INTO " . $this->table_name . " " .
                    "( sales_code, sales_date, destination ) VALUES(
                        :sales_code, :sales_date, :destination
                    )";
    
            /*----------------------Query-Insert-------------------------*/
            
  
            $stmt = $this->conn->prepare($q);
        
            $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
            $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
            $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );
        
            $stmt->execute();
                    

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            if ( $e->getCode() == 23000 ) {

                $specific_message = "This order already exists!";

                Message::errorMessage( $e, $specific_message );
                exit();
            }


            Message::errorMessage( $e );
            exit();

        }
    }
 

    /*-------------------------------UPDATE--------------------------------*/

    function update( $sales_to_update ){

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );

        $sales_to_update = htmlspecialchars( strip_tags( $sales_to_update ) );
        
        try{

            $q = "UPDATE " . $this->table_name . " " .
                    "SET sales_code=:sales_code,
                        sales_date=:sales_date,
                        destination=:destination
                    WHERE sales_code=:old_code;";
    
            /*----------------------Query-Update-------------------------*/
            
  
            $stmt = $this->conn->prepare($q);

            $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
            $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
            $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );

            $stmt->bindParam( ":old_code", $sales_to_update, PDO::PARAM_STR );
                    
            $stmt->execute();
                    

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );
            exit();

        }
    }

    
    /*-------------------------------DELETE--------------------------------*/

    function delete( string $code ){
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

        $check = $this->readByOrder( $code );

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