import { ApiService } from './services/api.js';

document.addEventListener('DOMContentLoaded', async () => {

    // Global Değişkenler
    let allEvents = [];
    let currentCategory = 'all';

    // --- 1. KULLANICI DURUMU VE MENÜ ---
    const user = await ApiService.checkAuth();
    const nav = document.querySelector('nav');

    if (user) {
        // DÜZELTME: Linklerin başına './' ekledik (Aynı dizin demek)
        // frontend/ öneki KESİNLİKLE kaldırıldı.
        nav.innerHTML = `
            <span style="margin-right:15px; font-weight:bold; color:#2c3e50;">Merhaba, ${user.name} 👋</span>
            
            <a href="./profile.html" class="btn-nav" style="background:#3498db; color:white; padding:8px 15px; border-radius:5px; text-decoration:none; margin-right:10px; font-size:14px;">
                🎫 Biletlerim
            </a>

            ${user.role === 'admin' ?
            '<a href="./admin.html" class="btn-nav" style="background:#f1c40f; color:black; padding:8px 15px; border-radius:5px; text-decoration:none; margin-right:10px; font-size:14px;">🛡️ Admin</a>'
            : ''}
            
            <a href="#" id="btn-logout" style="color:#c0392b; text-decoration:none; font-weight:bold; margin-left:5px;">Çıkış</a>
        `;

        document.getElementById('btn-logout').addEventListener('click', async (e) => {
            e.preventDefault();
            await ApiService.logout();
            window.location.reload();
        });

    } else {
        nav.innerHTML = `
            <a href="./index.html" style="text-decoration:none; color:#333; margin-right:15px;">Ana Sayfa</a> 
            <a href="./login.html" class="btn-login">Giriş Yap</a>
        `;
    }

    // --- 2. VERİLERİ ÇEK ---
    const eventListEl = document.getElementById('event-list');
    allEvents = await ApiService.getEvents();
    renderEvents(allEvents);

    // --- 3. FİLTRELEME ---
    const searchInput = document.getElementById('search-input');
    const sortSelect = document.getElementById('sort-select');
    const catBtns = document.querySelectorAll('.cat-btn');

    catBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector('.cat-btn.active').classList.remove('active');
            btn.classList.add('active');
            currentCategory = btn.getAttribute('data-cat');
            filterAndSortEvents(searchInput.value.toLowerCase(), sortSelect.value);
        });
    });

    searchInput.addEventListener('keyup', (e) => filterAndSortEvents(e.target.value.toLowerCase(), sortSelect.value));
    sortSelect.addEventListener('change', (e) => filterAndSortEvents(searchInput.value.toLowerCase(), e.target.value));

    function filterAndSortEvents(searchTerm, sortType) {
        let filtered = allEvents.filter(event => {
            const matchesSearch = event.title.toLowerCase().includes(searchTerm) ||
                event.venue.toLowerCase().includes(searchTerm);
            const matchesCategory = (currentCategory === 'all') || (event.category === currentCategory);
            return matchesSearch && matchesCategory;
        });

        filtered.sort((a, b) => {
            const priceA = parseFloat(a.price);
            const priceB = parseFloat(b.price);
            const dateA = new Date(a.date.replace(' ', 'T'));
            const dateB = new Date(b.date.replace(' ', 'T'));

            if (sortType === 'price-asc') return priceA - priceB;
            if (sortType === 'price-desc') return priceB - priceA;
            if (sortType === 'date-asc') return dateA - dateB;
            if (sortType === 'date-desc') return dateB - dateA;
        });

        renderEvents(filtered);
    }

    // --- 4. EKRANA BASMA ---
    function renderEvents(events) {
        eventListEl.innerHTML = '';

        if (!events || events.length === 0) {
            eventListEl.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; margin-top: 30px;">
                    <p style="font-size: 1.2rem; color: #7f8c8d;">Bu kriterlere uygun etkinlik bulunamadı. 😔</p>
                    <button onclick="window.location.reload()" style="margin-top:10px; padding:8px 15px; border:1px solid #ddd; background:white; cursor:pointer; border-radius:5px;">Tümünü Göster</button>
                </div>
            `;
            return;
        }

        events.forEach(event => {
            const dateFormatted = new Date(event.date).toLocaleDateString('tr-TR', {
                day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            const imageHTML = event.image_url ? `<img src="${event.image_url}" alt="${event.title}" style="width:100%; height:200px; object-fit:cover; display:block;">` : '';

            const catLabel = {
                'concert': '🎵 Konser', 'theater': '🎭 Tiyatro', 'standup': '🎤 Stand-up',
                'cinema': '🎬 Sinema', 'festival': '🎪 Festival', 'other': '📌 Diğer'
            }[event.category] || '📌 Etkinlik';

            const cardHTML = `
                <div class="event-card" style="position: relative;">
                    <span style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                        ${catLabel}
                    </span>
                    ${imageHTML}
                    <div class="card-header">
                        <h3>${event.title}</h3>
                    </div>
                    <div class="card-body">
                        <span class="price-tag">${parseFloat(event.price).toFixed(2)} ₺</span>
                        <p><strong>📍 Yer:</strong> ${event.venue}</p>
                        <p><strong>📅 Tarih:</strong> ${dateFormatted}</p>
                        
                        <div style="margin-top: 15px; text-align: right;">
                            <button class="btn-buy" data-id="${event.id}" data-price="${event.price}">
                                🎟️ Satın Al
                            </button>
                        </div>
                    </div>
                </div>
            `;
            eventListEl.innerHTML += cardHTML;
        });
    }

    // --- 5. SATIN ALMA ---
    eventListEl.addEventListener('click', async (e) => {
        if (e.target.classList.contains('btn-buy')) {
            if (!user) {
                alert("Önce giriş yapmalısınız!");
                // DÜZELTME: Burası da sadece dosya adı oldu
                window.location.href = './login.html';
                return;
            }
            if(!confirm("Satın almak istiyor musunuz?")) return;
            const btn = e.target;
            btn.disabled = true;
            btn.innerText = "İşleniyor...";
            const result = await ApiService.makeBooking(btn.getAttribute('data-id'), btn.getAttribute('data-price'));
            if (result.success) {
                alert("✅ " + result.message);
                btn.innerText = "Satın Alındı";
                btn.style.backgroundColor = "#27ae60";
            } else {
                alert("❌ Hata: " + result.message);
                btn.disabled = false;
                btn.innerText = "🎟️ Satın Al";
            }
        }
    });
});