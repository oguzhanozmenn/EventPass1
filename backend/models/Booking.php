<?php
class Booking {
    private $conn;
    private $table = 'bookings';

    public $event_id;
    public $user_id;
    public $ticket_count;
    public $total_price;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Rezervasyon Oluşturma Fonksiyonu
    public function create() {
        // 1. Sorguyu Hazırla
        $query = 'INSERT INTO ' . $this->table . ' 
                  (event_id, user_id, ticket_count, total_price, status) 
                  VALUES 
                  (:event_id, :user_id, :ticket_count, :total_price, :status)';

        $stmt = $this->conn->prepare($query);

        // 2. Verileri Temizle (Güvenlik)
        $this->event_id = htmlspecialchars(strip_tags($this->event_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id)); // Şu anlık manuel göndereceğiz

        // Varsayılan değerler
        $status = 'confirmed';

        // 3. Parametreleri Bağla
        $stmt->bindParam(':event_id', $this->event_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':ticket_count', $this->ticket_count);
        $stmt->bindParam(':total_price', $this->total_price);
        $stmt->bindParam(':status', $status);

        // 4. Çalıştır
        if($stmt->execute()) {
            return true;
        }

        // Hata olursa logla
        printf("Hata: %s.\n", $stmt->error);
        return false;
    }
}
?>