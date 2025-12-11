export class ApiService {
    // Backend Adresi (Port 8000)
    static BASE_URL = 'http://localhost:8000';

    // --- YARDIMCI: GENEL İSTEK FONKSİYONU ---
    // Bu fonksiyon tüm API çağrılarında kullanılır.
    // Otomatik olarak 'credentials: include' ekleyerek Session Cookie'lerini taşır.
    static async request(endpoint, method = 'GET', body = null) {
        const headers = {};

        // DİKKAT: Eğer gönderilen veri "FormData" ise (Resim yükleme vb.)
        // 'Content-Type' başlığını biz eklemeyiz, tarayıcı otomatik ekler.
        // Eğer normal veri ise 'application/json' ekleriz.
        if (!(body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }

        const options = {
            method: method,
            headers: headers,
            credentials: 'include' // <--- Session Cookie'leri için KRİTİK ayar
        };

        if (body) {
            // FormData ise direkt gönder, değilse JSON'a çevir
            options.body = (body instanceof FormData) ? body : JSON.stringify(body);
        }

        try {
            const response = await fetch(`${this.BASE_URL}${endpoint}`, options);

            // Backend'den gelen JSON yanıtını al
            const result = await response.json();

            // Hem HTTP durumunu (ok/status) hem de veriyi (data) döndür
            return { ok: response.ok, status: response.status, data: result };

        } catch (error) {
            console.error("API Hatası:", error);
            return { ok: false, data: { message: "Sunucu ile bağlantı kurulamadı." } };
        }
    }

    // --- 1. ETKİNLİK İŞLEMLERİ (EVENTS) ---

    // Tüm etkinlikleri getir
    static async getEvents() {
        const res = await this.request('/events');
        return res.ok ? res.data.data : [];
    }

    // Yeni Etkinlik Oluştur (Sadece Admin - Resim içerdiği için FormData alır)
    static async createEvent(formData) {
        return await this.request('/events', 'POST', formData);
    }

    // --- 2. REZERVASYON İŞLEMLERİ (BOOKINGS) ---

    // Bilet Satın Al
    static async makeBooking(eventId, price) {
        const res = await this.request('/book', 'POST', { event_id: eventId, price: price });
        // İşlem sonucunu basitçe döndür
        return { success: res.ok, message: res.data.message };
    }

    // Kullanıcının Kendi Biletlerini Getir (Profil Sayfası İçin)
    static async getMyBookings() {
        const res = await this.request('/my-tickets');
        return res.ok ? res.data.data : [];
    }

    // --- 3. KİMLİK DOĞRULAMA (AUTH) ---

    // Giriş Yap
    static async login(email, password) {
        return await this.request('/login', 'POST', { email, password });
    }

    // Kayıt Ol
    static async register(fullName, email, password) {
        return await this.request('/register', 'POST', { full_name: fullName, email, password });
    }

    // Çıkış Yap
    static async logout() {
        return await this.request('/logout', 'GET');
    }

    // Oturum Kontrolü (Sayfa yenilenince kullanıcıyı hatırla)
    static async checkAuth() {
        const res = await this.request('/check-auth');
        // Eğer sunucu "is_logged_in: true" dönerse kullanıcı bilgilerini ver
        if (res.ok && res.data.is_logged_in) {
            return res.data.user;
        }
        return null;
    }
}