# Panduan Menggunakan Bot Telegram Admin (Remote Trigger dari HP)

Sistem **Bot Telegram Admin** memungkinkan Anda mengontrol pengiriman WhatsApp Pengingat Absen langsung dari HP secara **100% Gratis dan Resmi**.

---

## 📱 LANGKAH 1: Buat Bot Telegram (Hanya 1 Menit)

1. Buka aplikasi **Telegram** di HP.
2. Cari **`@BotFather`** di kolom pencarian.
3. Klik **Start**, lalu ketik:
   ```text
   /newbot
   ```
4. Beri nama bot (contoh: `Pengingat Absen BPS`).
5. Beri username bot berakhiran `bot` (contoh: `bps_absen_reminder_bot`).
6. Salin **HTTP API Token** yang diberikan oleh BotFather (contoh: `7123456789:AAFk7_example...`).

---

## 🆔 LANGKAH 2: Dapatkan ID Akun Telegram Anda

Agar hanya **Anda (Admin)** yang bisa memberi perintah ke bot:
1. Cari akun **`@userinfobot`** di Telegram.
2. Klik **Start**.
3. Bot akan membalas dengan Id angka Anda (contoh: `1234567890`).

---

## ⚙️ LANGKAH 3: Masukkan Token ke File `.env`

Buka file [`.env`](file:///c:/Users/USER/pengingat-absen/.env) di Laravel dan isi 2 baris berikut:

```env
TELEGRAM_BOT_TOKEN=7123456789:AAFk7_example_TokenAnda
TELEGRAM_ADMIN_CHAT_ID=1234567890
```

---

## 🚀 LANGKAH 4: Menjalankan Bot

### A. Jika di Komputer Lokal (Localhost):
Buka terminal baru dan jalankan:
```bash
php artisan telegram:poll
```
*(Bot langsung aktif menerima chat dari HP Anda!)*

### B. Jika di Server VPS (Sudah ada Domain HTTPS):
Cukup jalankan 1 kali:
```bash
php artisan telegram:set-webhook https://domain-anda.com/api/telegram/webhook
```

---

## 💬 Daftar Tombol & Perintah di Telegram HP:

Di HP Anda, buka bot yang telah dibuat dan klik **Start**. Akan muncul tombol keyboard otomatis:

| Tombol / Perintah | Fungsi |
| :--- | :--- |
| **`🌅 Kirim Masuk`** / `/masuk` | Memicu pengiriman pengingat absen masuk pagi ke semua pegawai via WhatsApp Desktop. |
| **`🌇 Kirim Pulang`** / `/pulang` | Memicu pengiriman pengingat absen pulang sore ke semua pegawai via WhatsApp Desktop. |
| **`📊 Status Sistem`** / `/status` | Mengecek apakah PC kantor online dan melihat sisa antrean outbox. |
| **`🏖️ Cek Hari Libur`** / `/libur` | Mengecek apakah hari ini tanggal merah/weekend dan melihat daftar libur mendatang. |
| **`/broadcast [pesan]`** | Mengirimkan pesan pengumuman bebas ke seluruh pegawai aktif. |
