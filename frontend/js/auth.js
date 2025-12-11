import { ApiService } from './services/api.js';

document.addEventListener('DOMContentLoaded', () => {
    // Elementleri Seç
    const loginSection = document.getElementById('login-section');
    const registerSection = document.getElementById('register-section');
    const msgBox = document.getElementById('msg-box');

    // Geçiş Linkleri
    document.getElementById('show-register').onclick = () => {
        loginSection.classList.add('hidden');
        registerSection.classList.remove('hidden');
        msgBox.style.display = 'none';
    };

    document.getElementById('show-login').onclick = () => {
        registerSection.classList.add('hidden');
        loginSection.classList.remove('hidden');
        msgBox.style.display = 'none';
    };

    // Mesaj Gösterme Fonksiyonu
    function showMessage(msg, type) {
        msgBox.innerText = msg;
        msgBox.className = `message-box ${type}`; // error veya success
        msgBox.style.display = 'block';
    }

    // --- GİRİŞ YAPMA ---
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const pass = document.getElementById('login-password').value;

        const res = await ApiService.login(email, pass);

        if (res.ok) {
            showMessage("Giriş başarılı! Yönlendiriliyorsunuz...", "success");
            setTimeout(() => {
                window.location.href = 'index.html'; // Ana sayfaya git
            }, 1000);
        } else {
            showMessage(res.data.message, "error");
        }
    });

    // --- KAYIT OLMA ---
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;
        const pass = document.getElementById('reg-password').value;

        const res = await ApiService.register(name, email, pass);

        if (res.ok) {
            showMessage("Kayıt başarılı! Şimdi giriş yapabilirsiniz.", "success");
            setTimeout(() => {
                // Otomatik olarak giriş ekranına dön
                document.getElementById('show-login').click();
                // E-postayı otomatik doldur
                document.getElementById('login-email').value = email;
            }, 1500);
        } else {
            showMessage(res.data.message, "error");
        }
    });
});