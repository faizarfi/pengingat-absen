# Dokumentasi Integrasi WhatsApp Desktop Automation Tanpa Fonnte

> **Sistem Pengingat Absensi Otomatis via WhatsApp Desktop**
>
> Laravel 12 · PHP 8.2+ · MySQL · Windows · WhatsApp Desktop · C#/.NET Agent

---

## Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Tujuan Perubahan](#tujuan-perubahan)
3. [Arsitektur Lama](#arsitektur-lama)
4. [Arsitektur Baru](#arsitektur-baru)
5. [Komponen yang Tetap Digunakan](#komponen-yang-tetap-digunakan)
6. [Komponen yang Dihapus](#komponen-yang-dihapus)
7. [Cara Kerja Sistem Baru](#cara-kerja-sistem-baru)
8. [WA Desktop Agent](#wa-desktop-agent)
9. [Struktur Tabel WA Outbox](#struktur-tabel-wa-outbox)
10. [Status Pengiriman](#status-pengiriman)
11. [Alur Scheduler dan Queue](#alur-scheduler-dan-queue)
12. [Integrasi Laravel dengan Agent](#integrasi-laravel-dengan-agent)
13. [Jika Laravel dan WhatsApp Berada di PC yang Sama](#jika-laravel-dan-whatsapp-berada-di-pc-yang-sama)
14. [Jika Laravel Berada di Server](#jika-laravel-berada-di-server)
15. [Konfigurasi Environment](#konfigurasi-environment)
16. [Perubahan WhatsAppService](#perubahan-whatsappservice)
17. [Contoh Alur Pengiriman](#contoh-alur-pengiriman)
18. [Menjalankan Agent Otomatis](#menjalankan-agent-otomatis)
19. [Deteksi WhatsApp Desktop](#deteksi-whatsapp-desktop)
20. [Dashboard Monitoring](#dashboard-monitoring)
21. [Penanganan Error](#penanganan-error)
22. [Keamanan](#keamanan)
23. [Kelebihan](#kelebihan)
24. [Kekurangan dan Risiko](#kekurangan-dan-risiko)
25. [Teknologi yang Direkomendasikan](#teknologi-yang-direkomendasikan)
26. [Tahapan Implementasi](#tahapan-implementasi)
27. [Struktur Folder yang Disarankan](#struktur-folder-yang-disarankan)
28. [Kesimpulan](#kesimpulan)

---

# Gambaran Umum

Sistem pengingat absensi yang sebelumnya mengirim pesan WhatsApp melalui provider pihak ketiga seperti **Fonnte**, **WaSender**, atau **Infobip** akan diubah menjadi sistem yang mengirim pesan melalui **WhatsApp Desktop resmi** yang berjalan pada komputer Windows.

Laravel tetap bertugas sebagai pusat sistem untuk:

- Menyimpan data pegawai.
- Menyimpan nomor WhatsApp pegawai.
- Menentukan jadwal pengingat.
- Menyimpan template pesan.
- Menjalankan scheduler.
- Membuat antrean pesan.
- Menyimpan log pengiriman.
- Menampilkan monitoring pada dashboard admin.

Pengiriman WhatsApp tidak lagi dilakukan oleh API pihak ketiga. Pengiriman dilakukan oleh sebuah program lokal yang disebut:

```text
WaDesktopAgent
```

Agent tersebut berjalan di Windows dan mengendalikan WhatsApp Desktop secara otomatis.

---

# Tujuan Perubahan

Tujuan utama perubahan ini adalah:

```text
Tanpa Fonnte
Tanpa WaSender
Tanpa Infobip
Tanpa WhatsApp Gateway pihak ketiga
Tanpa token API provider
```

Sistem menjadi:

```text
Laravel
   ↓
Database / Outbox
   ↓
WA Desktop Agent
   ↓
WhatsApp Desktop
   ↓
Pegawai
```

Dengan demikian, pengiriman dilakukan langsung menggunakan akun WhatsApp yang sudah login di aplikasi WhatsApp Desktop pada komputer admin.

---

# Arsitektur Lama

Arsitektur sistem sebelumnya:

```text
┌─────────────┐
│ Scheduler   │
└──────┬──────┘
       │
       ▼
┌────────────────────┐
│ wa:send-reminders  │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ SendWhatsAppJob    │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ WhatsAppService    │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ Fonnte / Provider  │
│ WhatsApp API       │
└─────────┬──────────┘
          │
          ▼
      WhatsApp
```

Laravel melakukan HTTP request ke API provider.

Contoh konfigurasi lama:

```env
WHATSAPP_API_URL=https://api.fonnte.com
WHATSAPP_API_KEY=your-api-key
```

---

# Arsitektur Baru

Arsitektur baru:

```text
┌──────────────────────────┐
│        WEB LARAVEL       │
│                          │
│ Scheduler                │
│ Data Pegawai             │
│ Template Pesan           │
│ Jadwal Masuk/Pulang      │
└────────────┬─────────────┘
             │
             ▼
┌──────────────────────────┐
│        WA_OUTBOX         │
│                          │
│ phone                    │
│ message                  │
│ status                   │
│ scheduled_at             │
└────────────┬─────────────┘
             │
             ▼
┌──────────────────────────┐
│    WA DESKTOP AGENT      │
│                          │
│ C# / .NET                │
│ Windows UI Automation    │
└────────────┬─────────────┘
             │
             ▼
┌──────────────────────────┐
│    WHATSAPP DESKTOP      │
│ akun sudah login         │
└────────────┬─────────────┘
             │
             ▼
          Pegawai
```

Laravel tidak perlu lagi mengetahui cara mengirim pesan ke WhatsApp.

Laravel hanya membuat pekerjaan pengiriman.

Agent yang bertanggung jawab melakukan pengiriman melalui WhatsApp Desktop.

---

# Komponen yang Tetap Digunakan

Bagian berikut tetap dapat dipertahankan:

- Laravel 12.
- PHP 8.2+.
- MySQL.
- Data pegawai.
- Nomor WhatsApp pegawai.
- Status pegawai aktif/nonaktif.
- Template pesan.
- Pantun otomatis.
- Scheduler Laravel.
- Artisan command `wa:send-reminders`.
- Queue Laravel.
- Data log pengiriman.
- Pengingat masuk.
- Pengingat pulang.
- Pengingat terlambat.
- Broadcast manual.
- Dashboard admin.

Jadi sistem lama tidak perlu dibangun ulang dari nol.

---

# Komponen yang Dihapus

Bagian berikut dapat dihapus:

```text
Fonnte API
WaSender API
Infobip API
WHATSAPP_API_URL
WHATSAPP_API_KEY
Webhook khusus Fonnte
HTTP request ke provider WA
```

Contoh konfigurasi lama yang tidak diperlukan lagi:

```env
WHATSAPP_API_URL=https://api.fonnte.com
WHATSAPP_API_KEY=xxxxx
```

---

# Cara Kerja Sistem Baru

Secara umum alurnya sebagai berikut:

```text
1. Laravel Scheduler berjalan.

2. Scheduler mengecek waktu sekarang.

3. Jika waktu cocok dengan jadwal pengingat:

   Laravel mengambil semua pegawai aktif.

4. Laravel membuat pesan berdasarkan template.

5. Pesan dimasukkan ke tabel wa_outbox.

6. Status awal:

   pending

7. WA Desktop Agent membaca pesan pending.

8. Agent memastikan WhatsApp Desktop aktif.

9. Agent membuka nomor tujuan.

10. Agent memasukkan pesan.

11. Agent mengirim pesan.

12. Jika berhasil:

    status = sent

13. Jika gagal:

    status = failed
```

---

# WA Desktop Agent

WA Desktop Agent adalah aplikasi Windows kecil yang dibuat khusus untuk sistem ini.

Nama aplikasi dapat dibuat misalnya:

```text
WaDesktopAgent.exe
```

Agent dapat dibuat menggunakan:

```text
C#
.NET 8
Windows UI Automation
```

Tugas utama agent:

```text
- Membaca antrean pesan.
- Mengecek WhatsApp Desktop.
- Membuka WhatsApp jika belum berjalan.
- Membuka chat berdasarkan nomor tujuan.
- Menuliskan isi pesan.
- Mengirim pesan.
- Mengubah status antrean.
- Menyimpan error jika pengiriman gagal.
- Memberikan heartbeat/status ke Laravel.
```

---

# Struktur Tabel WA Outbox

Disarankan menambahkan tabel baru:

```text
wa_outbox
```

Contoh struktur:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT | Primary key |
| `employee_id` | BIGINT | ID pegawai |
| `phone_number` | VARCHAR | Nomor WhatsApp tujuan |
| `message` | TEXT | Isi pesan |
| `type` | VARCHAR | Jenis pengingat |
| `status` | VARCHAR | Status pengiriman |
| `attempts` | INT | Jumlah percobaan |
| `scheduled_at` | TIMESTAMP | Jadwal pengiriman |
| `processing_at` | TIMESTAMP NULL | Waktu mulai diproses |
| `sent_at` | TIMESTAMP NULL | Waktu berhasil terkirim |
| `last_error` | TEXT NULL | Error terakhir |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

Contoh isi:

```text
id | employee_id | phone          | status
------------------------------------------------
1  | 10          | 628123456789   | pending
2  | 11          | 628129876543   | pending
3  | 12          | 628137778899   | pending
```

---

# Status Pengiriman

Status yang direkomendasikan:

| Status | Keterangan |
|---|---|
| `pending` | Belum diproses |
| `processing` | Sedang diproses agent |
| `sent` | Pesan berhasil dikirim |
| `failed` | Pengiriman gagal |
| `retry` | Menunggu percobaan ulang |
| `cancelled` | Dibatalkan admin |

Alurnya:

```text
pending
   ↓
processing
   ↓
┌───────────┬───────────┐
│           │           │
▼           ▼           ▼
sent      failed      retry
```

---

# Alur Scheduler dan Queue

Scheduler Laravel tetap dapat digunakan.

Contoh:

```php
$schedule->command('wa:send-reminders')->everyMinute();
```

Alur:

```text
Laravel Scheduler
       ↓
wa:send-reminders
       ↓
Cek jam sekarang
       ↓
Cek pegawai aktif
       ↓
Build template pesan
       ↓
Insert ke wa_outbox
       ↓
WA Desktop Agent
```

Contoh jadwal:

```text
07:00 = pre_checkin
07:15 = late_checkin_reminder
07:30 = checkin
16:00 = checkout
16:15 = late_checkout_reminder
```

Untuk hari Jumat, jam pulang dapat tetap menggunakan setting khusus.

---

# Integrasi Laravel dengan Agent

Ada dua metode utama.

## Metode 1 — Database Langsung

Digunakan jika Laravel dan agent berada pada komputer atau jaringan database yang sama.

```text
Laravel
   │
   ▼
MySQL
   ▲
   │
WaDesktopAgent
```

Agent membaca tabel `wa_outbox` secara langsung.

Keuntungan:

- Implementasi sederhana.
- Tidak perlu REST API tambahan.
- Cocok untuk jaringan lokal.

Kekurangan:

- Credential database harus tersedia di agent.
- Kurang ideal jika agent berada di lokasi yang berbeda dengan server.

---

# Jika Laravel dan WhatsApp Berada di PC yang Sama

Ini adalah konfigurasi paling sederhana.

```text
PC WINDOWS

Laravel
   ↓
MySQL
   ↓
WaDesktopAgent.exe
   ↓
WhatsApp Desktop
```

Semua dapat dijalankan pada satu komputer.

Contoh:

```text
C:\xampp\htdocs\pengingat-absen
C:\wa-automation\WaDesktopAgent.exe
WhatsApp Desktop
```

Untuk penggunaan internal kantor, metode ini paling mudah dikembangkan.

---

# Jika Laravel Berada di Server

Jika website berada di VPS/server sedangkan WhatsApp Desktop berada di komputer kantor:

```text
SERVER
────────────────────────────
Laravel
   ↓
Database
   ↓
Agent API
   │
   │ HTTPS
   ▼

PC KANTOR
────────────────────────────
WaDesktopAgent
   ↓
WhatsApp Desktop
```

Laravel dapat menyediakan endpoint seperti:

```text
GET /api/agent/messages
```

Contoh response:

```json
{
    "id": 123,
    "phone": "628123456789",
    "message": "Selamat pagi Bapak Budi..."
}
```

Setelah dikirim:

```text
POST /api/agent/messages/123/sent
```

Jika gagal:

```text
POST /api/agent/messages/123/failed
```

Dengan body:

```json
{
    "error": "WhatsApp Desktop tidak ditemukan"
}
```

---

# Konfigurasi Environment

Konfigurasi lama:

```env
WHATSAPP_API_URL=https://api.fonnte.com
WHATSAPP_API_KEY=xxxxx
```

Dapat diganti menjadi:

```env
WA_DRIVER=desktop
WA_AGENT_ENABLED=true
WA_AGENT_TOKEN=change-this-token
WA_SEND_DELAY=5
WA_MAX_RETRY=3
WA_AGENT_TIMEOUT=30
```

Contoh tambahan jika menggunakan REST Agent:

```env
WA_AGENT_API_ENABLED=true
WA_AGENT_HEARTBEAT_TIMEOUT=60
```

---

# Perubahan WhatsAppService

Sebelumnya:

```text
WhatsAppService
      ↓
HTTP Request
      ↓
Fonnte
      ↓
WhatsApp
```

Setelah perubahan:

```text
WhatsAppService
      ↓
Create Outbox
      ↓
wa_outbox
      ↓
WA Desktop Agent
      ↓
WhatsApp Desktop
```

Jadi `WhatsAppService` tidak lagi melakukan request ke API pihak ketiga.

Contoh konsep service:

```php
public function queueMessage(
    int $employeeId,
    string $phone,
    string $message,
    string $type = 'manual'
): void {
    WaOutbox::create([
        'employee_id' => $employeeId,
        'phone_number' => $phone,
        'message' => $message,
        'type' => $type,
        'status' => 'pending',
        'attempts' => 0,
        'scheduled_at' => now(),
    ]);
}
```

---

# Contoh Alur Pengiriman

Misalnya jadwal masuk:

```text
07:30
```

Pengingat awal:

```text
30 menit sebelumnya
```

Maka pada:

```text
07:00
```

Laravel menjalankan:

```text
wa:send-reminders
```

Pegawai:

```text
Budi  -> 081234567890
Siti  -> 081298765432
Andi  -> 081377788899
```

Laravel membuat antrean:

```text
1 | Budi | 6281234567890 | pending
2 | Siti | 6281298765432 | pending
3 | Andi | 6281377788899 | pending
```

Agent mengambil antrean pertama:

```text
Budi
↓
WhatsApp Desktop
↓
Buka chat 6281234567890
↓
Isi pesan
↓
Kirim
↓
status = sent
```

Agent menunggu beberapa detik:

```text
WA_SEND_DELAY=5
```

Kemudian melanjutkan ke pegawai berikutnya.

---

# Menjalankan Agent Otomatis

Agent sebaiknya otomatis aktif saat Windows menyala.

Pilihan:

```text
Windows Startup
Windows Task Scheduler
Windows Service
```

Rekomendasi production:

```text
Windows Service
```

Alur:

```text
Windows menyala
      ↓
WaDesktopAgent aktif
      ↓
Cek database / API
      ↓
Ada pending?
      ↓
Ya → kirim
Tidak → tunggu
```

---

# Deteksi WhatsApp Desktop

Sebelum mengirim pesan, agent harus memastikan WhatsApp Desktop aktif.

Konsep:

```text
Cari process WhatsApp
      ↓
┌───────────────┐
│ Ada?          │
└──────┬────────┘
       │
   ┌───┴───┐
   │       │
  Ya      Tidak
   │       │
   │       ▼
   │    Start WhatsApp
   │       │
   └───────┘
       ↓
Cari window WhatsApp
       ↓
Lanjut pengiriman
```

Agent tidak disarankan menggunakan koordinat mouse seperti:

```text
klik X=300 Y=200
```

karena mudah rusak ketika ukuran window berubah.

Lebih baik gunakan:

```text
Windows UI Automation
```

untuk mencari elemen berdasarkan control/window.

---

# Dashboard Monitoring

Dashboard Laravel dapat ditambahkan status agent.

Contoh:

```text
WhatsApp Desktop
● TERHUBUNG

Agent
● AKTIF

Pesan Pending
12

Sedang Diproses
1

Terkirim Hari Ini
87

Gagal
2
```

Fitur yang dapat ditambahkan:

```text
[ Test WhatsApp ]
[ Retry Gagal ]
[ Batalkan Pending ]
[ Refresh Agent Status ]
[ Lihat Error ]
```

Agent dapat mengirim heartbeat:

```text
last_seen_at = 2026-08-24 13:30:05
```

Jika heartbeat lebih lama dari batas tertentu:

```text
Agent Offline
```

---

# Penanganan Error

Contoh error yang perlu ditangani:

| Masalah | Penanganan |
|---|---|
| WhatsApp Desktop belum aktif | Jalankan otomatis |
| WhatsApp logout | Tandai failed dan tampilkan notifikasi |
| Windows terkunci | Pause pengiriman |
| Nomor tidak valid | Tandai failed |
| Chat tidak bisa dibuka | Retry |
| Message box tidak ditemukan | Retry / restart WhatsApp |
| Agent mati | Dashboard menampilkan offline |
| Database tidak dapat diakses | Simpan log lokal |
| Internet putus | Retry setelah delay |

Contoh retry:

```text
attempt 1
   ↓ gagal
wait 10 detik
   ↓
attempt 2
   ↓ gagal
wait 30 detik
   ↓
attempt 3
   ↓ gagal
status = failed
```

---

# Keamanan

Walaupun tidak memakai Fonnte, tetap perlu keamanan.

## Token Agent

Jika Laravel dan agent berkomunikasi melalui REST API:

```env
WA_AGENT_TOKEN=very-secret-token
```

Request agent wajib membawa token tersebut.

## Jangan Menyimpan Password WhatsApp

Agent tidak perlu mengetahui password akun WhatsApp.

WhatsApp Desktop cukup sudah login menggunakan sesi resmi aplikasi.

## Batasi Endpoint

Endpoint agent tidak boleh dapat diakses bebas.

Gunakan:

```text
Bearer Token
HTTPS
IP whitelist jika memungkinkan
Rate limit
```

## Logging

Simpan:

```text
message_id
employee_id
phone_number
status
attempts
sent_at
last_error
```

Jangan menyimpan data sensitif yang tidak diperlukan.

---

# Kelebihan

Keuntungan arsitektur ini:

```text
✓ Tidak menggunakan Fonnte
✓ Tidak membutuhkan API key Fonnte
✓ Tidak membayar gateway WA pihak ketiga
✓ Scheduler Laravel tetap digunakan
✓ Database lama tetap digunakan
✓ Template lama tetap digunakan
✓ Dashboard tetap digunakan
✓ Bisa dibuat full otomatis
✓ Menggunakan WhatsApp Desktop resmi
✓ Agent sepenuhnya dikendalikan sendiri
```

---

# Kekurangan dan Risiko

Metode ini juga memiliki beberapa keterbatasan.

## Komputer Harus Hidup

Jika komputer mati:

```text
WhatsApp Desktop tidak berjalan
↓
Pesan tidak dapat dikirim
```

## WhatsApp Harus Login

Jika WhatsApp logout, pengiriman berhenti.

## Windows Sebaiknya Tidak Sleep

Hindari:

```text
Sleep
Hibernate
Lock screen terlalu lama
```

Untuk komputer pengirim, disarankan:

```text
Power = Always On
Sleep = Never
Internet = Aktif
WhatsApp Desktop = Login
Agent = Running
```

## UI WhatsApp Bisa Berubah

Update WhatsApp Desktop dapat mengubah struktur UI.

Jika control berubah, agent mungkin memerlukan penyesuaian.

## Automation Desktop Lebih Rapuh daripada API Resmi

UI automation bergantung pada:

```text
Windows session
WhatsApp window
UI control
keyboard focus
```

Jadi sistem perlu mekanisme recovery yang baik.

## Pengiriman Banyak Pesan

Jangan mengirim terlalu cepat.

Gunakan delay seperti:

```text
3-10 detik
```

antar pesan dan sesuaikan dengan kebutuhan operasional serta aturan penggunaan WhatsApp.

---

# Teknologi yang Direkomendasikan

## Backend

```text
Laravel 12
PHP 8.2+
MySQL
Laravel Scheduler
Laravel Queue
```

## Desktop Agent

```text
C#
.NET 8
Windows UI Automation
HttpClient
Entity Framework / MySQL Connector jika akses DB langsung
Serilog / logging file
```

## Desktop

```text
Windows 10 / 11
WhatsApp Desktop resmi
```

Arsitektur akhir:

```text
Laravel 12
   +
MySQL
   +
C#/.NET WaDesktopAgent.exe
   +
WhatsApp Desktop
```

---

# Tahapan Implementasi

Implementasi disarankan dilakukan bertahap.

## Tahap 1 — Buat Outbox

Tambahkan:

```text
wa_outbox
```

Pastikan Laravel dapat memasukkan pesan pending.

## Tahap 2 — Ubah WhatsAppService

Hilangkan HTTP request Fonnte.

Ganti menjadi insert ke `wa_outbox`.

## Tahap 3 — Buat Agent Dasar

Agent melakukan:

```text
- koneksi database/API
- ambil 1 pesan pending
- tampilkan nomor dan isi pesan
```

Belum perlu mengontrol WhatsApp.

## Tahap 4 — Integrasi WhatsApp Desktop

Tambahkan:

```text
- deteksi process
- buka WhatsApp
- buka nomor tujuan
- isi pesan
- kirim
```

## Tahap 5 — Update Status

Setelah pengiriman:

```text
sent
```

Jika gagal:

```text
failed
```

## Tahap 6 — Retry

Tambahkan:

```text
attempts
retry delay
max retry
```

## Tahap 7 — Heartbeat Agent

Agent melaporkan:

```text
online
last_seen_at
current_status
```

## Tahap 8 — Dashboard Monitoring

Tampilkan:

```text
Agent Online/Offline
WhatsApp Ready/Not Ready
Pending
Processing
Sent
Failed
```

## Tahap 9 — Autostart

Jalankan agent menggunakan:

```text
Windows Service
```

## Tahap 10 — Hapus Integrasi Lama

Setelah automation stabil:

```text
hapus Fonnte config
hapus kode provider lama
hapus webhook Fonnte jika tidak dipakai
```

---

# Struktur Folder yang Disarankan

## Laravel

```text
pengingat-absen/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── SendWaReminder.php
│   │
│   ├── Jobs/
│   │   └── QueueWhatsAppMessage.php
│   │
│   ├── Models/
│   │   ├── Employee.php
│   │   ├── WaLog.php
│   │   └── WaOutbox.php
│   │
│   └── Services/
│       └── WhatsAppService.php
│
├── database/
│   └── migrations/
│       └── create_wa_outbox_table.php
│
├── routes/
│   ├── web.php
│   └── api.php
│
└── .env
```

## Desktop Agent

```text
wa-desktop-agent/
│
├── WaDesktopAgent.sln
│
└── src/
    └── WaDesktopAgent/
        │
        ├── Program.cs
        ├── Worker.cs
        │
        ├── Services/
        │   ├── LaravelApiService.cs
        │   ├── WhatsAppAutomationService.cs
        │   ├── QueueService.cs
        │   └── HeartbeatService.cs
        │
        ├── Models/
        │   ├── WaMessage.cs
        │   └── AgentStatus.cs
        │
        ├── appsettings.json
        │
        └── Logs/
```

Jika sudah dibuild:

```text
C:\wa-automation\
│
├── WaDesktopAgent.exe
├── appsettings.json
└── Logs\
```

---

# Kesimpulan

Sistem pengingat absensi dapat diubah agar tidak menggunakan Fonnte atau provider WhatsApp pihak ketiga lainnya.

Arsitektur yang direkomendasikan:

```text
                    WEBSITE
                       │
                       ▼
              Laravel Scheduler
                       │
                       ▼
                SendWaReminder
                       │
                       ▼
                  WA_OUTBOX
                       │
                 status pending
                       │
                       ▼
             ┌──────────────────┐
             │ WA DESKTOP AGENT │
             │ C# / .NET        │
             └────────┬─────────┘
                      │
                      ▼
              WHATSAPP DESKTOP
                      │
                      ▼
                   PEGAWAI
                      │
                      ▼
                 status sent
```

Dengan pendekatan ini:

```text
Fonnte                  = Dihapus
WaSender                = Dihapus
Infobip                 = Dihapus
API Gateway WA          = Tidak digunakan

Laravel                 = Tetap
MySQL                   = Tetap
Scheduler               = Tetap
Queue                   = Tetap
Template                = Tetap
Data Pegawai            = Tetap
Log                     = Tetap
WhatsApp Desktop        = Digunakan
C#/.NET Agent           = Ditambahkan
```

Pendekatan ini cocok jika sistem digunakan secara internal dan tersedia satu komputer Windows yang dapat terus menyala, terhubung ke internet, serta WhatsApp Desktop tetap dalam keadaan login.

> Catatan: automation UI WhatsApp Desktop tidak sekuat integrasi API resmi. Karena itu sistem perlu dilengkapi retry, heartbeat, monitoring, logging, dan recovery jika WhatsApp Desktop berubah atau tidak aktif.

---

**Dokumen dibuat untuk rancangan migrasi sistem Pengingat Absen dari WhatsApp API pihak ketiga menuju WhatsApp Desktop Automation lokal.**
