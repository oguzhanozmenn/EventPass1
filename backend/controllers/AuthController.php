<?php
// Dosya yollarını garantiye al
$dbPath = realpath(__DIR__ . '/../config/Database.php');
if (file_exists($dbPath)) {
    include_once $dbPath;
}

class AuthController {

    // Oturum başlatma (Her sayfanın başında gerekli)
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // --- 1. KULLANICI KAYDI (REGISTER) ---
    public function register() {
        $database = new Database();
        $db = $database->connect();

        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->full_name) && !empty($data->email) && !empty($data->password)) {

            // Şifreyi Hashle (Güvenlik)
            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            $role = 'customer'; // Varsayılan rol

            try {
                $query = "INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :pass, :role)";
                $stmt = $db->prepare($query);

                $stmt->bindParam(':name', $data->full_name);
                $stmt->bindParam(':email', $data->email);
                $stmt->bindParam(':pass', $password_hash);
                $stmt->bindParam(':role', $role);

                if($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(["message" => "Kayıt başarılı! Şimdi giriş yapabilirsiniz."]);
                }
            } catch(PDOException $e) {
                http_response_code(500);
                echo json_encode(["message" => "Kayıt başarısız. Bu e-posta kullanılıyor olabilir."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Eksik bilgi."]);
        }
    }

    // --- 2. GİRİŞ YAPMA (LOGIN) ---
    public function login() {
        $database = new Database();
        $db = $database->connect();

        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->email) && !empty($data->password)) {

            // Kullanıcıyı bul
            $query = "SELECT id, full_name, password_hash, role FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Şifre Doğrulama
                if(password_verify($data->password, $user['password_hash'])) {

                    // OTURUM BAŞLAT
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];

                    http_response_code(200);
                    echo json_encode([
                        "message" => "Giriş başarılı!",
                        "user" => [
                            "name" => $user['full_name'],
                            "role" => $user['role']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["message" => "Hatalı şifre!"]);
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Kullanıcı bulunamadı."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "E-posta ve şifre gerekli."]);
        }
    }

    // --- 3. ÇIKIŞ YAPMA (LOGOUT) ---
    public function logout() {
        session_unset();
        session_destroy();
        http_response_code(200);
        echo json_encode(["message" => "Çıkış yapıldı."]);
    }

    // --- 4. KULLANICI KONTROL (CHECK SESSION) ---
    public function checkUser() {
        if(isset($_SESSION['user_id'])) {
            http_response_code(200);
            echo json_encode([
                "is_logged_in" => true,
                "user" => [
                    "id" => $_SESSION['user_id'],
                    "name" => $_SESSION['full_name'],
                    "role" => $_SESSION['role']
                ]
            ]);
        } else {
            http_response_code(200);
            echo json_encode(["is_logged_in" => false]);
        }
    }
}
?>