# API DOCUMENTATION & ARCHITECTURE

## 1. PENGINGAT ABSEN - Route Map & Endpoints

### Web Routes (resources/routes/web.php)

```
GET    /                          → AdminController@index        [Home/Dashboard]
POST   /employees                 → AdminController@storeEmployee
PATCH  /employees/{id}            → AdminController@updateEmployee
DELETE /employees/{id}            → AdminController@deleteEmployee
POST   /employees/import          → AdminController@importEmployees
GET    /employees/export          → AdminController@exportEmployees
POST   /settings                  → AdminController@updateSetting
POST   /send-now                  → AdminController@sendNow
POST   /send-pre-checkin          → AdminController@sendPreCheckinNow
POST   /send-pre-checkout         → AdminController@sendPreCheckoutNow
POST   /set-default-times         → AdminController@setDefaultTimes
```

---

### 1.1 Employee Management Endpoints

#### POST /employees
**Add New Employee**

```
Request Body:
{
  "name": "Budi Santoso",
  "phone_number": "62812345678"
}

Response (201):
{
  "status": "success",
  "message": "Karyawan baru berhasil ditambahkan."
}

Response (422):
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "phone_number": ["The phone_number field is required."]
  }
}
```

---

#### PATCH /employees/{id}
**Update Employee**

```
URL Parameters:
  id: Employee ID (integer)

Request Body:
{
  "name": "Budi Santoso Updated",
  "phone_number": "62812345679",
  "is_active": true
}

Response (200):
{
  "status": "success",
  "message": "Karyawan berhasil diperbarui."
}

Response (404):
{
  "message": "Employee not found"
}
```

---

#### DELETE /employees/{id}
**Delete Employee**

```
URL Parameters:
  id: Employee ID (integer)

Response (200):
{
  "status": "success",
  "message": "Karyawan berhasil dihapus."
}

Response (404):
{
  "message": "Employee not found"
}
```

---

#### POST /employees/import
**Bulk Import Employees from Excel/CSV**

```
Request (multipart/form-data):
  file: employee_file (binary, max 5MB)
        Supported formats: csv, xlsx, xls, ods
        Expected columns: [Name, Phone Number]

Response (200):
{
  "status": "success",
  "message": "Import selesai: 45 baris berhasil, 2 baris dilewati."
}

Response (422):
{
  "message": "Validation failed",
  "errors": {
    "employee_file": ["The file must be a file of type: csv, xlsx, xls, ods."]
  }
}

Response (400):
{
  "error": "File kosong atau tidak dapat dibaca."
}
```

**Expected File Format:**
```
Row 0 (Header): Nama Pegawai | Nomor Telepon
Row 1: Budi Santoso | 0812345678
Row 2: Siti Nurhaliza | 0813456789
...
```

---

#### GET /employees/export
**Export All Employees to CSV**

```
Response (200):
  Content-Type: text/csv; charset=utf-8
  Content-Disposition: attachment; filename="employees-20260818-123456.csv"
  
  Body:
  ﻿No,Nama Pegawai,Nomor Telepon / WA
  1,Budi Santoso,0812345678
  2,Siti Nurhaliza,0813456789
  ...
```

---

### 1.2 Settings Management Endpoints

#### POST /settings
**Update System Settings**

```
Request Body:
{
  "check_in_time": "07:30",
  "check_out_time": "16:00",
  "pre_reminder_minutes": 30,
  "template_checkin": "Halo {name}, sudah waktunya absen pagi...",
  "template_checkout": "Halo {name}, sudah waktunya absen pulang...",
  "template_pre_checkin": "Halo {name}, 30 menit lagi waktunya...",
  "closing_word": "Semangat kerja!"
}

Response (200):
{
  "status": "success",
  "message": "Pengaturan berhasil disimpan."
}

Response (422):
{
  "message": "Validation failed",
  "errors": {
    "check_in_time": ["The check in time field must match the format H:i."],
    "pre_reminder_minutes": ["The pre reminder minutes must be between 1 and 120."]
  }
}
```

**Setting Keys:**
```
check_in_time              → String, format: "HH:mm" (e.g., "07:30")
check_out_time             → String, format: "HH:mm" (e.g., "16:00")
pre_reminder_minutes       → Integer, range: 1-120
template_checkin           → Text, supports placeholders
template_checkout          → Text, supports placeholders
template_pre_checkin       → Text, supports placeholders
closing_word               → String, appended to messages
organization_name          → String, default: "BPS Kabupaten Karanganyar"
```

---

### 1.3 Message Sending Endpoints

#### POST /send-now
**Send Immediate Reminder to All Active Employees**

```
Request Body: (empty)
{}

Response (200):
{
  "status": "success",
  "message": "Pengiriman pesan dikirimkan ke 42/45 karyawan secara langsung."
}
```

**Behind the scenes:**
- Query: SELECT * FROM employees WHERE is_active = TRUE
- For each employee:
  - Pick random template from samples
  - Replace placeholders
  - Create wa_logs record (status: pending)
  - Dispatch SendWhatsAppJob (synchronous)
  - Update wa_logs (status: sent or failed)
- Return count of successfully sent messages

---

#### POST /send-pre-checkin
**Send Pre-Reminder Before Check-In Time**

```
Request Body: (empty)
{}

Response (200):
{
  "status": "success",
  "message": "Pengingat masuk dikirim ke 45/45 karyawan. Sisa menit: 28"
}
```

**Calculation:**
```
Current time: 07:02:00
Check-in time: 07:30:00
Minutes left: (07:30 - 07:02) × 60 = 28 menit

For each employee, message includes:
- Replacement of {minutes_left} placeholder
- Append random pantun (type: 'masuk')
- Professional formatting with salutation & closing
```

---

#### POST /send-pre-checkout
**Send Pre-Reminder Before Check-Out Time**

```
Request Body: (empty)
{}

Response (200):
{
  "status": "success",
  "message": "Pengingat pulang dikirim ke 45/45 karyawan. Sisa menit: 356"
}
```

---

#### POST /set-default-times
**Reset to Default Times**

```
Request Body: (empty)
{}

Response (200):
{
  "status": "success",
  "message": "Waktu default disimpan: Masuk 07:30, Pulang 16:00."
}

Defaults:
  check_in_time: "07:30"
  check_out_time: "16:00"
  pre_reminder_minutes: 30
```

---

### 1.4 Dashboard Data Endpoints

#### GET /
**Dashboard with Status Overview**

```
Response (200):
View: admin.dashboard

Data Passed:
{
  "employees": Collection[
    {
      "id": 1,
      "name": "Budi Santoso",
      "phone_number": "812345678",
      "is_active": true,
      "created_at": "2026-08-01 10:30:00",
      "updated_at": "2026-08-01 10:30:00"
    }
  ],
  
  "employeeStatuses": {
    "1": {
      "label": "Sudah terkirim",
      "variant": "success",
      "detail": "10:30:45"
    },
    "2": {
      "label": "Menunggu antrean",
      "variant": "warning",
      "detail": "120 detik"
    },
    "3": {
      "label": "Gagal kirim",
      "variant": "danger",
      "detail": "10:25:30"
    }
  },
  
  "checkIn": "07:30",
  "checkOut": "16:00",
  "preReminderMinutes": 30,
  "templateIn": "Halo {name}, sudah waktunya absen pagi...",
  "templateOut": "Halo {name}, sudah waktunya absen pulang...",
  "templatePreCheckin": "Halo {name}, 30 menit lagi...",
  "kata": "Semangat kerja!"
}
```

---

## 2. EXCEL PDF GENERATOR - Route Map & Endpoints

### Web Routes

```
GET    /                   → ProgressReportController@index    [Upload Form]
POST   /process            → ProgressReportController@process   [Generate PDF]
```

---

### 2.1 Upload & Generate Endpoints

#### GET /
**Show Upload Form**

```
Response (200):
View: index

Form Fields:
  - file_progress: File input (xlsx/xls only)
  - file_ppk: File input (xlsx/xls only)
  - action: Select (pdf, preview, etc)
  - Submit button
```

---

#### POST /process
**Process Files & Generate PDF**

```
Request (multipart/form-data):
  file_progress: Binary file (required, xlsx/xls, max size from config)
  file_ppk: Binary file (required, xlsx/xls, max size from config)
  action: String (default: "pdf")

Response (200 - PDF Download):
  Content-Type: application/pdf
  Content-Disposition: attachment; filename="Beban_Kerja_Petugas_SE2026.pdf"
  Body: PDF binary

Response (422 - Validation Error):
{
  "message": "Validation failed",
  "errors": {
    "file_progress": ["The file_progress field is required."],
    "file_ppk": ["The file_ppk must be a file of type: xlsx, xls."]
  }
}

Response (500 - Processing Error):
{
  "error": "Terjadi kesalahan saat memproses file: Invalid Excel format"
}
```

---

### 2.2 Data Processing API (Internal)

#### buildMergedReport(array $progressRows, array $ppkRows): array

**Purpose:** Merge progress and PPK data into single report

```
Input:
  $progressRows: [
    [0: "No", 1: "Nama PML", 2: "Sobat", 3: "Nama PPL", 4: "Sobat", 
     5: "KodeKec", 6: "KodeDesa", 7: "KodeSLS", 8: "Target", 9: "Realisasi", 10: "ID", 11: "Keterangan"],
    [0: "1", 1: "Budi", 2: "Ahmad", 3: "", 4: "", 
     5: "12", 6: "01", 7: "0001", 8: "100", 9: "85", 10: "001", 11: "OK"]
  ]
  
  $ppkRows: [
    [0: "No", 1: "ID", 2: "Name", 3: "PML", 4: "PPL", 
     5: "KodeKec", 6: "KodeDesa", 7: "KodeSLS", 8: "Target", 9: "Realisasi", ...],
    [0: "1", 1: "ID001", 2: "Budi", 3: "Budi", 4: "", 
     5: "12", 6: "01", 7: "0001", 8: "105", 9: "90", ...]
  ]

Output:
  [
    {
      "pml_nama": "Budi",
      "pml_sobat": "Ahmad",
      "ppl_nama": "",
      "ppl_sobat": "",
      "kode_kec": "12",
      "kode_desa": "01",
      "kode_sls": "0001",
      "target": 105,           ← From PPK (overrides progress)
      "realisasi": 90,         ← From PPK (overrides progress)
      "keterangan": "OK"
    }
  ]
```

---

#### buildPpkIndex(array $rows): array

**Purpose:** Create lookup index from PPK data

```
Input:
  $rows: PPK rows array

Output:
  [
    "0001-01-12": {
      "target": 105,
      "realisasi": 90
    },
    "0002-01-12": {
      "target": 110,
      "realisasi": 95
    }
  ]

Key Format: "kodeSls-kodeDesa-kodeKec"
```

---

#### detectPetugasType(array $row): string

**Purpose:** Detect if row is PML or PPL

```
Input:
  $row: ["...", "...", "PML", "...", ...]

Output:
  "PML" | "PPL" (default: "PML")

Logic:
  - Search for "PML" in row → return "PML"
  - Search for "PPL" in row → return "PPL"
  - Else → return "PML"
```

---

#### getRowValue(array $row, array $indexes): string

**Purpose:** Get value with fallback column indexes

```
Input:
  $row: [..., "Budi", null, "Ahmad", ...]
  $indexes: [1, 0, 2]

Output:
  "Budi" (from index 1)

Logic:
  - Try $row[1]: found "Budi" (non-empty) → return "Budi"
  - (would have tried $row[0], $row[2] if index 1 was empty)
```

---

#### normalizeNumber(mixed $value): int

**Purpose:** Convert various number formats to int

```
Inputs → Outputs:
  "1.234"        → 1234
  "1,234"        → 1234
  "1,234.56"     → 1234
  1234           → 1234
  "N/A"          → 0
  null           → 0
  ""             → 0
  12.5           → 12
```

---

#### resolveRowKey(string $kodeSls, string $kodeDesa, string $kodeKec): ?string

**Purpose:** Create composite key for PPK lookup

```
Inputs:
  $kodeSls: "0001"
  $kodeDesa: "01"
  $kodeKec: "12"

Output:
  "0001-01-12"

Edge cases:
  - Empty strings → null
  - Only some empty → join non-empty: "0001-01" or "01-12"
```

---

## 3. ARCHITECTURE DIAGRAMS

### System Architecture - Pengingat Absen

```
┌─────────────────────────────────────────────────────────────┐
│                         USER BROWSER                         │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Admin Dashboard (Blade Template)                        ││
│  │  - Employee List (CRUD)                                  ││
│  │  - Send Buttons (Now, Pre-Checkin, Pre-Checkout)        ││
│  │  - Settings Form (Times, Templates)                      ││
│  │  - Status Display (Sent, Pending, Failed)               ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                             ↕ HTTP
┌─────────────────────────────────────────────────────────────┐
│                    LARAVEL WEB SERVER                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ AdminController (Request Handler)                    │   │
│  │ - index(), storeEmployee(), sendNow(), etc          │   │
│  └──────────────────────────────────────────────────────┘   │
│                             ↕                                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ WhatsAppService (API Abstraction)                   │   │
│  │ - sendMessage(): Phone + Message → Provider API     │   │
│  │ - Supports: Foonte, WaSender, Infobip              │   │
│  └──────────────────────────────────────────────────────┘   │
│                             ↕                                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Models (Eloquent ORM)                               │   │
│  │ - Employee, Setting, WaLog, Pantun                  │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
    ↕                                ↕
┌──────────────────────┐   ┌──────────────────────┐
│  JOB QUEUE SYSTEM    │   │    SQLite DATABASE   │
│  (Queue Worker)      │   │  ┌─────────────────┐ │
│ ┌──────────────────┐ │   │  │ employees       │ │
│ │SendWhatsAppJob   │ │   │  │ settings        │ │
│ │- Execute: send   │ │   │  │ wa_logs (audit) │ │
│ │- Update status   │ │   │  │ pantuns         │ │
│ └──────────────────┘ │   │  └─────────────────┘ │
└──────────────────────┘   └──────────────────────┘
    ↕
┌──────────────────────┐
│ WHATSAPP PROVIDERS   │
│ ┌──────────────────┐ │
│ │ Foonte API       │ │
│ │ WaSender API     │ │
│ │ Infobip API      │ │
│ └──────────────────┘ │
└──────────────────────┘
    ↕
┌──────────────────────┐
│ WHATSAPP MESSAGES    │
│ Employee Phones      │
└──────────────────────┘
```

---

### System Architecture - Excel PDF Generator

```
┌─────────────────────────────────────────────────────────────┐
│                         USER BROWSER                         │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Upload Form (Blade Template)                            ││
│  │  - Select file_progress (Excel)                          ││
│  │  - Select file_ppk (Excel)                               ││
│  │  - Submit for PDF generation                             ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                             ↕ HTTP (multipart/form-data)
┌─────────────────────────────────────────────────────────────┐
│              LARAVEL WEB SERVER                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ ProgressReportController::process()                 │   │
│  │ 1. Validate files (xlsx/xls)                        │   │
│  │ 2. Parse Excel → Excel::toArray()                   │   │
│  │ 3. Call buildMergedReport($progress, $ppk)          │   │
│  └──────────────────────────────────────────────────────┘   │
│                             ↓                                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ MERGE & PROCESS LOGIC                               │   │
│  │ ┌──────────────────────────────────────────────────┐ │   │
│  │ │ buildPpkIndex($ppkRows)                          │ │   │
│  │ │ Create lookup: "sls-desa-kec" => {target,real}   │ │   │
│  │ └──────────────────────────────────────────────────┘ │   │
│  │ ┌──────────────────────────────────────────────────┐ │   │
│  │ │ Loop progressRows:                               │ │   │
│  │ │ - detectPetugasType() → PML or PPL               │ │   │
│  │ │ - getRowValue() → Extract names (fallback)       │ │   │
│  │ │ - Extract: kode*, target, realisasi              │ │   │
│  │ │ - resolveRowKey() → Create lookup key            │ │   │
│  │ │ - normalizeNumber() → Convert amounts            │ │   │
│  │ │ - Override with PPK if key found                 │ │   │
│  │ │ - Build output row                               │ │   │
│  │ └──────────────────────────────────────────────────┘ │   │
│  │ Return: Array of merged rows                        │   │
│  └──────────────────────────────────────────────────────┘   │
│                             ↓                                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ PDF GENERATION (Barryvdh DomPDF)                    │   │
│  │ 1. Load view: pdf_template.blade.php                │   │
│  │ 2. Render: merged data → HTML                       │   │
│  │ 3. Convert: HTML → PDF (wkhtmltopdf)                │   │
│  │ 4. Set paper: A4 landscape                          │   │
│  │ 5. Download: Beban_Kerja_Petugas_SE2026.pdf         │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                             ↕
┌─────────────────────────────────────────────────────────────┐
│                    USER DOWNLOADS PDF                        │
└─────────────────────────────────────────────────────────────┘
```

---

### Data Transformation Flow - Excel PDF Generator

```
FILE 1: Progress Report                FILE 2: PPK Report
(Actual Progress Data)                 (Official Target Data)

Row 0: Header                          Row 0: Header
[No] [PML] [Sobat] [PPL] [Sobat]      [No] [ID] [Name] [PML] [PPL]
[KodeKec] [KodeDesa] [KodeSLS]        [KodeKec] [KodeDesa] [KodeSLS]
[Target] [Realisasi]                   [Target] [Realisasi]

             ↓                                      ↓
    ┌────────────────┐          ┌────────────────┐
    │ Parse Excel    │          │ Parse Excel    │
    │ → Array        │          │ → Array        │
    └────────────────┘          └────────────────┘
             ↓                                      ↓
    ┌────────────────────────────────────────────────┐
    │ buildMergedReport(progressRows, ppkRows)       │
    │                                                │
    │ 1. buildPpkIndex() ─────────────────────────→ │
    │    Create lookup:                              │
    │    {"0001-01-12": {target:105, real:90}}      │
    │                                                │
    │ 2. Loop progressRows ──────────────────────→ │
    │    For each row:                               │
    │    - Extract: pml_nama, ppl_nama, etc         │
    │    - resolveRowKey: "0001-01-12"              │
    │    - Lookup in PPK index                       │
    │    - Override: target=105, real=90             │
    │    - Build output row                          │
    │                                                │
    │ 3. Return: Array of merged rows               │
    └────────────────────────────────────────────────┘
             ↓
    ┌────────────────┐
    │ PDF Template   │
    │ Render & Gen   │
    └────────────────┘
             ↓
    ┌────────────────┐
    │ PDF File       │
    │ Download       │
    └────────────────┘
```

---

### Request/Response Flow - WhatsApp Sending

```
ADMIN CLICK: "Send Now"
    ↓
POST /send-now
    ↓
AdminController::sendNow()
    ├─ SELECT * FROM employees WHERE is_active = TRUE
    ├─ FOR each employee:
    │  ├─ Pick random template
    │  ├─ Replace {name}, {kata}, etc
    │  ├─ INSERT INTO wa_logs (status: pending)
    │  ├─ DISPATCH SendWhatsAppJob (sync)
    │  │  ├─ WhatsAppService::sendMessage($phone, $msg)
    │  │  │  ├─ Normalize phone
    │  │  │  ├─ Detect provider
    │  │  │  ├─ Build payload
    │  │  │  ├─ POST to WhatsApp API
    │  │  │  └─ Return: {success, status, error}
    │  │  ├─ UPDATE wa_logs (status: sent/failed)
    │  │  └─ Increment counter
    │  └─ NEXT employee
    └─ REDIRECT with summary message
```

---

## 4. SEQUENCE DIAGRAMS

### Sequence: Employee Import

```
User ─────────────────────┐
      │ Click Import       │
      │                    ↓
      │          AdminController
      │          ├─ Validate file
      │          ├─ Parse Excel
      │          │
      │          ├─ FOR each row:
      │          │  ├─ Normalize phone
      │          │  ├─ Create Employee
      │          │  └─ NEXT
      │          │
      │          ├─ Count imported & skipped
      │          └─ Return redirect with summary
      │                    │
      ├─ Receive redirect ─┘
      │ + Success message
      ↓
User sees: "Import selesai: 45 berhasil, 2 lewati"
```

---

### Sequence: PDF Generation

```
User ─────────────────────┐
      │ Upload 2 files     │
      │                    ↓
      │          ProgressReportController::process()
      │          │
      │          ├─ Validate both files
      │          │
      │          ├─ Parse file_progress → Array
      │          │
      │          ├─ Parse file_ppk → Array
      │          │
      │          ├─ buildMergedReport()
      │          │  ├─ buildPpkIndex() → Lookup table
      │          │  ├─ Loop progress rows
      │          │  ├─ Extract + override with PPK
      │          │  └─ Return merged array
      │          │
      │          ├─ Pdf::loadView('pdf_template', data)
      │          │
      │          ├─ Convert HTML → PDF
      │          │
      │          └─ Return download response
      │                    │
      ├─ Download PDF ─────┘
      │
      ↓
User opens: Beban_Kerja_Petugas_SE2026.pdf
```

---

## 5. ERROR HANDLING FLOW

### WhatsApp Error Scenarios

```
sendMessage() Call
    ├─ Config empty?
    │  └─ Return: {success: false, error: "Missing config"}
    │
    ├─ API unreachable?
    │  └─ Catch exception → Return: {success: false, error: "exception"}
    │
    ├─ HTTP Request
    │  ├─ 429 (Rate Limited)
    │  │  └─ Return: {success: false, error: "rate_limited", retry_after: 60}
    │  │
    │  ├─ 401 (Auth Failed)
    │  │  └─ Return: {success: false, error: "auth_failed", status: 401}
    │  │
    │  ├─ 200 (Success)
    │  │  ├─ Check response body
    │  │  ├─ If success → Return: {success: true, status: 200}
    │  │  └─ If error → Return: {success: false, error: "api_error"}
    │  │
    │  └─ Other status
    │     └─ Return: {success: false, status: XXX}
    │
    └─ Log everything: request, response, errors
```

---

## 6. Performance Considerations

### Query Optimization (pengingat-absen)

```
CURRENT (N+1 problem):
├─ SELECT * FROM employees (1 query)
├─ FOR each employee:
│  └─ SELECT * FROM wa_logs WHERE employee_id = ? (N queries)

OPTIMIZED:
├─ SELECT * FROM employees
├─ SELECT * FROM wa_logs WHERE employee_id IN (?, ?, ?) (1 query)
├─ Create map: {employee_id => log}
└─ Use map for lookups
```

---

### Memory Optimization (excel-pdf-generator)

```
LARGE FILE PROCESSING:
├─ Old: Load entire file into memory
│  └─ Risk: Out of memory for large files
│
└─ New: Process with streaming/chunking
   ├─ Read N rows at a time
   ├─ Process chunk
   ├─ Write to PDF
   └─ Free memory, continue
```

---

**Document Version: 1.0**
**Last Updated: 2026-08-18 06:06 UTC**
