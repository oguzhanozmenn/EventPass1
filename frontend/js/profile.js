import { ApiService } from './services/api.js';

document.addEventListener('DOMContentLoaded', async () => {

    // 1. GÜVENLİK KONTROLÜ
    const user = await ApiService.checkAuth();
    if (!user) {
        window.location.href = 'login.html';
        return;
    }

    // Kullanıcı adını yaz
    document.getElementById('user-info').innerText = `${user.name} (${user.role === 'admin' ? 'Yönetici' : 'Üye'})`;

    // Çıkış Butonu
    document.getElementById('profile-logout').addEventListener('click', async (e) => {
        e.preventDefault(); await ApiService.logout(); window.location.href = 'index.html';
    });

    // 2. BİLETLERİ ÇEK
    const ticketListEl = document.getElementById('ticket-list');
    const bookings = await ApiService.getMyBookings();

    ticketListEl.innerHTML = '';

    if (bookings.length === 0) {
        ticketListEl.innerHTML = `
            <div class="empty-state">
                <h3>Henüz hiç bilet almamışsınız. 😔</h3>
                <p>Hemen ana sayfaya gidip eğlenceye katılın!</p>
                <a href="index.html" style="display:inline-block; margin-top:15px; background:#3498db; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Etkinlikleri Keşfet</a>
            </div>
        `;
        return;
    }

    // 3. BİLETLERİ LİSTELE
    bookings.forEach(booking => {
        const eventDate = new Date(booking.event_date).toLocaleDateString('tr-TR', {
            day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        // QR Kod Simülasyonu (Google Chart API kullanarak gerçek QR üretelim!)
        // booking_id'yi QR koda gömüyoruz.
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=EVENTPASS-TICKET-${booking.booking_id}`;

        const imageSrc = booking.image_url ? booking.image_url : 'assets/images/default-event.jpg';

        const ticketHTML = `
            <div class="ticket-card">
                <img src="${imageSrc}" class="ticket-img" alt="Etkinlik Resmi">
                
                <div class="ticket-info">
                    <h3 style="margin:0 0 10px 0; color:#2c3e50;">${booking.title}</h3>
                    <p style="margin:0; color:#7f8c8d;">📍 ${booking.venue}</p>
                    <p style="margin:5px 0; color:#e67e22; font-weight:bold;">📅 ${eventDate}</p>
                    <div style="margin-top:auto; font-size:0.9rem;">
                        <span>🎟️ ${booking.ticket_count} Adet</span> | 
                        <span>💰 ${booking.total_price} ₺</span>
                    </div>
                </div>

                <div class="ticket-qr">
                    <img src="${qrUrl}" alt="QR Kod" style="width:80px; height:80px; margin-bottom:5px;">
                    <span class="ticket-status">ONAYLANDI</span>
                    <span style="font-size:10px; color:#aaa;">#${booking.booking_id}</span>
                </div>
            </div>
        `;

        ticketListEl.innerHTML += ticketHTML;
    });
});