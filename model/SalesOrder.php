<?php

namespace App\model;

use App\core\Message;
use DateTime;
use PDO;
use PDOException;

class SalesOrder{

    public $sales_code, $sales_date, $destination, $products, $product_id, $n_products;

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

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
            
    }

    /*-------------------------CHECK-BY-SALES-CODE-------------------------*/
        
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
    
/*--------------------------------------------CRUD-METHODS--------------------------------------------*/
    
    /*--------------------------------READ---------------------------------*/

    public function read() {
        
        try{

            $q = "SELECT p.name, p.product_code, so.sales_code, so.sales_date, so.destination, p.saved_kg_co2, so.n_products
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

    public function read_by_code( $sales_code ) {

        $sales_code = htmlspecialchars( strip_tags( $sales_code ) );
        
        try{
            $q = "SELECT p.name, p.product_code, so.sales_code, so.sales_date, so.destination, p.saved_kg_co2, so.n_products
                    FROM ". $this->table_join .
                    " ON p.product_code = so.product_id
                    WHERE so.sales_code = :sales_code
                    ORDER BY so.sales_code;";

            $stmt = $this->conn->prepare( $q );
            $stmt->bindParam( ':sales_code', $sales_code, PDO::PARAM_STR );
            
            $stmt->execute();

            return $stmt;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }

    }
    /*-------------------------------INSERT--------------------------------*/

    function insert(){

        $affected_rows = 0;

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );

        try{

            /*----------------Check-if-row-already-exists----------------*/
                        
            $check = $this->checkSale( $this->sales_code );

            /*----------------It-does-the-insert-normally----------------*/

            if ( $check->rowCount() == 0 ) {

                foreach( $this->products as $prod ) {

                    $prod = (array) $prod;

                    $this->product_id = $prod["product_code"];
                    $this->n_products = (int) $prod["n_prod"];

                    $stmt = $this->simple_insert();


                    // Returns affected rows
                    if ( isset($stmt) && $stmt->rowCount() > 0 ){
                        $affected_rows += $stmt->rowCount();
                    }
                }
            }

            /*--------------When-sales-code-already-exists---------------*/
            elseif ( $check->rowCount() != 0 ) {

                $message["result"] = [
                    "Error"=> "Integrity violation",
                    "Message" => "Order already exists"
                ];

                $message = json_encode( $message );
                header("Content-Type: application/json charset=UTF-8");
                echo $message;
                
                $message = null;
                exit();               

            }
    
            

            return $affected_rows;

        } catch( PDOException $e ) {

            exceptionHandler( $e );

            Message::errorMessage( $e );

        }
    }

    function simple_insert( ){

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

            Message::errorMessage( $e );
            exit();

        }
    }
 

    /*-------------------------------UPDATE--------------------------------*/

    function update( string $code ){

        // code by uri
        $old_sales_code = htmlspecialchars( strip_tags( $code ) );

        /*----------------Check-if-row-already-exists----------------*/
                        
        $check = $this->checkSale( $old_sales_code );

        if ( $check->rowCount() == 0 ) {

            Message::writeJsonMessage("The searched 'SALES CODE' not exists!");
            exit();

        }

        /*----------------It-does-the-update-normally----------------*/


        // This ensures that when you make the request, 
        // you can change one or more values leaving the old values unchanged.
        
        if ( empty($this->sales_code)
                || empty($this->sales_date)
                || empty($this->destination)
                || empty($this->products)
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
            if ( !$this->products ) {

                $res = [];

                foreach ( $old_data as $old ) {
                        
                    $row = [
                        "product_code" => $old["product_code"],
                        "n_prod" => $old["n_products"],
                        "prod_name" => $old["name"],
                    ];

                    array_push( $res, $row );
                }
                $this->products = $res;
            }  
        }

        $result = [];

        $this->sales_code = htmlspecialchars( strip_tags( $this->sales_code ) );
        $this->sales_date = htmlspecialchars( strip_tags( $this->sales_date ) );
        $this->destination = htmlspecialchars( strip_tags( $this->destination ) );

        try{

            $old_data = $check->fetchAll(PDO::FETCH_ASSOC);
            
            // existing products
            $already_exists = array_map( function($row){

                return $row["product_id"];

            }, $old_data );

            
            // Products to insert not yet present in sales orders' table
            
            $to_update = [];
            $to_insert = [];

            foreach ( $this->products as $p ) {

                $p = (array) $p;


                $exists = in_array( $p["product_code"], $already_exists );

                if ($exists) {
                    array_push( $to_update, $p );
                } else {
                    array_push( $to_insert, $p );
                }

            }
            

            /*---------------------INSERT-NEW-VALUES----------------------*/
            
            if ( $to_insert ){

                $count = 0;

                foreach( $to_insert as $p ) {

                    $this->product_id = htmlspecialchars( strip_tags( $p["product_code"] ) );
                    $this->n_products = htmlspecialchars( strip_tags( $p["n_prod"] ) );

                    $stmt = $this->simple_insert();

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

                $products = array_column( (array) $this->products, "product_code" );

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
                        product_id=:product_id,
                        n_products=:n_products
                    WHERE sales_code=:old_code
                    AND product_id=:product_id;";

                    /*--extrapolate which products are linked to the sales order--*/
                        
                    $stmt = $this->conn->prepare($q);

                    $stmt->bindParam( ":sales_code", $this->sales_code, PDO::PARAM_STR );
                    $stmt->bindParam( ":sales_date", $this->sales_date, PDO::PARAM_STR );
                    $stmt->bindParam( ":destination", $this->destination, PDO::PARAM_STR );

                    $stmt->bindParam( ":product_id", $p["product_code"], PDO::PARAM_STR );
                    $stmt->bindParam( ":n_products", $p["n_prod"], PDO::PARAM_INT );
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

            exceptionHandler( $e );

            Message::errorMessage( $e );

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