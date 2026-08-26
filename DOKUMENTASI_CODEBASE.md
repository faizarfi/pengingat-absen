# Dokumentasi Codebase - Proyek Utama

## Daftar Isi
1. [Pengingat Absen (WhatsApp Reminder System)](#1-pengingat-absen)
2. [Excel PDF Generator (Progress Report)](#2-excel-pdf-generator)

---

## 1. PENGINGAT ABSEN (WhatsApp Reminder System)

### Gambaran Umum
Aplikasi Laravel untuk mengirim pengingat absen otomatis melalui WhatsApp kepada karyawan. Sistem ini menjadwalkan pengingat masuk, pulang, dan pre-reminder dengan template yang dapat dikustomisasi.

**Stack Teknologi:**
- Backend: Laravel 12.0
- Database: SQLite
- Antrian: Job Queue System
- WhatsApp Provider: Foonte, WaSender, Infobip
- Import/Export: Maatwebsite Excel

**File Konfigurasi:**
```
composer.json          # Dependencies
.env                   # Environment configuration
routes/                # URL routing
app/Models/            # Data models
app/Http/Controllers/  # Business logic
app/Services/          # WhatsApp service
app/Jobs/              # Job queue tasks
```

---

### 1.1 Fungsi Utama - WhatsAppService

**File:** `app/Services/WhatsAppService.php`

#### `sendMessage(string $phone, string $message): array`

**Tujuan:** Mengirim pesan WhatsApp ke nomor telepon tertentu

**Parameter:**
- `$phone` (string): Nomor telepon tujuan (format: dengan/tanpa 0, dengan/tanpa kode negara)
- `$message` (string): Isi pesan yang akan dikirim

**Return:** Array dengan struktur:
```php
[
    'success' => bool,           // Status pengiriman
    'status' => int,            // HTTP status code
    'error' => string|null,     // Jenis error jika gagal
    'retry_after' => int|null,  // Retry delay untuk rate limit (detik)
    'message' => string|null    // Detail error message
]
```

**Alur Kerja:**
1. **Normalisasi Nomor Telepon**
   - Hapus karakter non-numerik
   - Hilangkan leading zero (0) jika ada
   - Contoh: "0812345678" → "812345678"

2. **Validasi Konfigurasi**
   - Ambil URL base dan token dari `config('whatsapp.*')`
   - Return error jika konfigurasi kosong

3. **Deteksi Provider WhatsApp**
   - **Foonte**: Endpoint `/send`, header Authorization: token langsung
   - **WaSender**: Endpoint `/api/send-message`, header Authorization: Bearer token
   - **Infobip**: Endpoint `/whatsapp/1/message/text`, header Authorization: App token
   - Payload format berbeda untuk setiap provider

4. **Pengiriman HTTP POST**
   - Disable SSL verification untuk environment lokal/debug
   - Logging untuk debugging (request & response)

5. **Penanganan Response**
   - **429 (Rate Limit)**: Return error dengan `retry_after`
   - **401 (Auth Error)**: Return error authentication failed
   - **200 (Success)**: Validasi response body provider
   - Logging detail untuk setiap response

**Contoh Penggunaan:**
```php
$whatsappService = new WhatsAppService();
$result = $whatsappService->sendMessage('62812345678', 'Halo, ini pengingat absen');

if ($result['success']) {
    echo "Pesan terkirim!";
} else {
    echo "Error: " . $result['error'];
}
```

**Konfigurasi di `.env`:**
```
WHATSAPP_URL=https://api.fonnte.com
WHATSAPP_KEY=your_token_here
WHATSAPP_FROM=business_account_id
```

---

### 1.2 Fungsi Utama - AdminController

**File:** `app/Http/Controllers/AdminController.php`

#### `index()`

**Tujuan:** Dashboard admin dengan status karyawan dan pengaturan pengingat

**Data yang dikembalikan:**
- `$employees`: Semua data karyawan
- `$employeeStatuses`: Status terakhir pengiriman per karyawan
  - Sudah terkirim (success)
  - Menunggu antrean (pending)
  - Gagal kirim (failed)
- Settings: Waktu check-in/out, template pesan, closing word

**Output View:** `admin.dashboard`

---

#### `storeEmployee(Request $request)`

**Tujuan:** Tambah karyawan baru

**Validasi Input:**
```php
[
    'name' => 'required|string',
    'phone_number' => 'required|string'
]
```

**Aksi:**
- Buat record `Employee` baru dengan `is_active = true`
- Redirect dengan success message

---

#### `updateEmployee(Request $request, $id)`

**Tujuan:** Update data karyawan

**Validasi Input:**
```php
[
    'name' => 'required|string',
    'phone_number' => 'required|string',
    'is_active' => 'boolean'
]
```

**Aksi:**
- Update record karyawan berdasarkan ID
- Redirect dengan success message

---

#### `deleteEmployee($id)`

**Tujuan:** Hapus karyawan

**Aksi:**
- Soft delete atau hard delete record `Employee`
- Redirect dengan success message

---

#### `importEmployees(Request $request)`

**Tujuan:** Import daftar karyawan dari file Excel/CSV

**Validasi Input:**
```php
[
    'employee_file' => 'required|file|mimes:csv,xlsx,xls,ods|max:5120'
]
```

**Alur Kerja:**
1. Validasi file format dan ukuran (max 5MB)
2. Parse Excel ke array menggunakan Maatwebsite Excel
3. Loop setiap row:
   - Deteksi header row (skip jika ada kata "name"/"nama" dan "phone"/"telepon")
   - Normalisasi nomor telepon
   - Create record Employee baru jika data valid
   - Skip jika ada cell kosong
4. Return summary: X berhasil, Y dilewati

**Format File yang Didukung:**
```
CSV, XLSX, XLS, ODS
Kolom: [Nama, Nomor Telepon]
```

---

#### `exportEmployees()`

**Tujuan:** Export daftar karyawan ke CSV

**Output:**
- CSV file dengan nama: `employees-YYYYMMDD-HHmmss.csv`
- Kolom: No | Nama Pegawai | Nomor Telepon / WA
- UTF-8 BOM untuk kompatibilitas Excel
- Normalisasi display nomor telepon

---

#### `updateSetting(Request $request)`

**Tujuan:** Update pengaturan sistem pengingat

**Validasi Input:**
```php
[
    'check_in_time' => 'required|date_format:H:i',
    'check_out_time' => 'required|date_format:H:i',
    'pre_reminder_minutes' => 'nullable|integer|min:1|max:120',
    'template_checkin' => 'string (optional)',
    'template_checkout' => 'string (optional)',
    'template_pre_checkin' => 'string (optional)',
    'closing_word' => 'string (optional)'
]
```

**Setting yang Disimpan:**
- `check_in_time`: Waktu absen masuk (e.g., "07:30")
- `check_out_time`: Waktu absen pulang (e.g., "16:00")
- `pre_reminder_minutes`: Menit sebelum jadwal untuk kirim pre-reminder
- `template_checkin`: Template pesan absen masuk
- `template_checkout`: Template pesan absen pulang
- `template_pre_checkin`: Template pre-reminder masuk
- `closing_word`: Kata penutup pesan (e.g., "Semangat kerja!")

**Template Placeholders:**
- `{name}`: Nama karyawan
- `{kata}`: Closing word
- `{minutes_left}`: Sisa menit hingga jam target
- `{target_time}`: Waktu target (HH:mm format)
- `{organization}`: Nama organisasi

---

#### `sendNow(Request $request)`

**Tujuan:** Kirim pesan pengingat absen secara manual dan langsung (synchronous)

**Alur Kerja:**
1. Ambil semua karyawan aktif
2. Untuk setiap karyawan:
   - Pilih random template dari sample templates
   - Replace placeholders dengan data karyawan
   - Insert log ke tabel `wa_logs` dengan status "pending"
   - Dispatch `SendWhatsAppJob` secara synchronous
   - Increment counter jika berhasil
3. Return summary: X karyawan dipilih

**Job yang Didispatch:** `SendWhatsAppJob::dispatchSync()`

---

#### `sendPreCheckinNow(Request $request)`

**Tujuan:** Kirim pre-reminder absen masuk (N menit sebelum jam check-in)

**Alur Kerja:**
1. Hitung sisa menit hingga jam masuk (`check_in_time`)
2. Untuk setiap karyawan:
   - Replace template pre-checkin placeholders
   - Jika template tidak punya `{minutes_left}`, append fallback sentence
   - Ambil random pantun dari tabel `pantuns` dengan type='masuk'
   - Insert ke pantun di tengah pesan (setelah salutation jika ada)
   - Insert log ke `wa_logs`
   - Dispatch `SendWhatsAppJob` secara synchronous
3. Return summary dengan sisa menit global

**Pantun:** Puisi tradisional Indonesia yang diappend ke pesan

---

#### `sendPreCheckoutNow(Request $request)`

**Tujuan:** Kirim pre-reminder absen pulang (N menit sebelum jam check-out)

**Alur Kerja:** Sama dengan `sendPreCheckinNow()` tapi untuk jam pulang dan type='pulang'

---

#### `setDefaultTimes(Request $request)`

**Tujuan:** Reset waktu ke default

**Default Values:**
- Check-in: 07:30
- Check-out: 16:00
- Pre-reminder: 30 menit

---

### 1.3 Models & Database

#### Model: `Employee`
```
Columns:
- id (primary key)
- name (string)
- phone_number (string) - stored tanpa leading 0
- is_active (boolean) - default true
- created_at, updated_at
```

#### Model: `Setting`
Key-value store untuk pengaturan sistem
```
Columns:
- id (primary key)
- key (string, unique)
- value (text)
- created_at, updated_at
```

#### Model: `WaLog`
Log pengiriman WhatsApp untuk audit trail
```
Columns:
- id (primary key)
- employee_id (foreign key)
- type (enum: 'manual', 'checkin', 'checkout', 'pre_checkin', 'pre_checkout')
- status (enum: 'pending', 'sent', 'failed')
- scheduled_at (datetime)
- sent_at (datetime, nullable)
- error_message (text, nullable)
- created_at, updated_at
```

---

### 1.4 Job Queue System

#### `SendWhatsAppJob`

**Tujuan:** Job untuk mengirim pesan WhatsApp asynchronously atau synchronously

**Data yang diterima:**
- `$logId`: ID dari wa_logs record
- `$employeeId`: ID karyawan
- `$phone`: Nomor telepon
- `$message`: Isi pesan
- `$type`: Tipe pengingat

**Alur Kerja:**
1. Load WaLog record berdasarkan ID
2. Call `WhatsAppService::sendMessage()`
3. Update WaLog:
   - Jika sukses: set status='sent', sent_at=now()
   - Jika gagal: set status='failed', error_message=response error

---

### 1.5 Console Commands

#### `SendWaReminder` Command

**Tujuan:** Scheduled task untuk kirim reminder absen otomatis

**Execution:** Dijadwalkan via `app/Console/Kernel.php`

**Alur Kerja:**
1. Ambil `check_in_time` dan `check_out_time` dari settings
2. Jika waktu sekarang = jam check-in:
   - Loop karyawan aktif
   - Dispatch `SendWhatsAppJob` untuk tipe 'checkin'
3. Jika waktu sekarang = jam check-out:
   - Loop karyawan aktif
   - Dispatch `SendWhatsAppJob` untuk tipe 'checkout'
4. Jika ada `pre_reminder_minutes`, handle pre-reminder tasks

---

---

## 2. EXCEL PDF GENERATOR (Progress Report)

### Gambaran Umum
Aplikasi Laravel untuk mengkonversi file Excel progress report dan PPK menjadi laporan terpadu dalam format PDF. Sistem ini merge data dari 2 file Excel dan generate laporan professional.

**Stack Teknologi:**
- Backend: Laravel 12.0
- PDF Generation: Barryvdh DomPDF
- Excel Parsing: Maatwebsite Excel
- Database: SQLite

**File Konfigurasi:**
```
composer.json                          # Dependencies
app/Http/Controllers/ProgressReportController.php  # Main logic
resources/views/pdf_template.blade.php # PDF template
```

---

### 2.1 Fungsi Utama - ProgressReportController

**File:** `app/Http/Controllers/ProgressReportController.php`

#### `index()`

**Tujuan:** Render halaman form upload file

**Output View:** `index`

---

#### `process(Request $request)`

**Tujuan:** Process 2 file Excel dan generate PDF laporan gabungan

**Validasi Input:**
```php
[
    'file_progress' => 'required|mimes:xlsx,xls',  // File progress report
    'file_ppk' => 'required|mimes:xlsx,xls'        // File PPK report
]
```

**Alur Kerja:**
1. Parse kedua file Excel ke array menggunakan Maatwebsite Excel
2. Call `buildMergedReport()` untuk merge data
3. Jika action = 'pdf':
   - Load view `pdf_template` dengan data merged
   - Set paper size: A4 landscape
   - Download PDF dengan nama: `Beban_Kerja_Petugas_SE2026.pdf`
4. Redirect back dengan success message

**Request Parameter:**
- `$request->action`: 'pdf' atau action lain untuk future expansion
- `$request->file('file_progress')`: Upload file progress report
- `$request->file('file_ppk')`: Upload file PPK report

---

#### `buildMergedReport(array $progressRows, array $ppkRows): array`

**Tujuan:** Merge data dari 2 Excel file dan return structured report

**Input:**
- `$progressRows`: Array dari file progress report (sudah di-parse)
- `$ppkRows`: Array dari file PPK report (sudah di-parse)

**Alur Kerja:**

1. **Build PPK Index**
   - Call `buildPpkIndex($ppkRows)` untuk create lookup table
   - Struktur: `['kodeSls-kodeDesa-kodeKec' => ['target' => X, 'realisasi' => Y]]`

2. **Loop Progress Rows**
   - Skip header row (index 0) dan empty rows
   - Untuk setiap row:
     
     a) **Deteksi Tipe Petugas (PML atau PPL)**
        - Call `detectPetugasType()` 
        - Search 'PML' atau 'PPL' dalam row text
        - Default: PML

     b) **Extract Data Petugas**
        ```php
        pml_nama = getRowValue($row, [1, 0, 2])  // Try index 1, fallback 0, fallback 2
        pml_sobat = getRowValue($row, [2, 3, 1]) // Partner PML
        ppl_nama = getRowValue($row, [3, 4, 1])  // PPL nama
        ppl_sobat = getRowValue($row, [4, 5, 2]) // Partner PPL
        ```
        
        - Jika PML: set `ppl_nama` dan `ppl_sobat` kosong
        - Jika PPL: set `pml_nama` dan `pml_sobat` kosong

     c) **Extract Geografi & Target**
        ```php
        kodeKec = column[5]
        kodeDesa = column[6]
        kodeSls = column[7]
        target = normalizeNumber(column[8])
        realisasi = normalizeNumber(column[9])
        keterangan = column[11]
        ```

     d) **Override dengan Data PPK**
        ```
        Jika key (kodeSls-kodeDesa-kodeKec) ada di PPK index:
          - gunakan target dari PPK
          - gunakan realisasi dari PPK
        ```

     e) **Build Output Row**
        ```php
        [
            'pml_nama' => string,
            'pml_sobat' => string,
            'ppl_nama' => string,
            'ppl_sobat' => string,
            'kode_kec' => string,
            'kode_desa' => string,
            'kode_sls' => string,
            'target' => int,
            'realisasi' => int,
            'keterangan' => string
        ]
        ```

3. **Return Merged Data**
   - Array of rows siap untuk PDF template

**Output:** Array of structured rows dengan format terbaku

---

#### `buildPpkIndex(array $rows): array`

**Tujuan:** Create lookup index dari PPK data untuk quick access

**Alur Kerja:**
1. Loop PPK rows
2. Skip header dan empty rows
3. Extract: kodeKec, kodeDesa, kodeSls, target, realisasi
4. Create key: `"kodeSls-kodeDesa-kodeKec"`
5. Store di array dengan key sebagai index

**Return Format:**
```php
[
    'kodeSls-kodeDesa-kodeKec' => [
        'target' => 100,
        'realisasi' => 85
    ],
    // ...
]
```

**Manfaat:** O(1) lookup time saat merge progress data

---

#### `detectPetugasType(array $row): string`

**Tujuan:** Deteksi apakah row adalah data PML atau PPL

**Alur Kerja:**
1. Gabungkan semua cell dalam row jadi string uppercase
2. Search untuk substring 'PML' → return 'PML'
3. Search untuk substring 'PPL' → return 'PPL'
4. Default: return 'PML'

**Return:** 'PML' atau 'PPL'

---

#### `getRowValue(array $row, array $indexes): string`

**Tujuan:** Get value dari multiple fallback indexes (fallback extraction)

**Alur Kerja:**
1. Loop setiap index dalam `$indexes` array
2. Jika cell ada dan tidak kosong → return value (string cast)
3. Jika semua index empty → return empty string

**Contoh:**
```php
$nama = getRowValue($row, [1, 0, 2]);
// Try index 1, jika kosong try index 0, jika kosong try index 2
```

**Manfaat:** Handle variasi column position dalam file Excel

---

#### `resolveRowKey(string $kodeSls, string $kodeDesa, string $kodeKec): ?string`

**Tujuan:** Create composite key untuk lookup PPK data

**Alur Kerja:**
1. Filter 3 parameters, remove empty strings
2. Join dengan '-' delimiter
3. Return null jika semua empty

**Output Format:** `"1234-5678-12"` atau null

**Used in:** Lookup ke PPK index

---

#### `normalizeNumber(mixed $value): int`

**Tujuan:** Convert berbagai format number ke integer

**Alur Kerja:**
1. Jika null atau empty string → return 0
2. Jika numeric → cast to int
3. Jika string:
   - Remove non-numeric chars (keep . dan - untuk float/negative)
   - Remove comma separator
   - Cast to int

**Contoh:**
```
"1.234"      → 1234
"1,234"      → 1234
"1234.56"    → 1234
"1,234.00"   → 1234
"N/A"        → 0
```

---

#### `safeCell(array $row, int $index): string`

**Tujuan:** Safe access cell value dengan fallback empty string

**Alur Kerja:**
```php
return (string) ($row[$index] ?? '')
```

**Manfaat:** Prevent undefined index error

---

#### `isEmptyRow(array $row): bool`

**Tujuan:** Check apakah row tidak punya data (all cells empty)

**Alur Kerja:**
1. Loop setiap cell
2. Jika ada cell yang tidak null dan tidak whitespace → return false
3. Jika semua cell empty → return true

**Used in:** Skip empty rows during processing

---

### 2.2 View & Template

#### `resources/views/pdf_template.blade.php`

**Input Variables:**
- `$items`: Array of merged rows (dari `buildMergedReport()`)

**Struktur PDF:**
```
Header:
- Judul: "BEBAN KERJA PETUGAS SE 2026"
- Info: Tanggal, Organisasi, etc

Table:
| No | Kode | Petugas (PML/PPL) | Sobat | Target | Realisasi | Keterangan |

Footer:
- Nomor halaman
- Tanda tangan
```

**Layout:** Landscape A4 untuk accommodate banyak kolom

---

### 2.3 Data Flow & File Format

#### File Progress Report Format (Excel)
```
Column: [0]   [1]       [2]     [3]     [4]     [5]       [6]       [7]     [8]     [9]       [10] [11]
        [No]  [PML]     [Sobat] [PPL]   [Sobat] [KodeKec] [KodeDesa] [KodeSLS] [Target] [Realisasi] [ID] [Keterangan]

Row 0 (Header): Dilewati
Row 1+: Data
```

#### File PPK Report Format (Excel)
```
Column: [0]   [1]   [2]     [3]   [4]   [5]       [6]       [7]       [8]     [9]       ...
        [No]  [ID]  [Name]  [PML] [PPL] [KodeKec] [KodeDesa] [KodeSLS] [Target] [Realisasi] ...

Row 0 (Header): Dilewati
Row 1+: Data
```

#### Merge Logic
```
Progress File + PPK File → Merged Report (PDF)

Key untuk match: KodeSLS-KodeDesa-KodeKec

Jika key ada di PPK:
  - Gunakan Target & Realisasi dari PPK (lebih akurat)
  Else:
  - Gunakan dari Progress File
```

---

### 2.4 Error Handling

**Validation Errors:**
- File tidak ada → return error
- File format salah → return error
- File terlalu besar → return error

**Processing Errors:**
- Empty file → skip dengan warning
- Invalid Excel format → return error message
- Exception during merge → catch & return error

**User Feedback:**
- Success: "Data berhasil diproses" + PDF download
- Error: Redirect back dengan error message

---

---

## 3. DATABASE SCHEMA SUMMARY

### pengingat-absen
```sql
-- Karyawan
CREATE TABLE employees (
  id INT PRIMARY KEY,
  name VARCHAR(255),
  phone_number VARCHAR(20),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Pengaturan Sistem
CREATE TABLE settings (
  id INT PRIMARY KEY,
  key VARCHAR(255) UNIQUE,
  value TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Log Pengiriman WhatsApp
CREATE TABLE wa_logs (
  id INT PRIMARY KEY,
  employee_id INT,
  type VARCHAR(50), -- manual, checkin, checkout, pre_checkin, pre_checkout
  status VARCHAR(50), -- pending, sent, failed
  scheduled_at TIMESTAMP,
  sent_at TIMESTAMP NULL,
  error_message TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Pantun (Puisi untuk Message)
CREATE TABLE pantuns (
  id INT PRIMARY KEY,
  type VARCHAR(50), -- masuk, pulang
  text LONGTEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## 4. DEPENDENCIES & REQUIREMENTS

### pengingat-absen
```
PHP: ^8.2
Laravel: ^12.0
Maatwebsite Excel: ^3.1 (Excel import/export)
Box Spout: ^3.3 (Excel engine)
Laravel Tinker: ^2.10.1 (REPL)

Dev:
- PHPUnit: ^11.5.50
- Laravel Pint: ^1.24 (Code formatter)
- Laravel Sail: ^1.41 (Docker)
- Mockery: ^1.6 (Testing)
```

### excel-pdf-generator
```
PHP: ^8.2
Laravel: ^12.0
Barryvdh DomPDF: ^3.1 (PDF generation)
Maatwebsite Excel: ^3.1 (Excel parsing)
Laravel Tinker: ^2.10.1

Dev:
- PHPUnit: ^11.5.50
- Laravel Pint: ^1.24
- Laravel Sail: ^1.41
- Mockery: ^1.6
```

---

## 5. SETUP & RUNNING INSTRUCTIONS

### pengingat-absen

**Setup:**
```bash
cd ~/pengingat-absen
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install && npm run build
```

**Development:**
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker (untuk async jobs)
php artisan queue:listen --tries=1 --timeout=0

# Terminal 3: Frontend dev
npm run dev
```

**Or use composer dev script:**
```bash
composer run dev
```

**Testing:**
```bash
php artisan test
# atau
./vendor/bin/phpunit
```

---

### excel-pdf-generator

**Setup:**
```bash
cd ~/excel-pdf-generator
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install && npm run build
```

**Development:**
```bash
php artisan serve
```

**Testing:**
```bash
php artisan test
```

---

## 6. CONFIGURATION FILES

### pengingat-absen `.env` Example
```env
APP_NAME=PengingatAbsen
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

WHATSAPP_URL=https://api.fonnte.com
WHATSAPP_KEY=your_api_key_here
WHATSAPP_FROM=business_account

QUEUE_CONNECTION=database
```

### excel-pdf-generator `.env` Example
```env
APP_NAME=ExcelPDFGenerator
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

---

## 7. KEY FEATURES & WORKFLOWS

### pengingat-absen

**Feature 1: Automatic Scheduled Reminders**
- Jam check-in/out → auto send ke semua karyawan aktif
- Configurable waktu & template
- Pre-reminder N menit sebelumnya

**Feature 2: Manual Trigger**
- Admin bisa kirim reminder kapan saja via dashboard
- Kirim immediately atau queue untuk later

**Feature 3: Employee Management**
- CRUD operations via UI
- Bulk import dari Excel/CSV
- Export ke CSV

**Feature 4: Customizable Templates**
- Template check-in, check-out, pre-reminder
- Support placeholders: {name}, {kata}, {minutes_left}, etc
- Random pantun append untuk pre-reminders

**Feature 5: Audit Trail**
- Setiap pengiriman di-log ke wa_logs
- Track status: pending, sent, failed
- Error messages untuk debugging

---

### excel-pdf-generator

**Feature 1: Dual File Processing**
- Upload 2 file Excel: Progress Report + PPK Report
- Auto parse dan merge berdasarkan geographic keys

**Feature 2: Data Validation & Normalization**
- Handle multiple column position variants
- Normalize phone numbers, amounts, etc
- Skip invalid/empty rows

**Feature 3: Intelligent Data Override**
- PPK data override Progress data jika ada match
- Fallback to original jika tidak ada match
- Flexible key matching strategy

**Feature 4: PDF Generation**
- Professional landscape A4 layout
- Formatted table dengan computed fields
- Ready untuk print/archive

---

## 8. COMMON TROUBLESHOOTING

### pengingat-absen

**Issue: WhatsApp message tidak terkirim**
- Cek `.env` WHATSAPP_URL dan WHATSAPP_KEY
- Check logs: `storage/logs/laravel.log`
- Verify nomor telepon format (8xxxxxxx)
- Check provider rate limit (429 error)

**Issue: Queue jobs tidak di-execute**
- Ensure queue worker running: `php artisan queue:listen`
- Check `QUEUE_CONNECTION` di .env (gunakan 'database')
- Verify database connection

**Issue: Import Excel gagal**
- Check file format: CSV, XLSX, XLS, ODS only
- Max file size: 5MB
- Verify column structure: [Nama, Phone]

---

### excel-pdf-generator

**Issue: PDF blank/corrupted**
- Check PDF template view: `resources/views/pdf_template.blade.php`
- Verify data merge: check `buildMergedReport()` output
- Test dengan sample data dulu

**Issue: Merge logic tidak cocok**
- Verify Excel column positions
- Check geographic code format (kodeSls-kodeDesa-kodeKec)
- Debug: add dd($ppkIndex) untuk inspect

---

## 9. DEVELOPMENT NOTES

### Best Practices

1. **WhatsApp Integration**
   - Always normalize phone numbers consistently
   - Log semua request/response untuk audit
   - Implement retry logic untuk rate limits
   - Support multiple providers untuk flexibility

2. **File Processing**
   - Use fallback indexes untuk handle Excel variations
   - Always validate & normalize data
   - Skip invalid rows gracefully
   - Provide detailed error messages

3. **Error Handling**
   - Try-catch untuk external API calls
   - Logging untuk debugging
   - User-friendly error messages
   - Graceful degradation

4. **Performance**
   - Use database indexes untuk frequently queried columns
   - Batch operations saat possible (import, export)
   - Queue long-running tasks (job dispatch)

---

## 10. FUTURE IMPROVEMENTS

### pengingat-absen
- [ ] WhatsApp Group messaging support
- [ ] Message delivery tracking with read receipts
- [ ] Multiple reminder templates based on day/occasion
- [ ] Employee scheduling (shift-based reminders)
- [ ] SMS fallback jika WhatsApp gagal

### excel-pdf-generator
- [ ] Support lebih banyak Excel provider/format
- [ ] Real-time preview sebelum generate PDF
- [ ] Excel diff tracking (changes highlight)
- [ ] Batch processing untuk multiple file pairs
- [ ] Export hasil merge ke Excel juga

---

**Dokumentasi ini dibuat: 18 Agustus 2026**
**Last Updated: 2026-08-18 06:04:47 UTC**
