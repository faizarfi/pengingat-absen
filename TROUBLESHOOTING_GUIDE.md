# TROUBLESHOOTING GUIDE & CHEATSHEET

## 1. PENGINGAT ABSEN - Common Issues & Solutions

### Issue 1: WhatsApp Messages Not Sending

**Symptoms:**
- Messages stuck in "pending" status
- wa_logs shows status = "pending" but never updated to "sent"
- No errors in log

**Root Causes & Solutions:**

**A) Missing/Invalid WhatsApp Configuration**
```
Check .env:
  WHATSAPP_URL=https://api.fonnte.com
  WHATSAPP_KEY=your_actual_token_here

Test in Tinker:
  php artisan tinker
  >>> config('whatsapp.url')
  >>> config('whatsapp.key')
  
Fix:
  - Update .env with correct URL and token
  - Run: php artisan config:clear
```

**B) Queue Worker Not Running**
```
Check if queue is listening:
  ps aux | grep "queue:listen"
  
If not running, start it:
  php artisan queue:listen --tries=1 --timeout=0
  
Or use supervisor (production):
  /etc/supervisor/conf.d/laravel-worker.conf
```

**C) Phone Number Format Invalid**
```
Valid formats:
  - "812345678" (with country code prefix)
  - "62812345678" (full international)
  
Invalid formats:
  - "0812345678" (leading zero - will be removed)
  - "+62812345678" (+ will be removed, but number is valid)

Test normalization:
  $phone = "0812345678";
  $phone = preg_replace('/[^0-9+]/', '', $phone);
  if (strpos($phone, '0') === 0) {
      $phone = substr($phone, 1);
  }
  // Result: "812345678"
```

**D) WhatsApp Provider API Rate Limit**
```
Error response:
  {
    "success": false,
    "error": "rate_limited",
    "retry_after": 60
  }

Solution:
  - Add delay between sends (use queue with delay)
  - Implement exponential backoff retry
  - Contact provider for rate limit increase
  
In code:
  SendWhatsAppJob::dispatch($logId, $empId, $phone, $msg)
    ->delay(now()->addSeconds(10));
```

**E) SSL Certificate Verification Error**
```
Error in logs:
  "cURL error 60: SSL certificate problem"

Reason:
  - Invalid SSL cert on provider endpoint
  - Local environment SSL settings
  
Temporary fix (DEBUG mode only):
  app/Services/WhatsAppService.php line 64-66:
  if (app()->environment('local') || config('app.debug')) {
      $options['verify'] = false;
  }

Permanent fix:
  - Update CA certificates
  - Contact provider for valid certificate
```

**Debug Steps:**
```bash
# 1. Check latest logs
tail -f storage/logs/laravel.log | grep -i whatsapp

# 2. Check wa_logs table
php artisan tinker
>>> DB::table('wa_logs')->orderByDesc('id')->limit(10)->get();

# 3. Check jobs table (if using database queue)
>>> DB::table('jobs')->get();

# 4. Test WhatsAppService directly
>>> $svc = app(App\Services\WhatsAppService::class);
>>> $result = $svc->sendMessage('62812345678', 'Test');
>>> dd($result);

# 5. Check database connection
>>> DB::connection()->getPdo();
```

---

### Issue 2: Import Excel File Fails

**Symptoms:**
- "File kosong atau tidak dapat dibaca"
- "Import gagal: ..." with error message
- Some rows imported but others skipped

**Root Causes & Solutions:**

**A) File Format Not Supported**
```
Supported: csv, xlsx, xls, ods
Not supported: pdf, txt, json, etc

Fix:
  - Convert file to XLSX in Excel
  - Save as CSV with UTF-8 encoding
  - Check file extension
```

**B) File Too Large**
```
Max size: 5MB

Check file size:
  ls -lh employees.xlsx
  
If too large:
  - Split into multiple files
  - Remove unnecessary columns
  - Filter data before export
```

**C) Invalid Column Structure**
```
Expected format:
  Row 0 (Header): Nama Pegawai | Nomor Telepon
  Row 1+: Data rows
  
Invalid:
  - Missing header
  - Wrong column order
  - Extra columns in wrong place

Fix: Follow template exactly
```

**D) Phone Numbers in Wrong Format**
```
In Excel, phone numbers stored as:
  - String: "0812345678" (correct)
  - Number: 812345678 (loses leading zero)
  - Formula: =CONCATENATE("08",12345678) (needs evaluation)

Ensure all phone numbers are stored as TEXT:
  - Right click column → Format Cells → Text
  - Or add single quote prefix: '0812345678
```

**E) Empty or Duplicate Rows**
```
Skipped rows:
  - Completely empty rows (all cells blank)
  - Rows with missing name or phone
  - Duplicate phone numbers (separate issue)

Solution:
  - Clean Excel file before import
  - Remove empty rows
  - Fill all required columns
```

**Debug Steps:**
```bash
# 1. Check import logs
tail -f storage/logs/laravel.log | grep -i import

# 2. Test file parsing
php artisan tinker
>>> $rows = Maatwebsite\Excel\Facades\Excel::toArray([], 'path/to/file.xlsx')[0];
>>> dd($rows);

# 3. Check parsed data
>>> $rows[0];  // Header
>>> $rows[1];  // First data row

# 4. Verify database after import
>>> App\Models\Employee::count();
>>> App\Models\Employee::latest()->first();
```

---

### Issue 3: Settings Not Saving or Applying

**Symptoms:**
- Settings page shows old values after submit
- Sent messages don't use new templates
- Times don't update

**Root Causes & Solutions:**

**A) Form Validation Failing**
```
Expected format:
  check_in_time: "HH:mm" (e.g., "07:30")
  check_out_time: "HH:mm" (e.g., "16:00")
  pre_reminder_minutes: integer 1-120

Invalid:
  "7:30" → Should be "07:30"
  "1630" → Should be "16:30"
  200 → Exceeds max 120

Check validation in form:
  <input type="time" name="check_in_time" />
```

**B) Settings Table Empty or Corrupted**
```
Check settings:
  php artisan tinker
  >>> App\Models\Setting::all();
  >>> App\Models\Setting::get('check_in_time');

If empty, seed defaults:
  >>> App\Models\Setting::create(['key' => 'check_in_time', 'value' => '07:30']);
  >>> App\Models\Setting::create(['key' => 'check_out_time', 'value' => '16:00']);
```

**C) Config Cache Not Cleared**
```
After updating settings:
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
```

**D) Templates Have Invalid Placeholders**
```
Valid placeholders:
  {name}           → Employee name
  {kata}           → Closing word
  {minutes_left}   → Minutes until time
  {target_time}    → HH:mm format time
  {organization}   → Org name

Invalid placeholders (will NOT be replaced):
  {employee_name}  → Use {name}
  {message}        → Not supported
  {{name}}         → Double braces

When template doesn't have placeholder:
  - Message is used as-is
  - Fallback text appended if applicable
```

**Debug Steps:**
```bash
# 1. Check database
php artisan tinker
>>> DB::table('settings')->get();

# 2. Test setting retrieval
>>> App\Models\Setting::get('check_in_time');
>>> App\Models\Setting::get('template_checkin');

# 3. Check if Setting model uses cache
>>> App\Models\Setting::where('key', 'check_in_time')->first();
>>> DB::table('settings')->where('key', 'check_in_time')->first();
```

---

### Issue 4: Dashboard Shows Wrong Status

**Symptoms:**
- All employees show "Sudah terkirim" even if not sent
- Times in detail column are wrong
- Statuses don't update after sending

**Root Causes & Solutions:**

**A) wa_logs Table Not Populated**
```
Check logs:
  php artisan tinker
  >>> DB::table('wa_logs')->count();
  >>> DB::table('wa_logs')->latest()->first();

If empty:
  - Send messages to populate logs
  - Or manually insert test record:
    >>> DB::table('wa_logs')->insert([
      'employee_id' => 1,
      'type' => 'manual',
      'status' => 'sent',
      'sent_at' => now(),
      'created_at' => now(),
      'updated_at' => now()
    ]);
```

**B) Status Detection Logic in Controller**
```
Status mapping (AdminController@index):
  - sent_at is not null → 'sent'
  - scheduled_at is not null → 'pending'
  - status column = 'failed' → 'failed'
  - else → no status shown

Fix:
  - Ensure wa_logs has correct columns
  - Verify migration created all columns
  - Check column data types
```

**C) Timestamp Formatting Wrong**
```
Detail shows: "10:30:45" (last send time)

Format: Carbon::parse($log->sent_at)->format('H:i:s')

If wrong:
  - Check timezone in config/app.php
  - Verify database timestamp storage
  - Test: Carbon::now()->format('H:i:s')
```

---

### Issue 5: Pre-Reminder Not Working

**Symptoms:**
- Pre-reminder not sent at scheduled time
- No records in wa_logs for pre_checkin/pre_checkout
- Pantun not appearing in message

**Root Causes & Solutions:**

**A) Scheduled Command Not Running**
```
Check if command scheduled:
  config/app/Console/Kernel.php
  
Should have:
  $schedule->command('send:wa-reminder')
    ->everyMinute();

Test command manually:
  php artisan send:wa-reminder

Check scheduled tasks:
  php artisan schedule:list
```

**B) Pantuns Table Not Populated**
```
Check pantuns:
  php artisan tinker
  >>> DB::table('pantuns')->count();
  >>> DB::table('pantuns')->where('type', 'masuk')->get();

If empty, add pantuns:
  >>> DB::table('pantuns')->insert([
    ['type' => 'masuk', 'text' => 'Pantun 1...'],
    ['type' => 'masuk', 'text' => 'Pantun 2...'],
    ['type' => 'pulang', 'text' => 'Pantun pulang...']
  ]);
```

**C) Minutes Left Calculation Wrong**
```
Code:
  $target = Carbon::createFromFormat('H:i', $checkInTime);
  $targetToday = $target->setDate($now->year, $now->month, $now->day);
  $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

Debug:
  >>> $now = Carbon::now();
  >>> $target = Carbon::createFromFormat('H:i', '07:30');
  >>> $targetToday = $target->setDate($now->year, $now->month, $now->day);
  >>> $now->diffInMinutes($targetToday);
```

**D) Pre-reminder Minutes Setting Wrong**
```
Setting key: 'pre_reminder_minutes'
Range: 1-120

If setting > 120 or < 1:
  - Validation fails
  - Pre-reminder not sent
  
Check:
  >>> App\Models\Setting::get('pre_reminder_minutes');
```

---

## 2. EXCEL PDF GENERATOR - Common Issues & Solutions

### Issue 1: PDF Generation Fails or Is Empty

**Symptoms:**
- PDF downloads but is blank
- "TCPDF or wkhtmltopdf error"
- File corrupted when opened

**Root Causes & Solutions:**

**A) PDF Template View Missing**
```
Check file exists:
  resources/views/pdf_template.blade.php

If missing:
  - Create template from scratch
  - Ensure variables match: $items array
  - Use Blade syntax: @foreach, {{ }}, etc
```

**B) Data Not Passed to Template**
```
In ProgressReportController::process():
  $pdf = Pdf::loadView('pdf_template', ['items' => $processedData]);

Check data:
  dd($processedData);  // Add before PDF generation
  
Verify $processedData:
  - Is array
  - Has expected structure
  - Not empty
```

**C) Blade Syntax Error in Template**
```
Common mistakes:
  {{ $item->name }} → Correct
  {!! $item->name !!} → For HTML (be careful!)
  {{ $undefined_var }} → Error if undefined
  
Safe access:
  {{ $item->name ?? 'N/A' }}
  {{ optional($item)->name }}
```

**D) DomPDF Library Issues**
```
Check installation:
  composer show | grep dompdf
  
Install if missing:
  composer require barryvdh/laravel-dompdf

Check config:
  config/dompdf.php (if exists)
  
Test PDF generation:
  php artisan tinker
  >>> $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>Test</h1>');
  >>> $pdf->download('test.pdf');
```

**E) Memory Exceeded During Generation**
```
Error:
  "Allowed memory size exhausted"

Increase memory:
  php.ini: memory_limit = 512M
  Or in code:
    ini_set('memory_limit', '512M');
  
In ProgressReportController::process():
    ini_set('memory_limit', '512M');
    $pdf = Pdf::loadView('pdf_template', ['items' => $processedData]);
```

**Debug Steps:**
```bash
# 1. Check template loads
php artisan tinker
>>> view('pdf_template')->render();

# 2. Test with dummy data
>>> $items = [
  ['pml_nama' => 'Test', 'target' => 100, 'realisasi' => 90]
];
>>> view('pdf_template', compact('items'))->render();

# 3. Test PDF generation
>>> $pdf = Pdf::loadView('pdf_template', ['items' => $items]);
>>> $pdf->stream('test.pdf');
```

---

### Issue 2: Merge Logic Not Working Correctly

**Symptoms:**
- Target/Realisasi not matching PPK data
- Wrong petugas names showing
- Kode columns mixed up

**Root Causes & Solutions:**

**A) Column Position Incorrect in Excel**
```
Progress file should have:
  [0]: No
  [1]: PML Nama (or index varies)
  [2]: PML Sobat
  [3]: PPL Nama
  [4]: PPL Sobat
  [5]: KodeKec
  [6]: KodeDesa
  [7]: KodeSLS
  [8]: Target
  [9]: Realisasi
  ...

If different:
  - Adjust column indexes in code
  - Or use getRowValue() with new indexes
  - Test: dd($progressRows[1]); to see actual indexes
```

**B) Geographic Key Not Matching**
```
Key format: "kodeSls-kodeDesa-kodeKec"

Both files must use same codes for match.

Test:
  >>> $key = resolveRowKey('0001', '01', '12');
  >>> // Result: "0001-01-12"
  
Check if key exists in PPK index:
  >>> isset($ppkIndex[$key]);
```

**C) Empty or Null Values in Key Components**
```
If kodeSls, kodeDesa, or kodeKec is empty:
  - Key becomes partial or null
  - Lookup fails
  - Data not overridden

Solution:
  - Fill all geographic codes in both files
  - Or adjust key generation to handle nulls
```

**D) normalizeNumber() Converting Wrong**
```
Input: "1.234,56"
Expected: 1234

Current logic removes ',', then truncates decimal:
  $cleaned = str_replace(',', '', "1.234,56");  // "1.23456"
  return (int) "1.23456";  // 1 (WRONG!)

Better logic:
  $cleaned = preg_replace('/[^0-9]/', '', $input);  // "123456"
  return (int) "123456";  // 123456 (Correct)

If numbers wrong:
  - Check normalizeNumber() implementation
  - Verify Excel cell format (Number vs Text)
```

**E) PML vs PPL Detection Failing**
```
detectPetugasType() searches for "PML" or "PPL" in row text.

If not found:
  - Defaults to "PML"
  - Might extract wrong columns

Debug:
  >>> $type = detectPetugasType($row);
  >>> dd($type);
  
If wrong type:
  - Ensure "PML" or "PPL" text present in Excel
  - Or add more detection logic
```

**Debug Steps:**
```bash
# 1. Parse both files
php artisan tinker
>>> $progress = Excel::toArray([], 'file1.xlsx')[0];
>>> $ppk = Excel::toArray([], 'file2.xlsx')[0];

# 2. Inspect data
>>> dd($progress[1]);  // First data row
>>> dd($ppk[1]);

# 3. Test merge logic
>>> $ctrl = app(ProgressReportController::class);
>>> $merged = $ctrl->buildMergedReport($progress, $ppk);
>>> dd($merged[0]);

# 4. Check PPK index
>>> $ppkIndex = $ctrl->buildPpkIndex($ppk);
>>> dd($ppkIndex);

# 5. Test key resolution
>>> $key = $ctrl->resolveRowKey('0001', '01', '12');
>>> dd($key);
>>> isset($ppkIndex[$key]);
```

---

### Issue 3: Validation Errors on File Upload

**Symptoms:**
- "The file_progress field is required"
- "The file_ppk must be a file of type: xlsx, xls"
- "419: CSRF token mismatch"

**Root Causes & Solutions:**

**A) Both Files Not Uploaded**
```
Check form:
  <form method="POST" action="/process" enctype="multipart/form-data">
    <input type="file" name="file_progress" required />
    <input type="file" name="file_ppk" required />
  </form>

Fix:
  - Select both files before submit
  - Ensure correct input names
```

**B) Wrong File Format**
```
Allowed: xlsx, xls
Not allowed: csv, pdf, txt

Fix:
  - Convert to XLSX in Excel
  - Save as Excel format
  - Check file extension
```

**C) File Size Exceeds Limit**
```
Check config/filesystems.php:
  'local' => [
    'driver' => 'local',
    'root' => storage_path('app'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'private',
  ],

Also check php.ini:
  upload_max_filesize = 10M
  post_max_size = 10M

Increase if needed in .env or php.ini
```

**D) CSRF Token Expired**
```
Error: "419 Page Expired"

Reason:
  - Form submitted after token expires
  - Session corrupted
  
Fix:
  - Include @csrf in form:
    <form method="POST" action="/process" enctype="multipart/form-data">
      @csrf
      ...
    </form>
```

---

## 3. DATABASE TROUBLESHOOTING

### Issue: Database Connection Failed

**Symptoms:**
- "SQLSTATE[HY000]: General error: 1 cannot open shared object"
- "No such table: employees"
- Connection refused

**Solutions:**

**A) SQLite Database File Missing**
```
Check:
  database/database.sqlite

If missing:
  - Create file: touch database/database.sqlite
  - Run migrations: php artisan migrate --force
```

**B) Database File Permissions Wrong**
```
Check permissions:
  ls -la database/database.sqlite

Fix:
  chmod 666 database/database.sqlite
  chmod 775 database/
```

**C) Migrations Not Run**
```
Check:
  php artisan migrate:status

Run migrations:
  php artisan migrate --force

Rollback if needed:
  php artisan migrate:rollback
```

---

## 4. QUICK REFERENCE - Common Commands

### Pengingat Absen

```bash
# Development
php artisan serve                          # Start server
php artisan queue:listen                   # Queue worker
npm run dev                                # Frontend dev

# Database
php artisan migrate --force                # Run migrations
php artisan tinker                         # REPL console
php artisan db:seed                        # Seed database

# Testing
php artisan test                           # Run tests
php artisan test --filter=MethodName       # Single test

# Cleanup
php artisan cache:clear                    # Clear cache
php artisan view:clear                     # Clear views
php artisan config:clear                   # Clear config
php artisan queue:failed                   # View failed jobs
php artisan queue:forget job_id            # Forget job

# WhatsApp Testing
php artisan send:wa-reminder               # Manual send
php artisan command:test-whatsapp          # Test connection

# Logs
tail -f storage/logs/laravel.log           # Real-time logs
grep -i "whatsapp" storage/logs/laravel.log # Filter logs
```

### Excel PDF Generator

```bash
# Development
php artisan serve                          # Start server
npm run dev                                # Frontend dev

# Database
php artisan migrate --force                # Run migrations
php artisan tinker                         # REPL console

# Testing
php artisan test                           # Run tests

# Cleanup
php artisan cache:clear                    # Clear cache
php artisan view:clear                     # Clear views
storage/                                   # Temp files cleanup
```

---

## 5. TINKER DEBUGGING CHEATSHEET

```bash
# Start Tinker
php artisan tinker

# ===== PENGINGAT ABSEN =====

# Employee Management
>>> App\Models\Employee::all();
>>> App\Models\Employee::where('is_active', true)->count();
>>> App\Models\Employee::find(1);
>>> $emp = App\Models\Employee::create(['name' => 'Test', 'phone_number' => '62812345678']);
>>> $emp->delete();

# Settings
>>> App\Models\Setting::all();
>>> App\Models\Setting::get('check_in_time');
>>> App\Models\Setting::set('check_in_time', '07:30');

# WhatsApp Logs
>>> DB::table('wa_logs')->latest()->first();
>>> DB::table('wa_logs')->where('employee_id', 1)->get();
>>> DB::table('wa_logs')->where('status', 'failed')->get();

# Test WhatsApp Service
>>> $svc = app(App\Services\WhatsAppService::class);
>>> $result = $svc->sendMessage('62812345678', 'Test message');
>>> dd($result);

# Jobs Queue
>>> DB::table('jobs')->count();
>>> DB::table('jobs')->first();
>>> DB::table('failed_jobs')->get();

# ===== EXCEL PDF GENERATOR =====

# Parse Excel
>>> use Maatwebsite\Excel\Facades\Excel;
>>> $rows = Excel::toArray([], 'path/to/file.xlsx')[0];
>>> dd($rows[0]);  # Header
>>> dd($rows[1]);  # First data row

# Test Merge Logic
>>> $controller = app(App\Http\Controllers\ProgressReportController::class);
>>> $merged = $controller->buildMergedReport($progressRows, $ppkRows);
>>> dd($merged);

# ===== UTILITIES =====

# Date/Time
>>> now();
>>> Carbon\Carbon::now();
>>> Carbon\Carbon::now()->format('Y-m-d H:i:s');
>>> Carbon\Carbon::now()->addHours(1);

# Database
>>> DB::table('employees')->count();
>>> DB::select('SELECT * FROM employees LIMIT 5');
>>> DB::connection()->getPdo();  # Test connection

# File Operations
>>> file_exists('storage/app/test.txt');
>>> file_get_contents('storage/app/test.txt');

# Exit Tinker
>>> exit
```

---

## 6. PRODUCTION CHECKLIST

### Before Deployment

**pengingat-absen**
- [ ] `.env` configured with production values
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] WhatsApp credentials verified
- [ ] Database backups configured
- [ ] Queue worker running (supervisor)
- [ ] Logs configured (rotation, size limit)
- [ ] SSL certificate installed (HTTPS)
- [ ] File permissions correct (755 dirs, 644 files)
- [ ] Cache cleared (`php artisan optimize`)
- [ ] Config cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Migrations run (`php artisan migrate --force`)

**excel-pdf-generator**
- [ ] `.env` configured with production values
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] File upload directory exists with permissions
- [ ] Max upload size configured
- [ ] PDF temp files cleanup scheduled
- [ ] SSL certificate installed (HTTPS)
- [ ] Cache cleared (`php artisan optimize`)
- [ ] Migrations run (`php artisan migrate --force`)

---

## 7. LOG FILE Locations

```
Laravel Logs:
  storage/logs/laravel.log
  storage/logs/laravel-YYYY-MM-DD.log

Queue Logs:
  storage/logs/queue.log (if configured)

Web Server Logs:
  /var/log/apache2/error.log (Apache)
  /var/log/nginx/error.log (Nginx)

Database Logs:
  storage/logs/database.log (if configured)
```

---

**Document Version: 1.0**
**Last Updated: 2026-08-18 06:08 UTC**
