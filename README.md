# 🎫 EventPass - Etkinlik Biletleme Sistemi

EventPass, kullanıcıların konser, tiyatro ve çeşitli etkinlikleri görüntüleyip bilet alabildiği, modern ve kullanıcı dostu bir web uygulamasıdır.

![Proje Görseli](screenshots/anasayfa.jpg)

## 🚀 Özellikler

* **Etkinlik Listeleme:** Güncel etkinlikleri kategoriye göre filtreleme.
* **Detaylı Görünüm:** Etkinlik saati, yeri ve fiyat bilgileri.
* **API Entegrasyonu:** Backend ile RESTful iletişim.
* **Dockerize Yapı:** Tek komutla tüm sistemi ayağa kaldırma.
* **Admin Paneli:** (Varsa buraya ekleyebilirsin)

## 🛠 Kullanılan Teknolojiler

* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP (Apache Server)
* **Veritabanı:** PostgreSQL
* **DevOps:** Docker & Docker Compose

## ⚙️ Kurulum ve Çalıştırma

Bu projeyi kendi bilgisayarınızda çalıştırmak için Docker'ın kurulu olması yeterlidir.

1.  **Repoyu klonlayın:**
    ```bash
    git clone [https://github.com/KULLANICI_ADIN/EventPass1.git](https://github.com/KULLANICI_ADIN/EventPass1.git)
    cd EventPass1
    ```

2.  **Sistemi Ayağa Kaldırın:**
    ```bash
    docker-compose up --build
    ```

3.  **Tarayıcıda Görüntüleyin:**
    * **Frontend:** http://localhost:8001
    * **Backend API:** http://localhost:8000

## 📂 Proje Yapısı

* `/frontend`: Kullanıcı arayüzü dosyaları.
* `/backend`: API ve sunucu tarafı kodları.
* `docker-compose.yml`: Konteyner orkestrasyon dosyası.

---
*Geliştirici: Oğuzhan Özmen*