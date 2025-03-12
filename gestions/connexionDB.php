<?php
class ConnexionDB {
    private $host = 'mysql-api-sport.alwaysdata.net';
    private $db_name = 'api-sport_db';
    private $username = 'api-sport';
    private $password ='C,$M3sEtudes23';
    private $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>