<?php
class Database {
    // Veritabanı Bilgileri
    private $host = 'localhost';
    private $db_name = 'eventpass_db';
    private $username = 'postgres'; // pgAdmin varsayılan kullanıcısı genelde budur
    private $password = '123'; // <-- DİKKAT: Şifreni buraya yaz
    private $conn;

    // Bağlantı Fonksiyonu
    public function connect() {
        $this->conn = null;

        try {
            // PostgreSQL bağlantı cümlesi (DSN)
            $dsn = "pgsql:host=" . $this->host . ";port=5432;dbname=" . $this->db_name;

            // Bağlantıyı başlat
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Hata modunu aç (Hata olursa gizlemesin, söylesin)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $e) {
            echo "Bağlantı Hatası: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>