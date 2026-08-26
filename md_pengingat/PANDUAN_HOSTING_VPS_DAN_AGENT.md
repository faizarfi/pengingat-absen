# Panduan Hosting ke VPS & Menjalankan WA Desktop Agent

Panduan praktis dan simpel untuk menjalankan web Laravel di **Server VPS** (Cloud/Linux) sementara pengiriman WhatsApp tetap berjalan melalui **WhatsApp Desktop di PC Windows Kantor**.

---

## 🏛️ Gambaran Arsitektur

```text
┌─────────────────────────────────────────┐
│              SERVER VPS                 │
│  (Ubuntu / Nginx / Cloud)               │
│                                         │
│  • Web Dashboard Admin                  │
│  • Database MySQL                       │
│  • Laravel Scheduler (Cron Job)         │
│  • Agent REST API Endpoint              │
│    URL: https://domain-anda.com         │
└────────────────────┬────────────────────┘
                     │
                     │ Internet (HTTPS + Bearer Token)
                     ▼
┌─────────────────────────────────────────┐
│            KOMPUTER KANTOR              │
│          (Windows 10 / 11)              │
│                                         │
│  1. WhatsApp Desktop (Sudah Login)      │
│  2. WA Desktop Agent (agent.py / .exe)  │
└─────────────────────────────────────────┘
```

---

## 🚀 LANGKAH 1: Setup di Server VPS

### 1. Upload Project & Konfigurasi `.env`
Di VPS Anda, buka file `.env` dan pastikan konfigurasi berikut terisi:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Database VPS
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=pengingat_absen
DB_USERNAME=user_db
DB_PASSWORD=password_db

# WA Desktop Automation (SAMA DENGAN DI PC KANTOR)
WA_DRIVER=desktop
WA_AGENT_ENABLED=true
WA_AGENT_TOKEN=rahasia-token-kantor-123456
WA_AGENT_API_ENABLED=true
```

### 2. Jalankan Migrasi Database di VPS
```bash
php artisan migrate --force
```

### 3. Aktifkan Laravel Scheduler (Cron Job) di VPS
Edit crontab server:
```bash
crontab -e
```
Tambahkan baris berikut di bagian paling bawah:
```text
* * * * * cd /var/www/pengingat-absen && php artisan schedule:run >> /dev/null 2>&1
```

> Sekarang sistem Laravel di VPS sudah siap membuat antrean otomatis setiap jam pengingat tiba.

---

## 💻 LANGKAH 2: Setup di PC Windows Kantor

Hanya perlu mengarahkan Agent di PC kantor ke alamat VPS Anda:

### 1. Buka File `agent.py` di Folder `wa-desktop-agent`
Ubah bagian konfigurasi paling atas:

```python
# Ganti dengan domain VPS dan token Anda
API_BASE_URL = "https://domain-anda.com"
AGENT_TOKEN = "rahasia-token-kantor-123456"
```

*(Jika menggunakan C# `appsettings.json`, ubah `LaravelApi.BaseUrl` dan `LaravelApi.Token` ke nilai yang sama)*.

### 2. Buka Aplikasi WhatsApp Desktop
- Pastikan WhatsApp Desktop di Windows sudah terbuka dan login dengan akun pengirim.

### 3. Jalankan Agent
- Klik ganda file **`run-agent.bat`** (atau jalankan `python agent.py`).

Output di layar akan muncul:
```text
==================================================
🚀 WA Desktop Agent (Python Runner) Aktif
🌐 Backend API: https://domain-anda.com
==================================================
[08:00:00] ❤️ Heartbeat sent OK (Agent Online)
```

---

## 🔄 LANGKAH 3: Cara Kerja Setelah Online

1. **Admin / User** membuka web dashboard di VPS (`https://domain-anda.com`).
2. Badge dashboard akan otomatis menyala hijau: **`Agent Online (WA Desktop Ready)`**.
3. Saat jadwal absensi tiba (misal: 07:00 / 16:00) atau saat tombol kirim diklik:
   - Server VPS memasukkan semua pesan ke antrean (`wa_outbox`).
   - PC Kantor mendeteksi ada pesan baru via HTTPS REST API.
   - WhatsApp Desktop di PC kantor otomatis mengirimkan pesan satu per satu ke pegawai.
   - Status pesan di server VPS otomatis berubah menjadi **Terkirim**.

---

## ⚡ Tips Agar Agent Otomatis Jalan Saat PC Menyala (Autostart)

Agar Anda tidak perlu membuka `run-agent.bat` secara manual setiap kali komputer dinyalakan:

1. Tekan tombol **`Windows + R`** di keyboard.
2. Ketik **`shell:startup`** lalu tekan Enter (Folder Startup Windows akan terbuka).
3. Buat **Shortcut** dari file `run-agent.bat` dan letakkan di dalam folder startup tersebut.
4. Selesai! Agent akan otomatis berjalan setiap kali PC kantor dihidupkan.
