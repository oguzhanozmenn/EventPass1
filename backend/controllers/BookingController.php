<?php
// Oturum Kontrolü için session başlat (Eğer başlatılmadıysa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dosya yollarını garantiye alarak dahil et
$dbPath = realpath(__DIR__ . '/../config/Database.php');
$modelPath = realpath(__DIR__ . '/../models/Booking.php');

if (file_exists($dbPath) && file_exists($modelPath)) {
    include_once $dbPath;
    include_once $modelPath;
} else {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["message" => "Server Hatası: Gerekli dosyalar bulunamadı (BookingController)."]);
    exit();
}

class BookingController {

    // --- 1. YENİ BİLET SATIN ALMA (POST) ---
    public function createBooking() {
        // A) Giriş Kontrolü
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "Lütfen bilet almak için önce giriş yapın."]);
            exit();
        }

        $database = new Database();
        $db = $database->connect();
        $booking = new Booking($db);

        // B) JSON Verisini Al
        $data = json_decode(file_get_contents("php://input"));

        // C) Veri Kontrolü
        if( !empty($data->event_id) && !empty($data->price) ) {

            $booking->event_id = $data->event_id;
            $booking->total_price = $data->price;

            // Session'dan gelen gerçek kullanıcı ID'sini al
            $booking->user_id = $_SESSION['user_id'];
            $booking->ticket_count = 1;

            // D) Veritabanına Yaz (Trigger burada kapasiteyi kontrol eder)
            if($booking->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Bilet başarıyla alındı!"]);
            } else {
                http_response_code(503);
                // Trigger hata fırlatırsa burada yakalanabilir ama genelde Booking model false döner
                echo json_encode(["message" => "İşlem başarısız. Kapasite dolmuş olabilir."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Eksik veri gönderildi."]);
        }
    }

    // --- 2. KULLANICININ BİLETLERİNİ GETİR (GET) ---
    public function getMyBookings() {
        // A) Giriş Kontrolü
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Biletlerinizi görmek için giriş yapmalısınız."]);
            exit();
        }

        $database = new Database();
        $db = $database->connect();

        // B) SQL JOIN Sorgusu
        // Bookings tablosu ile Events tablosunu birleştiriyoruz.
        // Böylece biletin yanında etkinliğin adını, resmini ve yerini de çekiyoruz.
        $query = "SELECT 
                    b.id as booking_id,
                    b.booking_date,
                    b.ticket_count,
                    b.total_price,
                    b.status,
                    e.title,
                    e.event_date,
                    e.venue,
                    e.image_url
                  FROM bookings b
                  JOIN events e ON b.event_id = e.id
                  WHERE b.user_id = :uid
                  ORDER BY b.booking_date DESC";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':uid', $_SESSION['user_id']);
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // C) Resim URL'lerini Tam Hale Getir
            foreach ($bookings as &$booking) {
                if ($booking['image_url']) {
                    // Backend portunun 8000 olduğunu varsayıyoruz
                    $booking['image_url'] = "http://localhost:8000/uploads/" . $booking['image_url'];
                }
            }

            // D) JSON Yanıtı Döndür
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $bookings]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Veritabanı hatası: " . $e->getMessage()]);
        }
    }
}
?>