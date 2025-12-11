<?php
class Event {
    private $conn;
    private $table = 'events';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        // SQL sorgusuna 'category' alanını ekledik
        $query = 'SELECT 
                    id, 
                    title, 
                    description, 
                    event_date, 
                    venue, 
                    price, 
                    capacity, 
                    details, 
                    is_active,
                    image_url,
                    category
                  FROM ' . $this->table . '
                  ORDER BY event_date ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>