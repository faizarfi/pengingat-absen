# ⏰ Pengingat Absensi Otomatis BPS (WhatsApp & Telegram v3.0)

Sistem pengingat absensi otomatis (jam masuk dan pulang kerja) terintegrasi dengan **WA Web Agent v3.0 (Headless Background Mode & Anti-Ban)**, **Bot Telegram Admin**, kalender hari libur nasional resmi, dan **Dashboard Web Admin (Laravel 11)**.

---

## ⚡ Cara Menjalankan Aplikasi (1-Klik)

Cukup **Double-Click** file launcher utama di root project:

👉 **`START.bat`**

Akan muncul menu interaktif:
```text
======================================================
   PENGINGAT ABSENSI BPS - LAUNCHER UTAMA
======================================================
  [1] Jalankan Semua Layanan Lokal (Standar)
  [2] Jalankan Semua + Online Publik (Cloudflare Tunnel)
  [3] Jalankan Hanya WA Agent (Background Mode)
  [4] Jalankan Hanya Bot Telegram Admin
  [5] Buka Dashboard Admin di Web Browser
  [0] Keluar
======================================================
```
*(Atau langsung klik ganda **`START-ALL.bat`** jika ingin langsung menjalankan semua layanan tanpa menu).*

---

## 📚 Buku Panduan & Dokumentasi (Folder `docs/`)

Semua dokumentasi resmi tersimpan rapi di dalam folder **`docs/`**:

1. 💻 **[Panduan Instalasi di PC Lain (docs/PANDUAN_INSTALL_DI_PC_LAIN.md)](file:///c:/Users/USER/pengingat-absen/docs/PANDUAN_INSTALL_DI_PC_LAIN.md)**
   - Langkah demi langkah setup dari nol di PC / Laptop baru.
   - Tanpa perlu install Python atau WhatsApp Desktop.
   - Setup dependensi, scan QR sekali, dan troubleshooting.

2. ☁️ **[Panduan Hosting VPS & Arsitektur Anti-Ban (docs/PANDUAN_HOSTING_VPS_DAN_AGENT.md)](file:///c:/Users/USER/pengingat-absen/docs/PANDUAN_HOSTING_VPS_DAN_AGENT.md)**
   - Arsitektur Hybrid terbaik (Web Laravel di Cloud VPS + Pengirim WA di PC Kantor).
   - Setup Server VPS Ubuntu 22.04/24.04, Nginx, SSL Certbot, Crontab, dan Webhook Telegram 24 jam.

3. 📊 **[Contoh Format Import Pegawai CSV (docs/contoh-import-pegawai.csv)](file:///c:/Users/USER/pengingat-absen/docs/contoh-import-pegawai.csv)**
   - Template CSV untuk import data nama dan nomor WhatsApp pegawai ke sistem.

---

## 📁 Struktur Direktori Project

```text
pengingat-absen/
├── bin/                 # Tool binary (cloudflared.exe)
├── docs/                # Seluruh buku panduan & template CSV
├── scripts/             # Runner script per layanan (agent, telegram, tunnel)
├── wa-desktop-agent/    # WhatsApp Web Agent v3.0 (Node.js background mode)
├── app/                 # Backend Laravel 11
├── resources/           # Frontend views (Blade), CSS (Tailwind), JS
├── routes/              # Routing web & API
├── START.bat            # LAUNCHER UTAMA (Menu Interaktif)
├── START-ALL.bat        # Shortcut 1-klik jalan semua
└── README.md            # Dokumentasi utama
```

---

## 🖥️ Akses Dashboard Web
- **URL**: [http://localhost:8000/admin](http://localhost:8000/admin)
- **Email Default**: `admin@example.com`
- **Password Default**: `password`
