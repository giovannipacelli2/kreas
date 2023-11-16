<?php

namespace App\model;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

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

            $q = "SELECT p.name, p.product_code, so.sales_code, so.sales_date, so.destination, p.saved_kg_co2,
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

    public function read_by_code( $sales_code ) {

        $sales_code = htmlspecialchars( strip_tags( $sales_code ) );
        
        try{
            $q = "SELECT p.name, p.product_code, so.sales_code, so.sales_date, so.destination, p.saved_kg_co2,
                    SUM(p.saved_kg_co2) OVER ( PARTITION BY so.sales_code ) AS `total_saved_co2`,
                    COUNT(so.sales_code) OVER ( PARTITION BY so.sales_code ) AS `articles_num`
                    FROM ". $this->table_join .
                    " ON p.product_code = so.product_id
                    WHERE so.sales_code = :sales_code
                    ORDER BY so.sales_code;";

            $stmt = $this->conn->prepare( $q );
            $stmt->bindParam( ':sales_code', $sales_code, PDO::PARAM_STR );
            
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

        $affected_rows = 0;

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );
        $this->product_id = htmlspecialchars( strip_tags( $this->product_id ) );

        $products = $this->strToArray( $this->product_id );

        try{

            $q_check = "SELECT * FROM " . $this->table_name . " " .
                        "WHERE 
                            sales_code=:sales_code AND
                            sales_date=:sales_date AND
                            destination=:destination AND
                            product_id=:product_id;";

    
            for ( $i = 0; $i < count($products); $i++ ){

                /*----------------Check-if-row-already-exists----------------*/
                
                $check = $this->conn->prepare($q_check);
                
                $check->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
                $check->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
                $check->bindParam( ":destination", $this->destination, PDO::PARAM_STR );
                $check->bindParam( ":product_id",  $products[$i], PDO::PARAM_STR );
                
                $check->execute();
                
                /*----------------It-does-the-insert-normally----------------*/

                if ( $check->rowCount() < 1 ) {
                    
                    $stmt = $this->simple_insert( $products[$i] );

                    if ( isset($stmt) && $stmt->rowCount() > 0 ){
                        $affected_rows += $stmt->rowCount();
                    }
  
                }
            }

            return $affected_rows;

        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            
        } catch( Exception $e ) {
            $this->errorMessage( $e );
        }
    }

    function simple_insert( $single_product ){

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );

        try{

            $q = "INSERT INTO " . $this->table_name . " " .
                    "( sales_code, sales_date, destination, product_id ) VALUES(
                        :sales_code, :sales_date, :destination, :product_id
                    )";
    
            /*----------------------Query-Insert-------------------------*/
            
  
            $stmt = $this->conn->prepare($q);
        
            $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
            $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
            $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );
            $stmt->bindParam( ":product_id", $single_product, PDO::PARAM_STR );
        
            $stmt->execute();
                    

            return $stmt;

        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            exit();
            
        } catch( Exception $e ) {
            $this->errorMessage( $e );
            exit();
        }
    }
 

    /*-------------------------------UPDATE--------------------------------*/

    function update( string $code ){

        // Check values we want to change
        
        if ( empty($this->sales_code)
                || empty($this->sales_code)
                || empty($this->destination)
                || empty($this->product_id)
            ) {
        
            $old_data = $this->read_by_code( $code )->fetchAll( PDO::FETCH_ASSOC );
            
            if ( !$this->sales_code ) {
                $this->sales_code = $old_data[0]["sales_code"];
            }
            if ( !$this->sales_date ) {
                $this->sales_date = $old_data[0]["sales_date"];
            }
            if ( !$this->destination ) {
                $this->destination = $old_data[0]["destination"];
            }  
            if ( !$this->product_id ) {

                $res = "";

                foreach ( $old_data as $old ) {
                    if ( $res === "" ){
                        $res = $old["product_code"];
                    } else {
                        $res = $res . ", " . $old["product_code"];
                    }
                }
                $this->product_id = $res;
            }  
        }

        $result = [];

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );
        // All product codes as string -> "0100, 1234, 4040"
        $this->product_id = htmlspecialchars( strip_tags( $this->product_id ) );

        // code by uri
        $old_sales_code = htmlspecialchars( strip_tags( $code ) );

        // Extraxt values in array -> [ 0100, 1234, 4040 ]
        $products = $this->strToArray( $this->product_id );

        try{

            $q_check = "SELECT * FROM " . $this->table_name . " " .
                        "WHERE sales_code=:code;";

            /*--extrapolate which products are linked to the sales order--*/
                
            $check = $this->conn->prepare($q_check);

            $check->bindParam( ":code", $old_sales_code, PDO::PARAM_STR );
            
            $check->execute();

            $old_data = $check->fetchAll(PDO::FETCH_ASSOC);
            
            // existing products
            $already_exists = array_map( function($row){
                return $row["product_id"];
            }, $old_data );

            
            // Products to insert not yet present in sales orders' table
            
            $to_update = [];
            $to_insert = [];
            
            for( $i = 0; $i < count($products); $i++ ) {
                $exists= in_array( $products[$i], $already_exists );

                if ($exists) {
                    array_push( $to_update, $products[$i] );
                } else {
                    array_push( $to_insert, $products[$i] );
                }
            }
            

            /*---------------------INSERT-NEW-VALUES----------------------*/
            
            if ( $to_insert ){

                $count = 0;

                foreach( $to_insert as $p ) {

                    $stmt = $this->simple_insert( $p );

                    if ( isset($stmt) && $stmt->rowCount() > 0 ){
                        $count += $stmt->rowCount();
                    }
                }
                
                if ($count != 0){
                    $result["insert"] = $this->operationMessage( $count );
                }
            }

            /*---------------------DELETE-OLD-VALUES----------------------*/
            
            
            if ( $already_exists || $this->sales_code != $old_sales_code ){


                $del_id = "'" . implode( "','", $products ) . "'";
    
                $q_delete = "DELETE FROM " . $this->table_name .
                " WHERE sales_code = :sales_code 
                AND product_id NOT IN ( " . $del_id . " );";
                
                $stmt = $this->conn->prepare( $q_delete );
                
                $stmt->bindParam( ":sales_code", $old_sales_code, PDO::PARAM_STR );
                
                $stmt->execute();

                if ( $stmt->rowCount() != 0 ) {
                    $result["delete"] = $this->operationMessage( $stmt->rowCount() );
                }

            }

            /*---------------------UPDATE-OLD-VALUES----------------------*/
            
            if ( $to_update ){

                $count = 0;

                foreach( $to_update as $p ) {
                    
                    $q = "UPDATE " . $this->table_name . " " .
                    "SET sales_code=:sales_code,
                        sales_date=:sales_date,
                        destination=:destination,
                        product_id=:product_id
                    WHERE sales_code=:old_code
                    AND product_id=:product_id;";

                    /*--extrapolate which products are linked to the sales order--*/
                        
                    $stmt = $this->conn->prepare($q);

                    $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
                    $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
                    $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );

                    $stmt->bindParam( ":product_id", $p, PDO::PARAM_STR );
                    $stmt->bindParam( ":old_code", $old_sales_code, PDO::PARAM_STR );
                    
                    $stmt->execute();

                    if ( $stmt ) {
                        $count += $stmt->rowCount();
                    }

                }
                
                if ( $count != 0 ) {
                    $result["update"] = $this->operationMessage( $count );
                }
                
            }

            /*-----------------------RETURN-RESULT------------------------*/

            if ( $result ){
                return $result;
            } else {
                return FALSE;
            }


        } catch( PDOException $e ) {

            $this->errorMessage( $e );
            
        } catch( Exception $e ) {
            $this->errorMessage( $e );
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

            $this->errorMessage( $e );

        } catch( Exception $e ) {
            $e->getMessage();
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

    function strToArray( string $string ) : array {

        $arr = explode( ",", $string );

        for ( $i = 0; $i < count($arr); $i++ ) {
            $arr[$i] = trim( $arr[$i] );
        }
        return $arr;
    }

    function operationMessage( int $n_rows ) : string {

        if ( $n_rows > 0 ) {
            $s = $n_rows == 1 ? "" : "s";

            return $n_rows . " row" . $s;

        }
        return "";
    }

}

?>