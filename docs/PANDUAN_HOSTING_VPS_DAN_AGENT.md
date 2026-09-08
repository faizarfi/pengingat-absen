# ☁️ Panduan Lengkap Hosting VPS & Integrasi WA Agent v3.0

Panduan komprehensif ini menjelaskan cara men-deploy sistem **Pengingat Absensi Otomatis (Laravel + WhatsApp Web Agent + Bot Telegram Admin)** ke server produksi/cloud (VPS Linux Ubuntu) serta strategi terbaik agar nomor WhatsApp aman dari pemblokiran (Anti-Ban).

---

## 🏛️ Pilihan Arsitektur Hosting

Ada 2 model arsitektur yang dapat Anda pilih sesuai dengan kebutuhan dan infrastruktur kantor:

### 🌟 Model 1: Arsitektur Hybrid (Sangat Direkomendasikan ⭐⭐⭐⭐⭐)

> **Web & Scheduler di VPS Cloud, Pengirim WA di PC Kantor / Rumah**

```text
┌─────────────────────────────────────────────────────────────────────────┐
│                           SERVER CLOUD / VPS                            │
│                         (Ubuntu 22.04 / 24.04)                          │
│                                                                         │
│  • Web Dashboard Admin & Manajemen Pegawai (HTTPS)                      │
│  • Database MySQL / PostgreSQL                                          │
│  • Laravel Scheduler (Cron Job Otomatis Cek Jam Kerja & Hari Libur)     │
│  • Webhook Bot Telegram (Respons Cepat 24 Jam dari HP Admin)            │
│  • API Endpoint Outbox (/api/agent/*)                                   │
└───────────────────┬─────────────────────────────────┬───────────────────┘
                    │                                 │
           (REST API / HTTPS)               (Telegram Bot Webhook)
                    │                                 │
                    ▼                                 ▼
┌───────────────────────────────────────┐   ┌─────────────────────────────┐
│          PC WINDOWS KANTOR            │   │          HP ADMIN           │
│                                       │   │         (Telegram)          │
│ • WA Agent v3.0 (Background Node.js)  │   │                             │
│ • IP Residential / Internet Kantor    │   │ • Tombol Kirim Masuk/Pulang │
│   (Sangat aman dari deteksi ban Meta) │   │ • Notifikasi Disconnect/Log │
│ • Otomatis kirim dengan delay aman    │   │ • Laporan Rekap Selesai     │
└───────────────────────────────────────┘   └─────────────────────────────┘
```

#### Mengapa Model Hybrid Paling Direkomendasikan?
1. **Bebas Resiko Banned IP Datacenter**: WhatsApp/Meta sangat ketat memantau IP server publik (DigitalOcean, AWS, Linode, Google Cloud). Jika mengirim pesan massal langsung dari IP datacenter, nomor lebih cepat dicurigai sebagai spam bot. Dengan agen berjalan di PC kantor, pesan terkirim menggunakan IP jaringan perumahan/kantor (Residential IP) yang dianggap wajar oleh WhatsApp.
2. **Dashboard & Telegram Aktif 24/7**: Admin bisa memantau dan menekan tombol kirim dari HP kapan saja via Telegram atau website tanpa harus berada di kantor.
3. **Data Tidak Hilang Saat PC Kantor Mati**: Jika PC kantor dimatikan di malam hari, jadwal pengiriman tetap dibuat di database VPS. Begitu PC dinyalakan di pagi hari, agen langsung menyedot antrean pesan dan mengirimkannya secara otomatis.

---

### Model 2: Arsitektur Full VPS (Semua di Cloud Linux)

> **Seluruh sistem (Laravel + Database + WA Agent Headless) berjalan di 1 VPS Linux**

- **Kelebihan**: Tidak memerlukan komputer kantor yang harus menyala. Berjalan 24 jam nonstop di cloud.
- **Syarat**: Menggunakan IP VPS yang memiliki reputasi baik (bukan bekas spammer) dan menerapkan jeda waktu (delay) 20–40 detik per pesan serta batch cooldown.

---

## 🚀 PANDUAN DEPLOYMENT: SETUP SERVER VPS (Ubuntu Linux)

Berikut adalah langkah demi langkah menyiapkan server VPS Ubuntu (bisa di DigitalOcean, Linode, IDCloudHost, Biznet, AWS, dll):

### Langkah 1: Update Server & Install Software Dasar
Login ke VPS via SSH:
```bash
ssh root@IP_SERVER_ANDA
```

Jalankan perintah update dan instalasi dependensi utama:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip nginx mysql-server certbot python3-certbot-nginx
```

### Langkah 2: Install PHP 8.2 / 8.3 & Ekstensi yang Dibutuhkan
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-mbstring \
    php8.2-xml php8.2-zip php8.2-bcmath php8.2-sqlite3 php8.2-intl
```

### Langkah 3: Install Composer & Node.js
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js v20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Langkah 4: Setup Database MySQL di VPS
Masuk ke prompt MySQL:
```bash
sudo mysql
```
Jalankan perintah SQL berikut:
```sql
CREATE DATABASE pengingat_absen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'user_absen'@'localhost' IDENTIFIED BY 'PasswordKuat123!@#';
GRANT ALL PRIVILEGES ON pengingat_absen.* TO 'user_absen'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### Langkah 5: Clone Project & Setup File `.env`
Letakkan project di direktori web server:
```bash
cd /var/www
sudo git clone <URL_REPOSITORY_GIT_ANDA> pengingat-absen
cd pengingat-absen
```

Salin file environment dan atur permission:
```bash
cp .env.example .env
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

Buka dan edit file `.env`:
```bash
nano .env
```

Pastikan konfigurasi production diisi dengan benar:
```env
APP_NAME="Pengingat Absen BPS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://absen.kantoranda.go.id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pengingat_absen
DB_USERNAME=user_absen
DB_PASSWORD=PasswordKuat123!@#

# Konfigurasi WA Agent API
WA_DRIVER=desktop
WA_AGENT_ENABLED=true
WA_AGENT_TOKEN=BuatTokenRahasiaKuatDisini998877
WA_AGENT_API_ENABLED=true
WA_AGENT_HEARTBEAT_TIMEOUT=60
WA_DELAY_MIN=20
WA_DELAY_MAX=40

# Bot Telegram Admin
TELEGRAM_BOT_TOKEN=8620969809:AAFbmbgTGW6dmYk7MWIfSxc_7PNYfxZhibY
TELEGRAM_ADMIN_CHAT_ID=7178352292
```

### Langkah 6: Install Dependensi & Jalankan Migrasi
```bash
# 1. Install dependensi PHP
composer install --no-dev --optimize-autoloader

# 2. Generate Application Key
php artisan key:generate

# 3. Build aset frontend
npm install
npm run build

# 4. Migrasi database & sync hari libur
php artisan migrate --force --seed
php artisan holidays:sync

# 5. Optimasi cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Langkah 7: Konfigurasi Nginx Web Server
Buat file virtual host Nginx:
```bash
sudo nano /etc/nginx/sites-available/pengingat-absen
```

Isi dengan konfigurasi berikut:
```nginx
server {
    listen 80;
    server_name absen.kantoranda.go.id; # Ganti dengan domain atau subdomain Anda
    root /var/www/pengingat-absen/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/pengingat-absen /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Pasang sertifikat SSL gratis (HTTPS):
```bash
sudo certbot --nginx -d absen.kantoranda.go.id
```

---

### Langkah 8: Setup Otomasi Scheduler & Webhook Telegram

1. **Jadwalkan Laravel Scheduler (Cronjob Otomatis Setiap Menit):**
   ```bash
   crontab -e
   ```
   Tambahkan baris berikut di baris paling bawah:
   ```text
   * * * * * cd /var/www/pengingat-absen && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **Daftarkan Webhook Bot Telegram (Cukup 1x):**
   Karena server VPS sudah menggunakan HTTPS publik, daftarkan webhook agar bot Telegram merespons instan tanpa perlu polling manual di terminal:
   ```bash
   php artisan telegram:set-webhook https://absen.kantoranda.go.id/api/telegram/webhook
   ```
   *(Setelah ini, Bot Telegram di HP Anda sudah aktif 24 jam penuh!)*

---

## 💻 HUBUNGKAN WA AGENT DI PC KANTOR KE SERVER VPS (Model Hybrid)

Jika Anda menggunakan **Model Hybrid (Paling Direkomendasikan)**:

1. Di komputer kantor (Windows), buka folder `pengingat-absen`.
2. Buka file `.env` di PC kantor (atau buat file `.env` di dalam folder `wa-desktop-agent`):
   ```env
   WA_API_URL=https://absen.kantoranda.go.id
   WA_AGENT_TOKEN=BuatTokenRahasiaKuatDisini998877
   ```
   *(Pastikan `WA_AGENT_TOKEN` nilainya **persis sama** dengan yang ada di server VPS).*
3. Jalankan pengirim WA di PC kantor:
   ```powershell
   .\run-agent.bat
   ```
4. Agen otomatis tersambung ke server VPS!
   - Di terminal akan muncul: `❤️ Heartbeat sent OK (Agent Online — Background Mode)`.
   - Di web dashboard VPS, status agen akan langsung berubah menjadi **"Online"** dengan indikator hijau.

---

## 🐧 JIKA MEMILIH MENJALANKAN WA AGENT DI VPS LINUX (Model Full VPS)

Jika Anda ingin menjalankan `wa-agent.js` langsung di dalam server VPS Linux tanpa menyalakan PC kantor:

### 1. Install Chromium & Dependensi GUI Headless di Ubuntu
```bash
sudo apt install -y chromium-browser \
    libnss3 libatk-bridge2.0-0 libdrm2 libxkbcommon0 libgbm1 libasound2
```

### 2. Install PM2 (Process Manager Otomatis Restart)
```bash
sudo npm install -g pm2
```

### 3. Install Dependensi Agent
```bash
cd /var/www/pengingat-absen/wa-desktop-agent
npm install
```

### 4. Scan QR WhatsApp Pertama Kali via Terminal SSH
Jalankan sementara untuk scan QR:
```bash
node wa-agent.js
```
- Terminal SSH akan menampilkan QR Code dalam format karakter ASCII.
- Buka WhatsApp di HP -> **Perangkat Tertaut** -> Scan QR tersebut.
- Setelah muncul `✅ WhatsApp siap digunakan!`, tekan `Ctrl + C` untuk berhenti.

### 5. Jalankan Agen 24 Jam Nonstop Menggunakan PM2
Masuk ke root project dan jalankan file ecosystem:
```bash
cd /var/www/pengingat-absen
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

> **Perintah Berguna PM2 di VPS:**
> - Cek status agen: `pm2 status`
> - Lihat live log: `pm2 logs wa-agent`
> - Restart agen: `pm2 restart wa-agent`
> - Stop agen: `pm2 stop wa-agent`

---

## 🛡️ TIPS KEAMANAN & BEST PRACTICES PRODUKSI

1. **Jaga Kerahasiaan Token**: Jangan berikan `WA_AGENT_TOKEN` ke orang lain. Token ini berfungsi sebagai kunci autentikasi antara agen pengirim dan server web.
2. **Cadangkan (Backup) Database Berkala**:
   Buat cronjob backup mingguan database MySQL dengan `mysqldump`:
   ```bash
   mysqldump -u user_absen -p pengingat_absen > /backup/db_absen_$(date +\%F).sql
   ```
3. **Folder Sesi WhatsApp**: Folder `wa-desktop-agent/.wwebjs_auth` berisi sesi login WhatsApp Anda. Pastikan folder ini terdaftar di `.gitignore` agar tidak bocor ke Git repository publik.
4. **Log Retention**: Agen v3.0 otomatis membersihkan file log yang umurnya lebih dari 30 hari di folder `logs/`, sehingga tidak membebani ruang penyimpanan harddisk server.
