import { ApiService } from './services/api.js';

document.addEventListener('DOMContentLoaded', async () => {

    // 1. GÜVENLİK KONTROLÜ (Admin mi?)
    const user = await ApiService.checkAuth();

    if (!user || user.role !== 'admin') {
        alert("Bu sayfaya erişim yetkiniz yok!");
        window.location.href = 'index.html'; // Ana sayfaya postala
        return; // Kodun geri kalanını çalıştırma
    }

    // Çıkış Yap Butonu
    document.getElementById('admin-logout').addEventListener('click', async () => {
        await ApiService.logout();
        window.location.href = 'index.html';
    });

    // 2. RESİM ÖNİZLEME (Kullanıcı resmi seçince hemen görsün)
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // 3. FORM GÖNDERME (Etkinlik Ekleme)
    const form = document.getElementById('add-event-form');

    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Sayfanın yenilenmesini engelle

        // Formdaki tüm verileri (yazılar + resim) otomatik al
        const formData = new FormData(form);

        // Kullanıcıya bilgi ver
        const btn = form.querySelector('.btn-add');
        const oldText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Yükleniyor...";

        // API'ye Gönder
        const result = await ApiService.createEvent(formData);

        if (result.ok) {
            alert("✅ " + result.data.message);
            form.reset(); // Formu temizle
            imagePreview.style.display = 'none'; // Resmi gizle
        } else {
            alert("❌ Hata: " + result.data.message);
        }

        // Butonu eski haline getir
        btn.disabled = false;
        btn.innerText = oldText;
    });
});