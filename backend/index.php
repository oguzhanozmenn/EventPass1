<?php
// 1. OTURUM BAŞLATMA (En üstte olmalı)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hataları Görüntüle (Geliştirme modu)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. CORS AYARLARI (Frontend ile Backend arası izinler)
// Frontend'in (localhost:63342 vb.) çerez göndermesine izin veriyoruz.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true"); // Session Cookie izni
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tarayıcı ön kontrol (Pre-flight) isteği atarsa durdur ve OK de.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// URL'i Parçala
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// --- 3. STATİK DOSYA SUNUMU (RESİMLER) ---
// Eğer istek '/uploads/' ile başlıyorsa, PHP çalıştırma, direkt resmi ver.
if (strpos($request_uri, '/uploads/') === 0) {
    $file = __DIR__ . $request_uri;
    if (file_exists($file)) {
        $mime = mime_content_type($file);
        header("Content-Type: $mime");
        readfile($file);
        exit();
    }
}

// Geriye kalan tüm yanıtlar JSON formatında olacak
header("Content-Type: application/json; charset=UTF-8");

// --- 4. ROUTER (YÖNLENDİRİCİ) ---
switch ($request_uri) {

    // Ana Sayfa Testi
    case '/':
        echo json_encode(["message" => "EventPass API v2.0 Çalışıyor! 🚀"]);
        break;

    // --- ETKİNLİKLER ---
    case '/events':
        $path = __DIR__ . '/controllers/EventController.php';
        if (file_exists($path)) {
            include_once $path;
            $controller = new EventController();

            if ($request_method == 'GET') {
                $controller->getEvents(); // Listele
            }
            elseif ($request_method == 'POST') {
                $controller->createEvent(); // Yeni Ekle (Admin)
            }
            else {
                http_response_code(405); echo json_encode(["message" => "Method desteklenmiyor"]);
            }
        }
        break;

    // --- BİLET ALMA (BOOKING) ---
    case '/book':
        $path = __DIR__ . '/controllers/BookingController.php';
        if (file_exists($path)) {
            include_once $path;
            $bookingController = new BookingController();
            if ($request_method == 'POST') {
                $bookingController->createBooking();
            } else {
                http_response_code(405); echo json_encode(["message" => "Sadece POST."]);
            }
        }
        break;

    // --- BİLETLERİMİ GÖR (PROFİL) --- <-- YENİ EKLENDİ
    case '/my-tickets':
        $path = __DIR__ . '/controllers/BookingController.php';
        if (file_exists($path)) {
            include_once $path;
            $bookingController = new BookingController();
            if ($request_method == 'GET') {
                $bookingController->getMyBookings();
            } else {
                http_response_code(405); echo json_encode(["message" => "Sadece GET."]);
            }
        }
        break;

    // --- AUTH ROTALARI ---
    case '/login':
        $path = __DIR__ . '/controllers/AuthController.php';
        if (file_exists($path)) include_once $path; (new AuthController())->login();
        break;

    case '/register':
        $path = __DIR__ . '/controllers/AuthController.php';
        if (file_exists($path)) include_once $path; (new AuthController())->register();
        break;

    case '/logout':
        $path = __DIR__ . '/controllers/AuthController.php';
        if (file_exists($path)) include_once $path; (new AuthController())->logout();
        break;

    case '/check-auth':
        $path = __DIR__ . '/controllers/AuthController.php';
        if (file_exists($path)) include_once $path; (new AuthController())->checkUser();
        break;

    // --- BULUNAMADI ---
    default:
        http_response_code(404);
        echo json_encode(["message" => "404 - Sayfa Bulunamadı"]);
        break;
}
?>