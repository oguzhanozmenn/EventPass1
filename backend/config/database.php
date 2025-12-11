<?php
class Database {
    // Docker ortamındaysa 'DB_HOST' ortam değişkenini kullan, yoksa 'localhost' kullan
    private $host;
    private $db_name = 'eventpass_db';
    private $username = 'postgres';
    private $password = '123'; // <-- Kendi PostgreSQL şifreni buraya yazmayı unutma!
    private $conn;

    public function __construct() {
        // getenv: Ortam değişkenini okur (Docker bu değişkeni verecek)
        $this->host = getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost';
    }

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "pgsql:host=" . $this->host . ";port=5432;dbname=" . $this->db_name;

            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $e) {
            echo "Bağlantı Hatası: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>