<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbPath = realpath(__DIR__ . '/../config/Database.php');
$modelPath = realpath(__DIR__ . '/../models/Event.php');

if (file_exists($dbPath) && file_exists($modelPath)) {
    include_once $dbPath;
    include_once $modelPath;
}

class EventController {

    // --- 1. ETKİNLİKLERİ LİSTELE ---
    public function getEvents() {
        $database = new Database();
        $db = $database->connect();
        $event = new Event($db);
        $result = $event->read();

        $events_arr = array();
        $events_arr['status'] = 'success';
        $events_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $imageUrlFull = $image_url ? "http://localhost:8000/uploads/" . $image_url : null;

            $event_item = array(
                'id' => $id,
                'title' => $title,
                'description' => html_entity_decode($description),
                'date' => $event_date,
                'venue' => $venue,
                'price' => $price,
                'image_url' => $imageUrlFull,
                'details' => json_decode($details),
                'active' => $is_active,
                'category' => $category // <-- EKLENDİ
            );
            array_push($events_arr['data'], $event_item);
        }

        header('Content-Type: application/json');
        echo json_encode($events_arr);
    }

    // --- 2. YENİ ETKİNLİK OLUŞTUR ---
    public function createEvent() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Yetkisiz işlem!"]);
            exit();
        }

        $database = new Database();
        $db = $database->connect();

        // Dosya Yükleme
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);

            if (in_array(strtolower($filetype), $allowed)) {
                $newName = uniqid('event_', true) . "." . $filetype;
                $target = __DIR__ . '/../uploads/' . $newName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imageName = $newName;
                } else {
                    http_response_code(500); echo json_encode(["message" => "Dosya hatası."]); exit();
                }
            } else {
                http_response_code(400); echo json_encode(["message" => "Geçersiz dosya formatı."]); exit();
            }
        }

        // KATEGORİ ALANI EKLENDİ
        $query = "INSERT INTO events (title, description, event_date, venue, price, capacity, details, image_url, category) 
                  VALUES (:title, :desc, :date, :venue, :price, :cap, :details, :img, :cat)";

        try {
            $stmt = $db->prepare($query);

            $detailsJSON = json_encode(["organizer" => $_POST['organizer'] ?? "EventPass"]);

            // Varsayılan kategori 'other'
            $category = $_POST['category'] ?? 'other';

            $stmt->bindParam(':title', $_POST['title']);
            $stmt->bindParam(':desc', $_POST['description']);
            $stmt->bindParam(':date', $_POST['date']);
            $stmt->bindParam(':venue', $_POST['venue']);
            $stmt->bindParam(':price', $_POST['price']);
            $stmt->bindParam(':cap', $_POST['capacity']);
            $stmt->bindParam(':details', $detailsJSON);
            $stmt->bindParam(':img', $imageName);
            $stmt->bindParam(':cat', $category); // <-- BAĞLANDI

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "Etkinlik başarıyla oluşturuldu!"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Veritabanı hatası: " . $e->getMessage()]);
        }
    }
}
?>