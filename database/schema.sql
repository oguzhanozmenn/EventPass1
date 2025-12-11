-- Eğer varsa eski tabloları temizle (Geliştirme aşamasında kolaylık sağlar)
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

-- 1. KULLANICILAR TABLOSU
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer', -- 'admin' veya 'customer'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. ETKİNLİKLER TABLOSU
CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date TIMESTAMP NOT NULL,
    venue VARCHAR(100) NOT NULL, -- Etkinlik Yeri
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL, -- Toplam koltuk sayısı

    -- PROFESYONEL DOKUNUŞ: PostgreSQL JSONB
    -- Etkinliğin kuralları, sanatçı bilgileri gibi esnek verileri burada tutacağız.
    -- Örn: {"artist": "Tarkan", "rules": ["18+", "No Cameras"]}
    details JSONB DEFAULT '{}',

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. REZERVASYONLAR (BİLETLER) TABLOSU
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ticket_count INT DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'confirmed' -- 'confirmed', 'cancelled'
);

-- Test Verileri (Sistemi denerken boş kalmasın)
INSERT INTO users (full_name, email, password_hash, role)
VALUES
('Admin User', 'admin@eventpass.com', 'hash_password_buraya', 'admin'),
('Ahmet Yılmaz', 'ahmet@mail.com', 'hash_password_buraya', 'customer');

INSERT INTO events (title, description, event_date, venue, price, capacity, details)
VALUES
('Yazılım Geliştirici Zirvesi', 'Global teknoloji liderleri buluşuyor.', '2025-06-15 14:00:00', 'Kongre Merkezi', 250.00, 100, '{"speaker": "Linus Torvalds", "tags": ["tech", "coding"]}'),
('Klasik Müzik Gecesi', 'Beethoven Senfonileri.', '2025-07-20 20:00:00', 'Şehir Tiyatrosu', 120.50, 50, '{"orchestra": "Royal Phil", "dress_code": "formal"}');