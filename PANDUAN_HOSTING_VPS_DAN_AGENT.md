# Panduan Hosting ke VPS & Menjalankan WA Desktop Agent + Bot Telegram

Panduan praktis dan simpel untuk menjalankan web Laravel di **Server VPS** (Cloud/Linux) sementara pengiriman WhatsApp tetap berjalan melalui **WhatsApp Desktop di PC Windows Kantor**, serta integrasi **Bot Telegram Admin** untuk kontrol dari HP.

---

## 🏛️ Gambaran Arsitektur

```text
┌─────────────────────────────────────────────────────────┐
│                       SERVER VPS                        │
│                 (Ubuntu / Nginx / SSL)                  │
│                                                         │
│  • Web Dashboard Admin & Kalender Libur                 │
│  • Database MySQL / SQLite                              │
│  • Laravel Scheduler (Cron Job Jam Masuk/Pulang)        │
│  • Agent REST API Endpoint: https://domain-anda.com     │
│  • Telegram Bot Webhook Endpoint                        │
└───────────────┬─────────────────────────┬───────────────┘
                │                         │
      (Internet / HTTPS)        (Telegram Bot Webhook)
                │                         │
                ▼                         ▼
┌───────────────────────────────┐   ┌───────────────────────────┐
│        KOMPUTER KANTOR        │   │         HP ADMIN          │
│       (Windows 10 / 11)       │   │        (Telegram)         │
│                               │   │                           │
│ • WhatsApp Desktop (Login WA) │   │ • Tombol Kirim Masuk      │
│ • WA Desktop Agent (agent.py) │   │ • Tombol Kirim Pulang     │
│   (Otomatis kirim anti-ban)   │   │ • Cek Status & Outbox     │
└───────────────────────────────┘   └───────────────────────────┘
```

---

## 🚀 LANGKAH 1: Setup di Server VPS

### 1. Upload Project & Konfigurasi `.env`
Di VPS Anda, buka file `.env` dan pastikan konfigurasi berikut terisi:

```env
APP_NAME="Pengingat Absen BPS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Database VPS (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=pengingat_absen
DB_USERNAME=user_db
DB_PASSWORD=password_db

# WA Desktop Automation
WA_DRIVER=desktop
WA_AGENT_ENABLED=true
WA_AGENT_TOKEN=rahasia-token-kantor-123456
WA_AGENT_API_ENABLED=true

# Telegram Bot Admin (Remote Trigger dari HP)
TELEGRAM_BOT_TOKEN=8620969809:AAFbmbgTGW6dmYk7MWIfSxc_7PNYfxZhibY
TELEGRAM_ADMIN_CHAT_ID=7178352292
```

### 2. Jalankan Migrasi & Sinkronisasi Data Libur di VPS
```bash
php artisan migrate --force
php artisan holidays:sync
```

### 3. Daftarkan Webhook Telegram Bot (Cukup 1x di VPS)
Karena VPS Anda sudah memiliki domain publik HTTPS, daftarkan webhook Telegram agar bot merespons cepat tanpa perlu polling:
```bash
php artisan telegram:set-webhook https://domain-anda.com/api/telegram/webhook
```
> *(Bot Telegram Anda di HP sekarang langsung aktif 24 jam tanpa perlu terminal standby)*

### 4. Aktifkan Laravel Scheduler (Cron Job) di VPS
Buka crontab server:
```bash
crontab -e
```
Tambahkan baris berikut di bagian paling bawah:
```text
* * * * * cd /var/www/pengingat-absen && php artisan schedule:run >> /dev/null 2>&1
```

---

## 💻 LANGKAH 2: Setup di PC Windows Kantor

Di PC kantor, Anda hanya perlu mengarahkan `agent.py` ke alamat domain VPS:

### 1. Buka File `agent.py` di Folder `wa-desktop-agent`
Ubah bagian konfigurasi paling atas:

```python
# Ganti dengan domain VPS dan token Anda
API_BASE_URL = "https://domain-anda.com"
AGENT_TOKEN = "rahasia-token-kantor-123456"
```

### 2. Buka Aplikasi WhatsApp Desktop
- Pastikan WhatsApp Desktop di Windows sudah terbuka dan login dengan nomor kantor.

### 3. Jalankan Agent
- Klik ganda file **`run-agent.bat`** (atau jalankan `python agent.py`).

Output di layar:
```text
==================================================
🚀 WA Desktop Agent (Python Runner) Aktif
🌐 Backend API: https://domain-anda.com
==================================================
[08:00:00] ❤️ Heartbeat sent OK (Agent Online)
```

---

## 🔄 LANGKAH 3: Cara Penggunaan Sehari-hari

1. **Otomatis Melalui Scheduler**:
   - Server VPS secara otomatis mengecek jam masuk/pulang dan hari libur.
   - Jika hari kerja, server memasukkan pesan ke antrean `wa_outbox`.
   - PC kantor otomatis mengirimkan pesan satu per satu ke pegawai via WhatsApp Desktop (dengan jeda aman 6–15 detik).
   - Begitu selesai, server mengirimkan **Laporan Rekap Selesai ke Telegram HP Admin**.

2. **Manual dari HP (Telegram Bot)**:
   - Buka chat dengan `@bps_reminder_bot` di HP.
   - Klik **`🌅 Kirim Masuk`** atau **`🌇 Kirim Pulang`**.
   - PC kantor langsung memproses pengiriman saat itu juga.

3. **Manual dari Web Dashboard VPS**:
   - Buka `https://domain-anda.com/admin` dari browser HP/Laptop.
   - Anda dapat memantau live status antrean, retry pesan gagal, dan mengecek kalender hari libur nasional.

---

## ⚡ Tips Agar Agent Otomatis Jalan Saat PC Dinyalakan (Autostart)

Agar Anda tidak perlu membuka `run-agent.bat` secara manual setiap pagi:

1. Tekan tombol **`Windows + R`** di keyboard PC kantor.
2. Ketik **`shell:startup`** lalu tekan Enter.
3. Buat **Shortcut** dari file `wa-desktop-agent/run-agent.bat` dan paste ke dalam folder startup tersebut.
4. Selesai! Setiap kali PC kantor dihidupkan, agent otomatis aktif dan terhubung ke VPS.
