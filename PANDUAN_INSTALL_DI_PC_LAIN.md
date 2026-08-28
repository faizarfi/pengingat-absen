# 📖 Panduan Lengkap Instalasi & Setup di PC Baru (Step-by-Step)

Panduan ini berisi langkah-langkah lengkap untuk menginstal dan menjalankan sistem **Pengingat Absensi Otomatis WhatsApp & Telegram** di komputer / PC Windows yang baru.

---

## 📋 DAFTAR ISI
1. [Kebutuhan Software (Prerequisites)](#1-kebutuhan-software-prerequisites)
2. [Menyalin / Download Project](#2-menyalin--download-project-ke-pc-baru)
3. [Konfigurasi File Environment (.env)](#3-konfigurasi-file-env)
4. [Instalasi Dependensi (Composer & NPM)](#4-instalasi-dependensi-composer--npm)
5. [Setup Database, Migrasi & Seeder](#5-setup-database-migrasi--seeder)
6. [Persiapan WhatsApp Desktop](#6-persiapan-whatsapp-desktop)
7. [Cara Menjalankan Sistem (1-Klik / Manual)](#7-cara-menjalankan-sistem)
8. [Akses Web Dashboard & Akun Default](#8-akses-web-dashboard--akun-default)
9. [Tips Autostart Saat PC Hidup](#9-tips-autostart-otomatis-saat-pc-kantor-dinyalakan)
10. [Solusi Masalah (Troubleshooting / FAQ)](#10-solusi-masalah-troubleshooting--faq)

---

## 1. Kebutuhan Software (Prerequisites)

Sebelum memulai, pastikan PC baru sudah terinstal software berikut:

| Software | Versi Rekomendasi | Keterangan & Catatan Penting |
| :--- | :--- | :--- |
| **PHP** | 8.2 atau 8.3 | Bisa via **XAMPP 8.2+** atau PHP Standalone. Pastikan ekstensi `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, `curl`, `zip` aktif. |
| **Composer** | Versi 2.x | Download di [getcomposer.org](https://getcomposer.org/download/). |
| **Node.js & NPM** | LTS (v20 atau v22) | Download di [nodejs.org](https://nodejs.org/). |
| **Python** | 3.10 s.d. 3.12 | Download di [python.org](https://www.python.org/). **WAJIB CENTANG "Add python.exe to PATH"** saat instalasi! |
| **WhatsApp Desktop** | Versi Terbaru | Download resmi dari [whatsapp.com/download](https://www.whatsapp.com/download) atau Microsoft Store. |
| **Git for Windows** *(Opsional)* | Versi Terbaru | Download di [git-scm.com](https://git-scm.com/) jika ingin clone repository. |

---

## 2. Menyalin / Download Project ke PC Baru

Pilih salah satu metode untuk memindahkan folder project:

### Metode A: Lewat Git Clone (Jika menggunakan Git)
Buka **Terminal / PowerShell** di PC baru:
```powershell
cd C:\Users\%USERNAME%\
git clone <URL_REPOSITORY_ANDA> pengingat-absen
cd pengingat-absen
```

### Metode B: Copy Folder via Flashdisk / ZIP
1. Copy seluruh folder `pengingat-absen` ke PC baru (misal di letakkan pada `C:\Users\NAMA_USER\pengingat-absen`).
2. Buka folder tersebut di VS Code / Antigravity IDE atau buka Terminal di folder tersebut.

---

## 3. Konfigurasi File `.env`

1. Buat file `.env` dengan menduplikat dari `.env.example`:
   ```powershell
   copy .env.example .env
   ```
2. Buka file `.env` menggunakan teks editor (Notepad / VS Code).
3. Sesuaikan bagian-bagian penting berikut:

```env
APP_NAME="Pengingat Absen BPS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# PILIHAN DATABASE (Default: MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pengingat_absen
DB_USERNAME=root
DB_PASSWORD=

# KONFIGURASI WA DESKTOP AGENT
ADMIN_WA_NUMBER=08xxxxxxxxxx
WA_DRIVER=desktop
WA_AGENT_ENABLED=true
WA_AGENT_TOKEN=change-this-token-to-something-secure
WA_AGENT_API_ENABLED=true
WA_AGENT_HEARTBEAT_TIMEOUT=60

# TELEGRAM BOT ADMIN (Remote Trigger dari HP)
TELEGRAM_BOT_TOKEN=ISI_TOKEN_BOT_TELEGRAM_ANDA
TELEGRAM_ADMIN_CHAT_ID=ISI_CHAT_ID_ADMIN_TELEGRAM
```

> **Tips Database MySQL (XAMPP):**
> - Buka XAMPP Control Panel, klik **Start** pada Apache dan MySQL.
> - Buka browser ke `http://localhost/phpmyadmin`, buat database baru bernama: `pengingat_absen`.

---

## 4. Instalasi Dependensi (Composer & NPM)

Buka Terminal / Command Prompt di folder project `pengingat-absen`, lalu jalankan perintah berikut secara berurutan:

```powershell
# 1. Install dependensi PHP / Laravel
composer install

# 2. Generate Application Encryption Key
php artisan key:generate

# 3. Install dependensi Javascript & Frontend
npm install

# 4. Build asset frontend (Tailwind / Vite)
npm run build
```

---

## 5. Setup Database, Migrasi & Seeder

Jalankan perintah berikut untuk membuat seluruh tabel database, data pantun default, dan akun login Admin:

```powershell
# 1. Migrasi tabel & isi data awal (admin & pantun)
php artisan migrate --seed

# 2. Sinkronisasi hari libur nasional & cuti bersama
php artisan holidays:sync
```

*(Catatan: Akun login default yang dibuat adalah Email: `admin@example.com` dan Password: `password`)*

---

## 6. Persiapan WhatsApp Desktop

1. Buka aplikasi **WhatsApp Desktop** di Windows.
2. Login / tautkan perangkat dengan nomor WhatsApp yang akan digunakan untuk mengirim pesan pengingat.
3. Biarkan aplikasi WhatsApp Desktop tetap terbuka atau berjalan di latar belakang (background).
4. *Script WA Agent (`agent.py`) dibuat menggunakan built-in Windows Ctypes standard library sehingga **tidak memerlukan** install library pip tambahan.*

---

## 7. Cara Menjalankan Sistem

Ada dua cara mudah untuk menjalankan seluruh sistem di PC baru:

### 🌟 CARA TERCEPAT: Gunakan File Starter (1-Klik)

Cukup **Double-Click** salah satu file `.bat` yang sudah disediakan di folder utama:

#### 1. Untuk Pemakaian Standar / Lokal:
👉 **`START-ALL.bat`**
File ini akan otomatis membuka 4 jendela konsol:
- **Laravel Server** (`http://localhost:8000`)
- **WA Desktop Agent** (Otomasi pengiriman WhatsApp)
- **Telegram Bot Listener** (Penerima remote order dari Telegram HP)
- **Laravel Scheduler** (Penjadwal otomatis jam masuk & pulang)

#### 2. Untuk Pemakaian Online Publik (Bisa Diakses Luar Jaringan):
👉 **`START-ALL-ONLINE.bat`**
Sama seperti di atas, plus menjalankan **Cloudflare Tunnel** otomatis sehingga dashboard dan bot bisa diakses dari mana saja via link HTTPS gratis tanpa perlu setting port forwarding router.

---

### 💻 CARA MANUAL (Jika Ingin Menjalankan Lewat Terminal Satu-Per-Satu):

Jika Anda ingin menjalankan setiap service secara manual melalui Terminal:

1. **Jalankan Web Server:**
   ```powershell
   php artisan serve
   ```
2. **Jalankan WA Desktop Agent:**
   ```powershell
   .\run-agent.bat
   # atau:
   cd wa-desktop-agent
   python agent.py
   ```
3. **Jalankan Bot Telegram:**
   ```powershell
   .\run-telegram-bot.bat
   # atau:
   php artisan telegram:poll
   ```
4. **Jalankan Scheduler Penjadwalan Otomatis:**
   ```powershell
   php artisan schedule:work
   ```

---

## 8. Akses Web Dashboard & Akun Default

1. Buka web browser (Chrome / Edge / Firefox) dan kunjungi:
   **[http://localhost:8000/admin](http://localhost:8000/admin)**
2. Masuk dengan akun default:
   - **Email**: `admin@example.com`
   - **Password**: `password`
3. Di dalam dashboard, Anda dapat:
   - Mengelola data pegawai (tambah, edit, import CSV).
   - Mengatur jam kerja (Senin–Kamis, Jumat, atau jam khusus Ramadhan).
   - Menambah / mengedit variasi pantun masuk dan pulang.
   - Menekan tombol **"Kirim Pengingat Masuk"** atau **"Kirim Pengingat Pulang"** secara langsung.
   - Memantau status antrean WhatsApp di panel Live Outbox.

---

## 9. Tips Autostart Otomatis Saat PC Kantor Dinyalakan

Agar sistem langsung berjalan otomatis setiap kali komputer kantor dinyalakan:

1. Tekan kombinasi tombol **`Windows + R`** pada keyboard.
2. Ketik **`shell:startup`** lalu tekan **Enter** (folder Startup Windows akan terbuka).
3. Buat Shortcut dari file **`START-ALL.bat`** (atau `START-ALL-ONLINE.bat`).
4. Paste shortcut tersebut ke dalam folder Startup tadi.
5. Selesai! Setiap PC dinyalakan, server dan pengirim otomatis aktif.

---

## 10. Solusi Masalah (Troubleshooting / FAQ)

### ❓ 1. Error: `python : can't open file 'agent.py': No such file or directory`
- **Penyebab:** Perintah dijalankan dari luar folder `wa-desktop-agent`.
- **Solusi:** Jalankan `.\run-agent.bat` dari root folder project, atau ketik `cd wa-desktop-agent` lalu `python agent.py`.

### ❓ 2. Error: `'php'` / `'composer'` / `'python'` tidak dikenali (*is not recognized*)
- **Penyebab:** Path software belum terdaftar di Environment Variables Windows.
- **Solusi:**
  - Buka *Start Menu* -> cari *Environment Variables* -> edit `Path`.
  - Tambahkan folder instalasi PHP (contoh: `C:\xampp\php`), Composer, dan Python.
  - Untuk Python, instal ulang lalu centang opsi **"Add python.exe to PATH"**.

### ❓ 3. Error: `SQLSTATE[HY000] [1049] Unknown database 'pengingat_absen'`
- **Penyebab:** Database MySQL belum dibuat di phpMyAdmin.
- **Solusi:** Buka `http://localhost/phpmyadmin`, klik menu **New**, ketik `pengingat_absen` lalu klik **Create**. Setelah itu jalankan `php artisan migrate --seed`.

### ❓ 4. Error saat `composer install` (Ekstensi PHP kurang)
- **Penyebab:** Ekstensi `zip`, `fileinfo`, `pdo_mysql`, atau `curl` belum diaktifkan di `php.ini`.
- **Solusi:** Buka file `php.ini` (di XAMPP klik Config -> PHP (php.ini)), hilangkan tanda titik koma (`;`) di depan:
  ```ini
  extension=curl
  extension=fileinfo
  extension=mbstring
  extension=openssl
  extension=pdo_mysql
  extension=zip
  ```
  Simpan file dan restart Apache.

### ❓ 5. Pesan WhatsApp terbuka tetapi tombol Kirim (Enter) tidak tertekan
- **Penyebab:** WhatsApp Desktop membutuhkan waktu lebih lama untuk memuat chat di PC dengan spesifikasi tertentu.
- **Solusi:** Buka `wa-desktop-agent/agent.py`, cari variabel `focus_delay` (sekitar baris 109) dan naikkan jedanya (misal menjadi `random.uniform(6.0, 8.0)`).
