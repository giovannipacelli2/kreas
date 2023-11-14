<?php

    namespace App\core;

    use PDO;
    use PDOException;

    class Connection {

        private $dsn, $username, $password, $options;
        private $conn;

        public function __construct( $config )
        {
            $this->dsn = $config["connection"] . ";dbname=" . $config["name"];
            $this->username = $config["username"];
            $this->password = $config["password"];
            $this->options = $config["options"];

            try {

                $this->conn =  new PDO(
                    $this->dsn,
                    $this->username,
                    $this->password,
                    $this->options
                );
                $this->conn->exec( "set names utf8" );

            } catch( PDOException $e ) {
                echo "Connection error: " . $e->getMessage();
            }
            
            
        }

        public function getConnection() {
            return $this->conn;
        }

    }

?>