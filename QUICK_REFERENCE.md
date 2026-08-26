# QUICK REFERENCE GUIDE - Fungsi Utama

## 📋 Pengingat Absen - Function Map

### Controller Methods (AdminController)

| Method | Purpose | Input | Output |
|--------|---------|-------|--------|
| `index()` | Dashboard dengan status karyawan | - | View dengan employees, statuses, settings |
| `storeEmployee()` | Tambah karyawan | name, phone_number | Redirect + success msg |
| `updateEmployee()` | Update karyawan | id, name, phone_number, is_active | Redirect + success msg |
| `deleteEmployee()` | Hapus karyawan | id | Redirect + success msg |
| `importEmployees()` | Bulk import dari Excel | employee_file (max 5MB) | Summary: imported & skipped count |
| `exportEmployees()` | Export ke CSV | - | CSV file download |
| `updateSetting()` | Update waktu & template | times, templates, closing_word | Redirect |
| `sendNow()` | Manual send immediate | - | Send ke semua active employees |
| `sendPreCheckinNow()` | Pre-reminder masuk | - | Send N menit sebelum check-in |
| `sendPreCheckoutNow()` | Pre-reminder pulang | - | Send N menit sebelum check-out |
| `setDefaultTimes()` | Reset ke waktu default | - | Check-in: 07:30, Check-out: 16:00 |

### Service Methods (WhatsAppService)

| Method | Purpose | Input | Output |
|--------|---------|-------|--------|
| `sendMessage()` | Kirim WhatsApp | phone, message | Array: success, status, error |

---

## 📊 Pengingat Absen - Data Flow

```
┌─────────────────────────────────────────────────────────┐
│ USER ACTIONS                                            │
├─────────────────────────────────────────────────────────┤
│ 1. Admin Dashboard (index)                              │
│    ↓ Shows: employees, last send status, settings      │
│                                                          │
│ 2. Import Employees (importEmployees)                   │
│    ↓ Parse Excel → Normalize → Create records          │
│                                                          │
│ 3. Update Settings (updateSetting)                      │
│    ↓ check_in_time, check_out_time, templates          │
│                                                          │
│ 4. Send Now (sendNow)                                   │
│    ↓ Create wa_logs → SendWhatsAppJob (sync)           │
│    ↓ WhatsAppService::sendMessage() → Provider API    │
│    ↓ Update wa_logs status                             │
│                                                          │
│ 5. Send Pre-Reminder (sendPreCheckinNow)               │
│    ↓ Calculate minutes left                             │
│    ↓ Append random pantun                               │
│    ↓ Create wa_logs → SendWhatsAppJob (sync)           │
│    ↓ WhatsAppService::sendMessage() → Provider API    │
│                                                          │
│ 6. Scheduled Task (SendWaReminder command)             │
│    ↓ Runs via cron/scheduler                            │
│    ↓ Detects check-in/check-out time                   │
│    ↓ Dispatch SendWhatsAppJob (async queue)            │
└─────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ DATABASE UPDATES                                         │
├──────────────────────────────────────────────────────────┤
│ employees table:                                         │
│   - CRUD: create, read, update, delete                 │
│   - Filter: is_active = true saat send                 │
│                                                           │
│ settings table (key-value):                             │
│   - check_in_time: "07:30"                              │
│   - check_out_time: "16:00"                             │
│   - pre_reminder_minutes: 30                             │
│   - template_checkin: "Halo {name}, ..."               │
│   - closing_word: "Semangat kerja!"                     │
│                                                           │
│ wa_logs table (audit trail):                            │
│   - Record setiap attempt send                           │
│   - Status: pending → sent/failed                       │
│   - Log error messages                                   │
│                                                           │
│ pantuns table (optional):                               │
│   - Random poetry append ke messages                     │
└──────────────────────────────────────────────────────────┘
```

---

## 📊 Excel PDF Generator - Data Flow

```
┌──────────────────────────────────────────────────────────┐
│ USER UPLOAD                                              │
├──────────────────────────────────────────────────────────┤
│ 1. Upload file_progress (Excel/XLSX)                     │
│ 2. Upload file_ppk (Excel/XLSX)                          │
│ 3. Click "Generate PDF"                                  │
└──────────────────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────────────────┐
│ ProgressReportController::process()                      │
├──────────────────────────────────────────────────────────┤
│ 1. Validate: both files required, xlsx/xls only         │
│ 2. Parse file_progress → Excel::toArray()               │
│ 3. Parse file_ppk → Excel::toArray()                    │
│ 4. Call buildMergedReport($progressRows, $ppkRows)      │
└──────────────────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────────────────┐
│ buildMergedReport() - MAIN MERGE LOGIC                   │
├──────────────────────────────────────────────────────────┤
│ 1. Call buildPpkIndex($ppkRows)                          │
│    → Create lookup: "sls-desa-kec" => {target, real}    │
│                                                           │
│ 2. Loop progressRows:                                    │
│    a. Skip header (row 0) & empty rows                  │
│    b. detectPetugasType() → PML or PPL                  │
│    c. getRowValue() with fallback indexes               │
│       - Extract: pml_nama, pml_sobat, ppl_nama, etc    │
│    d. Extract geo codes: kodeKec, kodeDesa, kodeSls     │
│    e. Extract target & realisasi                         │
│    f. resolveRowKey() → "sls-desa-kec"                  │
│    g. normalizeNumber() → convert to int                │
│    h. Override target/realisasi if found in PPK index   │
│    i. Build output row with all fields                   │
│                                                           │
│ 3. Return array of structured rows                       │
└──────────────────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────────────────┐
│ PDF Generation                                           │
├──────────────────────────────────────────────────────────┤
│ 1. Load view: "pdf_template" with merged data           │
│ 2. Pdf::loadView() → Generate HTML to PDF               │
│ 3. setPaper('a4', 'landscape')                          │
│ 4. Download file: "Beban_Kerja_Petugas_SE2026.pdf"      │
└──────────────────────────────────────────────────────────┘
```

---

## 🔧 Common Code Patterns

### Pattern 1: Phone Normalization (Pengingat Absen)

```php
// Input: "0812345678" or "62812345678" or "+62812345678"
// Output: "812345678" (no leading 0, no country code)

$phone = preg_replace('/[^0-9+]/', '', $phone);  // Keep only digits & +
if (strpos($phone, '0') === 0) {
    $phone = substr($phone, 1);  // Remove leading 0
}
// Result: "812345678"
```

### Pattern 2: Fallback Column Access (Excel PDF Generator)

```php
// Try multiple column indexes, use first non-empty
$value = $this->getRowValue($row, [1, 0, 2]);

// Tries:
// 1. $row[1] if not empty → return
// 2. $row[0] if not empty → return
// 3. $row[2] if not empty → return
// 4. Else → return ''
```

### Pattern 3: Composite Key Matching (Excel PDF Generator)

```php
// Create key from 3 geographic codes
$key = $this->resolveRowKey($kodeSls, $kodeDesa, $kodeKec);
// Output: "1234-5678-12" or null

// Use for PPK lookup
if (isset($ppkIndex[$key])) {
    $target = $ppkIndex[$key]['target'];
    $realisasi = $ppkIndex[$key]['realisasi'];
}
```

### Pattern 4: Number Normalization (Excel PDF Generator)

```php
// Handle: "1.234", "1,234", "1234.56", "N/A"
$value = "1.234,56";

$cleaned = preg_replace('/[^0-9,.-]/', '', $value);  // "1.234,56"
$cleaned = str_replace(',', '', $cleaned);           // "1.23456"
return (int) $cleaned;                                // 1 (truncated)

// Better: preg_replace('/[^0-9]/', '') → "123456"
```

### Pattern 5: Template Replacement

```php
$template = "Halo {name}, sudah waktunya absen {target_time}. {kata}";

$message = str_replace(
    ['{name}', '{target_time}', '{kata}'],
    [$employee->name, '07:30', 'Semangat kerja!'],
    $template
);

// Output: "Halo Budi, sudah waktunya absen 07:30. Semangat kerja!"
```

### Pattern 6: Job Dispatch (Synchronous vs Asynchronous)

```php
// SYNCHRONOUS - blocking, immediate execution
SendWhatsAppJob::dispatchSync($logId, $empId, $phone, $msg, $type);

// ASYNCHRONOUS - non-blocking, queued for later
SendWhatsAppJob::dispatch($logId, $empId, $phone, $msg, $type);
// Requires: php artisan queue:listen
```

---

## 🎯 API Response Formats

### WhatsAppService::sendMessage() Response

**Success Response:**
```json
{
  "success": true,
  "status": 200
}
```

**Rate Limited Response:**
```json
{
  "success": false,
  "error": "rate_limited",
  "retry_after": 60
}
```

**Auth Error Response:**
```json
{
  "success": false,
  "error": "auth_failed",
  "status": 401,
  "message": "Invalid API key"
}
```

**Exception Response:**
```json
{
  "success": false,
  "error": "exception",
  "message": "Connection timeout"
}
```

---

## 📁 File Organization

### pengingat-absen/
```
app/
├── Http/Controllers/
│   ├── AdminController.php          ← Main CRUD & send logic
│   ├── AuthController.php           ← Login/logout
│   └── Controller.php               ← Base controller
├── Models/
│   ├── Employee.php                 ← Employee model
│   ├── Setting.php                  ← Settings key-value
│   ├── User.php                     ← Auth user
│   └── WaLog.php                    ← WhatsApp log (optional)
├── Services/
│   └── WhatsAppService.php          ← Provider abstraction
├── Jobs/
│   └── SendWhatsAppJob.php          ← Queue job
├── Console/
│   ├── Commands/
│   │   ├── SendWaReminder.php       ← Scheduled task
│   │   └── TestWhatsApp.php         ← Test command
│   └── Kernel.php                   ← Schedule definition
└── Providers/
    └── AppServiceProvider.php       ← Service registration

database/
├── migrations/
│   ├── create_employees_table.php
│   ├── create_settings_table.php
│   ├── create_wa_logs_table.php
│   └── create_pantuns_table.php
└── seeders/

resources/views/
├── admin/
│   └── dashboard.blade.php          ← Main UI
├── layouts/
│   └── app.blade.php

routes/
├── web.php                          ← Web routes
└── api.php                          ← API routes (if any)

config/
├── whatsapp.php                     ← WhatsApp config
├── database.php
└── queue.php
```

### excel-pdf-generator/
```
app/Http/Controllers/
├── ProgressReportController.php     ← Main logic
└── Controller.php

resources/views/
├── index.blade.php                  ← Upload form
├── pdf_template.blade.php           ← PDF layout
└── layouts/

routes/
└── web.php

config/
└── database.php
```

---

## 🚀 Quick Start Commands

### Pengingat Absen

```bash
# Setup
cd ~/pengingat-absen
composer install
php artisan migrate --force
npm install && npm run build

# Development
php artisan serve                    # Server on :8000
php artisan queue:listen            # Queue worker (Terminal 2)
npm run dev                          # Frontend (Terminal 3)

# Manual testing
php artisan tinker
>>> $emp = App\Models\Employee::first();
>>> app(App\Services\WhatsAppService::class)->sendMessage($emp->phone_number, 'Test');

# Database
php artisan tinker
>>> DB::table('wa_logs')->orderByDesc('created_at')->first();
>>> App\Models\Setting::all();

# Testing
php artisan test
```

### Excel PDF Generator

```bash
# Setup
cd ~/excel-pdf-generator
composer install
php artisan migrate --force
npm install && npm run build

# Development
php artisan serve                    # Server on :8000
npm run dev                          # Frontend

# Test PDF generation
# Upload sample files via UI at http://localhost:8000
```

---

## 🐛 Debugging Tips

### Pengingat Absen

**Check WhatsApp logs:**
```bash
# Real-time log viewing
tail -f storage/logs/laravel.log | grep -i whatsapp

# Or in tinker
>>> DB::table('wa_logs')->orderByDesc('id')->limit(5)->get();

# Check specific employee sends
>>> DB::table('wa_logs')->where('employee_id', 1)->get();
```

**Test WhatsApp service:**
```bash
php artisan tinker
>>> $svc = app(App\Services\WhatsAppService::class);
>>> $result = $svc->sendMessage('62812345678', 'Test message');
>>> dd($result);
```

**Check queue:**
```bash
# List queued jobs
>>> DB::table('jobs')->get();

# Process queue manually
php artisan queue:work --once

# Monitor queue
php artisan queue:monitor
```

**Check settings:**
```bash
php artisan tinker
>>> App\Models\Setting::all();
>>> App\Models\Setting::get('check_in_time');
```

---

### Excel PDF Generator

**Debug merge logic:**
```php
// Add to ProgressReportController::process()
dd($processedData);  // Inspect merged rows before PDF

// Or in tinker
>>> $ctrl = app(ProgressReportController::class);
>>> $progress = Excel::toArray([], 'file1.xlsx')[0];
>>> $ppk = Excel::toArray([], 'file2.xlsx')[0];
>>> $merged = $ctrl->buildMergedReport($progress, $ppk);
>>> dd($merged);
```

**Check PDF rendering:**
```php
// In controller
$pdf = Pdf::loadView('pdf_template', ['items' => $data]);
return $pdf->stream();  // View in browser instead of download
```

---

## 📈 Performance Optimization

### pengingat-absen

1. **Database Indexes:**
   ```sql
   CREATE INDEX idx_employees_active ON employees(is_active);
   CREATE INDEX idx_wa_logs_employee ON wa_logs(employee_id);
   CREATE INDEX idx_wa_logs_created ON wa_logs(created_at);
   CREATE INDEX idx_settings_key ON settings(key);
   ```

2. **Queue Optimization:**
   - Use `queue:work --daemon` untuk production (persistent worker)
   - Monitor queue dengan `queue:monitor`
   - Set retry policy untuk failed jobs

3. **API Caching:**
   - Cache WhatsApp responses jika needed
   - Implement request throttling per phone

---

### excel-pdf-generator

1. **Memory Optimization:**
   - Process large Excel files dengan chunking
   - Use streaming untuk PDF generation
   ```php
   // Instead of loading entire file
   Excel::import(new ProgressImport, $file);
   ```

2. **File Upload Limits:**
   - Set reasonable max file size (5MB-50MB)
   - Implement upload progress tracking
   - Cleanup temp files after processing

---

## 📞 Support & Contacts

**WhatsApp Provider Docs:**
- Foonte: https://fonnte.com/
- WaSender: https://wasender.com/
- Infobip: https://www.infobip.com/

**Laravel Documentation:**
- Jobs & Queues: https://laravel.com/docs/queues
- Excel: https://docs.laravel-excel.com/
- DomPDF: https://github.com/barryvdh/laravel-dompdf

---

## ✅ Checklist - Before Production

### pengingat-absen
- [ ] Set `APP_ENV=production` di .env
- [ ] Set `APP_DEBUG=false`
- [ ] Configure WhatsApp credentials
- [ ] Setup database backups
- [ ] Setup SSL certificate (HTTPS)
- [ ] Configure email for notifications
- [ ] Test queue worker persistence
- [ ] Setup monitoring for failed jobs
- [ ] Test all WhatsApp templates
- [ ] Prepare employee import template

### excel-pdf-generator
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Setup file upload directory with permissions
- [ ] Configure max upload size
- [ ] Test PDF generation with real data
- [ ] Setup file cleanup (remove old uploads)
- [ ] Configure backup for generated PDFs

---

**Version: 1.0**
**Last Updated: 2026-08-18**
