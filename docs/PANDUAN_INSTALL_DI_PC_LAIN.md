# 📖 Panduan Lengkap Instalasi & Penggunaan di PC Lain (Step-by-Step)

Panduan ini berisi tutorial lengkap dan mudah dipahami untuk menginstal dan menjalankan sistem **Pengingat Absensi Otomatis WhatsApp & Telegram (BPS)** di komputer / laptop baru (Windows 10 / 11 atau Linux).

---

## 🌟 Keunggulan Versi Terbaru (v3.0)
- ❌ **TIDAK PERLU** install Python lagi.
- ❌ **TIDAK PERLU** install aplikasi WhatsApp Desktop lagi.
- ✅ **100% Background Mode**: Pengiriman WhatsApp berjalan di latar belakang menggunakan browser headless (Chromium / Google Chrome / Microsoft Edge bawaan Windows).
- ✅ **Anti-Ban Otomatis**: Delay acak manusiawi (20–40 detik) + jeda istirahat berkala (batch cooldown tiap 3 pesan). Sangat aman untuk kuota 50 pesan pagi & 50 pesan sore.
- ✅ **Sesi Login Tersimpan**: Scan QR cukup 1x saat pertama kali pasang, setelah itu otomatis login selamanya.
- ✅ **Logging & Notifikasi**: Semua riwayat tersimpan rapi per hari di folder `logs/`, dan notifikasi otomatis dikirim ke Telegram jika koneksi WhatsApp terputus.

---

## 📋 Daftar Isi
1. [Kebutuhan Software (Prerequisites)](#1-kebutuhan-software-prerequisites)
2. [Menyalin / Download Project ke PC Baru](#2-menyalin--download-project-ke-pc-baru)
3. [Konfigurasi File Environment (.env)](#3-konfigurasi-file-env)
4. [Instalasi Dependensi (Composer & NPM)](#4-instalasi-dependensi-composer--npm)
5. [Setup Database, Migrasi & Seeder](#5-setup-database-migrasi--seeder)
6. [Tautkan WhatsApp (Scan QR Sekali Saja)](#6-tautkan-whatsapp-scan-qr-cukup-1x)
7. [Cara Menjalankan Sistem (1-Klik atau Manual)](#7-cara-menjalankan-sistem)
8. [Akses Web Dashboard Admin](#8-akses-web-dashboard-admin)
9. [Tips Autostart Saat PC Kantor Dihidupkan](#9-tips-autostart-otomatis-saat-pc-kantor-dihidupkan)
10. [Panduan Pemecahan Masalah (Troubleshooting / FAQ)](#10-panduan-pemecahan-masalah-troubleshooting--faq)

---

## 1. Kebutuhan Software (Prerequisites)

Pastikan software berikut sudah terinstal di PC baru:

| Software | Versi Minimal | Cara Mendapatkan / Catatan |
| :--- | :--- | :--- |
| **PHP** | 8.2 atau 8.3 | Bisa install via **XAMPP 8.2+** (paling mudah) atau PHP standalone. Pastikan ekstensi `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `zip` aktif di `php.ini`. |
| **Composer** | Versi 2.x | Download di [getcomposer.org](https://getcomposer.org/download/). |
| **Node.js & NPM** | v18 / v20 / v22 (LTS) | Download di [nodejs.org](https://nodejs.org/) (pilih versi LTS). |
| **Web Browser** | Bebas | **Google Chrome** atau **Microsoft Edge** (Edge sudah otomatis ada di Windows 10/11). |
| **Database** | MySQL / SQLite | Jika menggunakan XAMPP, gunakan MySQL. Atau bisa juga langsung menggunakan **SQLite** (tanpa perlu install XAMPP sama sekali). |
| **Git for Windows** | Opsional | Download di [git-scm.com](https://git-scm.com/) jika ingin clone via Git. |

> [!NOTE]
> Anda **TIDAK PERLU** menginstal Python atau WhatsApp Desktop! Agen WA v3.0 sudah berjalan 100% menggunakan Node.js dan headless browser bawaan.

---

## 2. Menyalin / Download Project ke PC Baru

Pilih salah satu cara berikut untuk memindahkan project ke PC baru:

### Opsi A: Lewat Git Clone (Rekomendasi jika ada Git)
Buka Command Prompt (CMD) atau PowerShell di PC baru:
```powershell
cd C:\Users\%USERNAME%
git clone <URL_REPOSITORY_ANDA> pengingat-absen
cd pengingat-absen
```

### Opsi B: Copy Lewat Flashdisk / File ZIP
1. Salin seluruh folder project `pengingat-absen` (kecuali folder `vendor` dan `node_modules` jika ukurannya terlalu besar).
2. Letakkan di PC baru, misalnya di folder `C:\pengingat-absen` atau `C:\Users\NAMA_USER\pengingat-absen`.
3. Buka folder tersebut di Terminal / Command Prompt.

---

## 3. Konfigurasi File `.env`

1. Buat file `.env` dengan menyalin contoh dari `.env.example`:
   ```powershell
   copy .env.example .env
   ```
2. Buka file `.env` menggunakan Notepad atau VS Code.
3. Sesuaikan konfigurasi berikut:

```env
APP_NAME="Pengingat Absen BPS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# PILIHAN DATABASE 1: MySQL (Jika menggunakan XAMPP)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pengingat_absen
DB_USERNAME=root
DB_PASSWORD=

# ATAU PILIHAN DATABASE 2: SQLite (Super simpel, tanpa perlu buka XAMPP)
# DB_CONNECTION=sqlite
# (Jika pakai SQLite, kosongkan DB_HOST, DB_DATABASE, dll)

# KONFIGURASI WA DESKTOP AGENT v3.0
ADMIN_WA_NUMBER=08xxxxxxxxxx
WA_DRIVER=desktop
WA_AGENT_ENABLED=true
WA_AGENT_TOKEN=change-this-token-to-something-secure
WA_AGENT_API_ENABLED=true
WA_AGENT_HEARTBEAT_TIMEOUT=60
WA_DELAY_MIN=20
WA_DELAY_MAX=40

# BOT TELEGRAM ADMIN (Remote Control dari HP)
TELEGRAM_BOT_TOKEN=8620969809:AAFbmbgTGW6dmYk7MWIfSxc_7PNYfxZhibY
TELEGRAM_ADMIN_CHAT_ID=7178352292
```

> [!TIP]
> **Jika Menggunakan Database MySQL XAMPP:**
> Buka XAMPP Control Panel, klik **Start** pada Apache dan MySQL. Buka browser ke `http://localhost/phpmyadmin`, buat database baru bernama `pengingat_absen`.

---

## 4. Instalasi Dependensi (Composer & NPM)

Jalankan perintah berikut di dalam folder utama `pengingat-absen`:

### A. Dependensi Backend Laravel & Frontend
```powershell
# 1. Install paket PHP
composer install

# 2. Generate Application Key Laravel
php artisan key:generate

# 3. Install paket frontend & build assets
npm install
npm run build
```

### B. Dependensi WA Agent v3.0
Masuk ke folder `wa-desktop-agent` dan install paket Node.js:
```powershell
cd wa-desktop-agent
npm install
cd ..
```

---

## 5. Setup Database, Migrasi & Seeder

Jalankan perintah ini di folder utama untuk membuat tabel database, mengisi data pantun default, dan akun login admin:

```powershell
# 1. Jalankan migrasi tabel dan seeder data awal
php artisan migrate --seed

# 2. Sinkronkan hari libur nasional & cuti bersama resmi
php artisan holidays:sync
```

> **Akun Default Admin:**
> - **Email**: `admin@example.com`
> - **Password**: `password`

---

## 6. Tautkan WhatsApp (Scan QR Cukup 1x)

Agar agen bisa mengirim pesan via WhatsApp di PC baru, Anda perlu menautkan perangkat sekali saja:

1. Di terminal root project, jalankan:
   ```powershell
   .\run-agent.bat
   ```
2. Tunggu beberapa detik hingga muncul **QR Code** di layar terminal.
3. Buka aplikasi WhatsApp di HP Anda:
   - Ketuk titik tiga di pojok kanan atas (Android) atau Pengaturan (iOS).
   - Pilih **Perangkat Tertaut (Linked Devices)**.
   - Ketuk **Tautkan Perangkat (Link a Device)**.
   - Arahkan kamera HP ke QR Code yang muncul di terminal.
4. Setelah berhasil, terminal akan menampilkan pesan:
   ```text
   ✅ WhatsApp siap digunakan!
   ❤️ Heartbeat sent OK (Agent Online — Background Mode)
   ```
5. **Selesai!** Sesi login otomatis tersimpan di folder `wa-desktop-agent/.wwebjs_auth`. Selanjutnya jika PC dimatikan atau direstart, agen akan langsung tersambung tanpa perlu scan QR lagi.

---

## 7. Cara Menjalankan Sistem

Anda dapat memilih cara yang paling sesuai kebutuhan:

### 🚀 Cara 1: Sekali Klik (Rekomendasi untuk Pemakaian Sehari-hari)

Cukup **Double-Click** salah satu file `.bat` di folder utama:

1. **`START-ALL.bat` (Mode Standar / Lokal di PC Kantor)**
   Otomatis menjalankan 4 jendela konsol di latar belakang:
   - **Jendela 1: Laravel Server** (`http://localhost:8000`)
   - **Jendela 2: WA Agent v3.0** (Pengirim pesan WhatsApp otomatis di background)
   - **Jendela 3: Bot Telegram Admin** (Penerima instruksi trigger jarak jauh dari HP)
   - **Jendela 4: Laravel Scheduler** (Pengecek jam masuk/pulang otomatis setiap menit)

2. **`START-ALL-ONLINE.bat` (Mode Online Publik Gratis)**
   Sama seperti di atas, ditambah **Cloudflare Tunnel HTTPS** sehingga dashboard dan bot bisa diakses dari HP di luar kantor tanpa perlu IP publik atau sewa VPS.

---

### 💻 Cara 2: Manual via Terminal (Jika Ingin Debugging)

Buka 4 tab terminal terpisah di folder project:
```powershell
# Tab 1: Web Server
php artisan serve

# Tab 2: WA Agent
.\run-agent.bat

# Tab 3: Bot Telegram
php artisan telegram:poll

# Tab 4: Scheduler
php artisan schedule:work
```

---

## 8. Akses Web Dashboard Admin

1. Buka browser dan akses:
   👉 **[http://localhost:8000/admin](http://localhost:8000/admin)**
2. Login dengan:
   - Email: `admin@example.com`
   - Password: `password`
3. Fitur yang siap dipakai:
   - **Data Pegawai**: Tambah manual atau import file CSV daftar nama dan nomor WA.
   - **Pengaturan Jam Kerja**: Sesuaikan jam masuk & pulang (Senin–Kamis, Jumat, atau jam khusus Ramadhan).
   - **Data Pantun**: Ratusan variasi pantun acak otomatis agar pesan terasa unik dan ramah.
   - **Kirim Cepat**: Tombol *Kirim Masuk Sekarang* dan *Kirim Pulang Sekarang*.
   - **Live Outbox**: Pantau proses pengiriman pesan, delay waktu, dan log status secara realtime.

---

## 9. Tips Autostart Otomatis Saat PC Kantor Dihidupkan

Agar sistem otomatis aktif setiap kali PC kantor dinyalakan tanpa perlu klik apa pun:

1. Tekan tombol **`Windows + R`** pada keyboard.
2. Ketik **`shell:startup`** lalu tekan **Enter** (folder Startup Windows akan terbuka).
3. Buat **Shortcut** dari file **`START-ALL.bat`**.
4. Pindahkan (Paste) shortcut tersebut ke dalam folder Startup tadi.
5. Selesai! Begitu komputer kantor dinyalakan di pagi hari, seluruh sistem pengingat absen langsung berjalan sendiri.

---

## 10. Panduan Pemecahan Masalah (Troubleshooting / FAQ)

### ❓ 1. Error: `Cannot find module 'whatsapp-web.js'`
- **Penyebab:** Paket dependensi di folder `wa-desktop-agent` belum diinstall.
- **Solusi:** Buka terminal dan jalankan:
  ```powershell
  cd wa-desktop-agent
  npm install
  cd ..
  ```

### ❓ 2. Error: `Cannot find module 'C:\...\wa-agent.js'`
- **Penyebab:** Perintah `node wa-agent.js` dijalankan langsung dari folder root utama, padahal file tersebut berada di dalam folder `wa-desktop-agent`.
- **Solusi:** Jalankan menggunakan batch file `.\run-agent.bat` atau gunakan path:
  ```powershell
  node wa-desktop-agent\wa-agent.js
  ```

### ❓ 3. Perintah `php`, `composer`, atau `node` tidak dikenali
- **Penyebab:** Path instalasi software belum masuk ke Environment Variables Windows.
- **Solusi:**
  1. Buka Start Menu -> cari **Environment Variables**.
  2. Klik **Environment Variables** -> pilih variabel `Path` -> klik **Edit**.
  3. Tambahkan path folder instalasi (misal: `C:\xampp\php`, `C:\Program Files\nodejs\`).
  4. Tutup dan buka kembali terminal PowerShell/CMD.

### ❓ 4. Error ekstensi PHP saat `composer install`
- **Penyebab:** Ekstensi wajib belum diaktifkan di `php.ini`.
- **Solusi:** Buka file `php.ini` (di XAMPP klik Config -> PHP (php.ini)), cari dan hilangkan tanda titik koma (`;`) di depan baris berikut:
  ```ini
  extension=curl
  extension=fileinfo
  extension=mbstring
  extension=openssl
  extension=pdo_mysql
  extension=zip
  ```
  Simpan file dan restart Apache.

### ❓ 5. Error Database `Unknown database 'pengingat_absen'`
- **Penyebab:** Database belum dibuat di phpMyAdmin.
- **Solusi:** Buka `http://localhost/phpmyadmin`, buat database baru bernama `pengingat_absen`, lalu jalankan kembali `php artisan migrate --seed`.

### ❓ 6. Bagaimana jika ingin reset login WhatsApp?
- Jika Anda ingin ganti nomor WhatsApp pengirim:
  1. Tutup jendela `WA Desktop Agent`.
  2. Hapus folder `wa-desktop-agent\.wwebjs_auth`.
  3. Jalankan kembali `.\run-agent.bat` dan scan QR dengan nomor baru.
