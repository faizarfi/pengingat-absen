# DOKUMENTASI CODEBASE LENGKAP - Analisis Menyeluruh

**Tanggal Pembuatan:** 18 Agustus 2026  
**Status:** Dokumentasi Komprehensif & Up-to-Date  
**Bahasa:** Indonesia

---

## 📑 Daftar Isi

1. [Overview Proyek](#overview-proyek)
2. [Struktur Direktori & File](#struktur-direktori--file)
3. [Proyek 1: Pengingat Absen (WhatsApp Reminder)](#proyek-1-pengingat-absen)
4. [Proyek 2: Excel PDF Generator](#proyek-2-excel-pdf-generator)
5. [Stack Teknologi](#stack-teknologi)
6. [Database Schema](#database-schema)
7. [API Endpoints Lengkap](#api-endpoints-lengkap)
8. [Alur Data & Workflow](#alur-data--workflow)
9. [Common Issues & Troubleshooting](#common-issues--troubleshooting)
10. [Best Practices & Optimizations](#best-practices--optimizations)
11. [Quick Commands Reference](#quick-commands-reference)

---

## Overview Proyek

Direktori `/c/Users/USER/` berisi 2 proyek Laravel utama:

### 📱 **Pengingat Absen** (`~/pengingat-absen/`)
Sistem otomasi pengiriman reminder absen melalui WhatsApp kepada karyawan. Fitur utama:
- ✅ Dashboard admin untuk manajemen karyawan
- ✅ Import/Export employee data (Excel/CSV)
- ✅ Pengaturan waktu check-in/check-out & templates
- ✅ Pengiriman reminder manual dan terjadwal
- ✅ Pre-reminder sebelum jam target
- ✅ Append pantun (puisi) ke pesan
- ✅ Audit trail (wa_logs table)

**Status:** Production-ready dengan queue support

### 📊 **Excel PDF Generator** (`~/excel-pdf-generator/`)
Aplikasi untuk merge 2 file Excel (Progress Report & PPK) dan generate PDF laporan terpadu.
- ✅ Upload 2 file Excel
- ✅ Merge data dengan lookup table
- ✅ Generate PDF A4 Landscape
- ✅ Deteksi tipe petugas (PML/PPL)
- ✅ Normalisasi nomor dan data

**Status:** Production-ready

---

## Struktur Direktori & File

```
C:\Users\USER\
├── pengingat-absen/                    # Proyek 1: WhatsApp Reminder
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── AdminController.php     # CRUD & send logic
│   │   │   ├── AuthController.php      # Login/logout
│   │   │   └── Controller.php          # Base controller
│   │   ├── Models/
│   │   │   ├── Employee.php            # Employee model
│   │   │   ├── Setting.php             # Key-value settings
│   │   │   ├── User.php                # Auth user
│   │   │   └── WaLog.php               # WhatsApp logs
│   │   ├── Services/
│   │   │   └── WhatsAppService.php     # Provider abstraction (Foonte, WaSender, Infobip)
│   │   ├── Jobs/
│   │   │   └── SendWhatsAppJob.php     # Queue job
│   │   ├── Console/
│   │   │   ├── Commands/
│   │   │   │   ├── SendWaReminder.php  # Scheduled task
│   │   │   │   └── TestWhatsApp.php    # Test command
│   │   │   └── Kernel.php              # Schedule definition
│   │   └── Providers/
│   │       └── AppServiceProvider.php  # Service registration
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── create_employees_table.php
│   │   │   ├── create_settings_table.php
│   │   │   ├── create_wa_logs_table.php
│   │   │   └── create_pantuns_table.php
│   │   └── seeders/
│   ├── resources/views/
│   │   ├── admin/
│   │   │   └── dashboard.blade.php     # Main UI
│   │   └── layouts/
│   │       └── app.blade.php
│   ├── routes/
│   │   ├── web.php                     # Web routes
│   │   └── api.php                     # API routes (if any)
│   ├── config/
│   │   ├── whatsapp.php                # WhatsApp config
│   │   ├── database.php
│   │   └── queue.php
│   ├── .env                            # Environment variables
│   ├── composer.json                   # PHP dependencies
│   └── artisan                         # Laravel CLI
│
├── excel-pdf-generator/                # Proyek 2: Excel to PDF
│   ├── app/Http/Controllers/
│   │   ├── ProgressReportController.php # Main logic
│   │   └── Controller.php
│   ├── resources/views/
│   │   ├── index.blade.php             # Upload form
│   │   ├── pdf_template.blade.php      # PDF layout
│   │   └── layouts/
│   ├── routes/
│   │   └── web.php
│   ├── config/
│   │   └── database.php
│   ├── .env
│   ├── composer.json
│   └── artisan
│
├── DOKUMENTASI_CODEBASE.md             # Dokumen ini
├── QUICK_REFERENCE.md                  # Quick reference guide
├── API_DOCUMENTATION.md                # Detailed API docs
├── TROUBLESHOOTING_GUIDE.md            # Common issues & solutions
│
├── .cache/                             # Cache files
├── .claude/                            # Claude AI config
├── .codex/                             # Codex AI config
├── .config/                            # Config files
├── .vscode/                            # VS Code settings
└── ... (other system folders)
```

---

## Proyek 1: Pengingat Absen

### 1.1 Database Schema

#### Table: `employees`
```sql
CREATE TABLE employees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,       -- Format: 812345678 (tanpa 0 dan +62)
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_employees_active (is_active),
    INDEX idx_employees_phone (phone_number)
);
```

**Contoh Data:**
```
id | name           | phone_number | is_active | created_at
1  | Budi Santoso   | 812345678    | true      | 2026-08-01 10:30:00
2  | Siti Nurhaliza | 813456789    | true      | 2026-08-01 10:31:00
3  | Ahmad Ridho    | 814567890    | false     | 2026-08-01 10:32:00
```

#### Table: `settings` (Key-Value Store)
```sql
CREATE TABLE settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) NOT NULL UNIQUE,
    value LONGTEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_settings_key (key)
);
```

**Predefined Keys:**
```
check_in_time              → "07:30"
check_out_time             → "16:00"
pre_reminder_minutes       → "30"
template_checkin           → "Halo {name}, sudah waktunya absen pagi..."
template_checkout          → "Halo {name}, sudah waktunya absen pulang..."
template_pre_checkin       → "Halo {name}, 30 menit lagi waktunya..."
closing_word               → "Semangat kerja!"
organization_name          → "BPS Kabupaten Karanganyar"
whatsapp_provider          → "foonte" | "wasender" | "infobip"
```

#### Table: `wa_logs` (Audit Trail)
```sql
CREATE TABLE wa_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT,
    type ENUM('manual', 'checkin', 'checkout', 'pre_checkin', 'pre_checkout'),
    status ENUM('pending', 'sent', 'failed'),
    message TEXT,
    scheduled_at DATETIME NULL,
    sent_at DATETIME NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_wa_logs_employee (employee_id),
    INDEX idx_wa_logs_status (status),
    INDEX idx_wa_logs_created (created_at)
);
```

#### Table: `pantuns` (Poetry Collection)
```sql
CREATE TABLE pantuns (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('masuk', 'pulang'),
    text LONGTEXT NOT NULL,
    created_at TIMESTAMP,
    
    INDEX idx_pantuns_type (type)
);
```

**Contoh Data:**
```
id | type  | text
1  | masuk | "Habang nagtayo sa lapag, Tina ang dugo ng bayani..."
2  | masuk | "Pagi yang cerah menyingsing terang..."
3  | pulang| "Pulang kerja dengan penuh lelah, Jangan lupa istirahat..."
```

---

### 1.2 Models & Relationships

#### Employee Model
```php
class Employee extends Model
{
    protected $fillable = ['name', 'phone_number', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    
    // Relationships
    public function waLogs() {
        return $this->hasMany(WaLog::class);
    }
    
    // Scopes
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
```

#### Setting Model (Key-Value)
```php
class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    protected $primaryKey = 'key';
    public $incrementing = false;
    public $keyType = 'string';
    
    // Static helper
    public static function get($key, $default = null) {
        return static::where('key', $key)->first()?->value ?? $default;
    }
    
    public static function set($key, $value) {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
```

#### WaLog Model
```php
class WaLog extends Model
{
    protected $fillable = ['employee_id', 'type', 'status', 'message', 'scheduled_at', 'sent_at', 'error_message'];
    protected $casts = ['scheduled_at' => 'datetime', 'sent_at' => 'datetime'];
    
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
```

---

### 1.3 Core Services

#### WhatsAppService
**File:** `app/Services/WhatsAppService.php`

**Main Method:** `sendMessage(string $phone, string $message): array`

```php
/**
 * Mengirim pesan WhatsApp
 * 
 * @param string $phone Nomor telepon (format: 812345678 atau 62812345678)
 * @param string $message Isi pesan
 * @return array ['success' => bool, 'status' => int, 'error' => string|null, 'retry_after' => int|null]
 */
public function sendMessage(string $phone, string $message): array {
    // 1. Normalisasi nomor telepon
    $phone = $this->normalizePhone($phone);
    
    // 2. Validasi konfigurasi
    $baseUrl = config('whatsapp.url');
    $token = config('whatsapp.key');
    if (!$baseUrl || !$token) {
        return ['success' => false, 'error' => 'config_missing'];
    }
    
    // 3. Deteksi provider & prepare payload
    $provider = config('whatsapp.provider', 'foonte');
    $payload = $this->buildPayload($provider, $phone, $message);
    $headers = $this->buildHeaders($provider, $token);
    
    // 4. Kirim HTTP POST
    try {
        $response = Http::withHeaders($headers)
            ->withOptions(['verify' => app()->isProduction()])
            ->post($baseUrl . $payload['endpoint'], $payload['body']);
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'exception', 'message' => $e->getMessage()];
    }
    
    // 5. Handle response
    if ($response->status() === 429) {
        return ['success' => false, 'error' => 'rate_limited', 'retry_after' => 60];
    }
    
    if ($response->status() === 401) {
        return ['success' => false, 'error' => 'auth_failed', 'status' => 401];
    }
    
    if ($response->status() === 200) {
        $data = $response->json();
        if ($data['success'] ?? false) {
            return ['success' => true, 'status' => 200];
        }
    }
    
    return ['success' => false, 'error' => 'unknown', 'status' => $response->status()];
}

private function normalizePhone(string $phone): string {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (strpos($phone, '+') === 0) $phone = substr($phone, 1);
    if (strpos($phone, '0') === 0) $phone = substr($phone, 1);
    return $phone;
}
```

**Provider Support:**
- **Foonte:** `https://api.fonnte.com/send` (Header: Authorization)
- **WaSender:** `https://api.wasender.com/api/send-message` (Header: Authorization: Bearer)
- **Infobip:** `https://api.infobip.com/whatsapp/1/message/text` (Header: Authorization: App)

---

### 1.4 Controllers - Key Methods

#### AdminController

##### `index()` - Dashboard
```php
public function index() {
    $employees = Employee::all();
    
    // Get latest status per employee
    $employeeStatuses = [];
    foreach ($employees as $emp) {
        $latestLog = $emp->waLogs()->latest('sent_at')->first();
        if ($latestLog?->sent_at) {
            $employeeStatuses[$emp->id] = [
                'label' => 'Sudah terkirim',
                'variant' => 'success',
                'detail' => $latestLog->sent_at->format('H:i:s')
            ];
        }
    }
    
    return view('admin.dashboard', [
        'employees' => $employees,
        'employeeStatuses' => $employeeStatuses,
        'checkIn' => Setting::get('check_in_time', '07:30'),
        'checkOut' => Setting::get('check_out_time', '16:00'),
        'preReminderMinutes' => Setting::get('pre_reminder_minutes', '30'),
        'templateIn' => Setting::get('template_checkin'),
        'templateOut' => Setting::get('template_checkout'),
        'kata' => Setting::get('closing_word', 'Semangat kerja!')
    ]);
}
```

##### `storeEmployee(Request $request)` - Tambah Karyawan
```php
public function storeEmployee(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:20'
    ]);
    
    Employee::create([
        'name' => $validated['name'],
        'phone_number' => $this->normalizePhone($validated['phone_number']),
        'is_active' => true
    ]);
    
    return redirect()->route('admin.index')->with('success', 'Karyawan berhasil ditambahkan');
}
```

##### `importEmployees(Request $request)` - Import dari Excel
```php
public function importEmployees(Request $request) {
    $file = $request->validate(['employee_file' => 'required|file|mimes:csv,xlsx,xls,ods|max:5120'])['employee_file'];
    
    $rows = Excel::toArray([], $file)[0];
    $imported = 0;
    $skipped = 0;
    
    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // Skip header
        
        $name = $row[0] ?? null;
        $phone = $row[1] ?? null;
        
        if (!$name || !$phone) {
            $skipped++;
            continue;
        }
        
        Employee::create([
            'name' => $name,
            'phone_number' => $this->normalizePhone($phone),
            'is_active' => true
        ]);
        $imported++;
    }
    
    return redirect()->back()->with('success', "Import: $imported berhasil, $skipped dilewati");
}
```

##### `sendNow()` - Kirim Langsung
```php
public function sendNow() {
    $employees = Employee::active()->get();
    $checkInTime = Setting::get('check_in_time', '07:30');
    $templates = $this->getSampleTemplates();
    $closingWord = Setting::get('closing_word', 'Semangat kerja!');
    
    $sent = 0;
    
    foreach ($employees as $emp) {
        $template = $templates[array_rand($templates)];
        $message = str_replace(
            ['{name}', '{target_time}', '{kata}'],
            [$emp->name, $checkInTime, $closingWord],
            $template
        );
        
        $log = WaLog::create([
            'employee_id' => $emp->id,
            'type' => 'manual',
            'status' => 'pending',
            'message' => $message
        ]);
        
        SendWhatsAppJob::dispatchSync($log->id, $emp->id, $emp->phone_number, $message, 'manual');
        $sent++;
    }
    
    return redirect()->back()->with('success', "Pesan dikirim ke $sent karyawan");
}
```

##### `sendPreCheckinNow()` - Pre-reminder Masuk
```php
public function sendPreCheckinNow() {
    $employees = Employee::active()->get();
    $checkInTime = Setting::get('check_in_time', '07:30');
    $template = Setting::get('template_pre_checkin', 'Halo {name}, {minutes_left} menit lagi...');
    
    $now = Carbon::now();
    $target = Carbon::createFromFormat('H:i', $checkInTime);
    $targetToday = $target->setDate($now->year, $now->month, $now->day);
    $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));
    
    $sent = 0;
    
    foreach ($employees as $emp) {
        $message = str_replace(['{name}', '{minutes_left}'], [$emp->name, $minutesLeft], $template);
        
        // Append random pantun
        $pantun = DB::table('pantuns')->where('type', 'masuk')->inRandomOrder()->first();
        if ($pantun) {
            $message .= "\n\n" . $pantun->text;
        }
        
        $log = WaLog::create([
            'employee_id' => $emp->id,
            'type' => 'pre_checkin',
            'status' => 'pending',
            'message' => $message
        ]);
        
        SendWhatsAppJob::dispatchSync($log->id, $emp->id, $emp->phone_number, $message, 'pre_checkin');
        $sent++;
    }
    
    return redirect()->back()->with('success', "Pre-reminder dikirim ke $sent karyawan. Sisa: $minutesLeft menit");
}
```

---

### 1.5 Job Queue

#### SendWhatsAppJob
```php
class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $logId;
    protected $employeeId;
    protected $phone;
    protected $message;
    protected $type;
    
    public function __construct($logId, $employeeId, $phone, $message, $type) {
        $this->logId = $logId;
        $this->employeeId = $employeeId;
        $this->phone = $phone;
        $this->message = $message;
        $this->type = $type;
    }
    
    public function handle(WhatsAppService $service) {
        $log = WaLog::find($this->logId);
        $result = $service->sendMessage($this->phone, $this->message);
        
        if ($result['success']) {
            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } else {
            $log->update(['status' => 'failed', 'error_message' => $result['error']]);
            
            // Retry jika rate limited
            if ($result['error'] === 'rate_limited') {
                $this->release(($result['retry_after'] ?? 60) + 10);
            }
        }
    }
}
```

---

### 1.6 Scheduled Commands

#### SendWaReminder Command
```php
class SendWaReminder extends Command
{
    protected $signature = 'send:wa-reminder';
    
    public function handle() {
        $checkInTime = Setting::get('check_in_time', '07:30');
        $checkOutTime = Setting::get('check_out_time', '16:00');
        $now = Carbon::now();
        
        // Check if it's time for check-in
        if ($now->format('H:i') === $checkInTime) {
            $this->sendReminders('checkin');
        }
        
        // Check if it's time for check-out
        if ($now->format('H:i') === $checkOutTime) {
            $this->sendReminders('checkout');
        }
        
        $this->info('Reminder task completed');
    }
    
    private function sendReminders($type) {
        $employees = Employee::active()->get();
        foreach ($employees as $emp) {
            SendWhatsAppJob::dispatch(...);
        }
    }
}
```

**Jadwal di `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule) {
    $schedule->command('send:wa-reminder')
        ->everyMinute()
        ->runInBackground();
}
```

---

### 1.7 Routes

```php
// routes/web.php

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    
    // Employee Management
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::patch('/employees/{id}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [AdminController::class, 'deleteEmployee'])->name('employees.destroy');
    
    // Import/Export
    Route::post('/employees/import', [AdminController::class, 'importEmployees'])->name('employees.import');
    Route::get('/employees/export', [AdminController::class, 'exportEmployees'])->name('employees.export');
    
    // Settings
    Route::post('/settings', [AdminController::class, 'updateSetting'])->name('settings.update');
    Route::post('/set-default-times', [AdminController::class, 'setDefaultTimes'])->name('settings.defaults');
    
    // Sending
    Route::post('/send-now', [AdminController::class, 'sendNow'])->name('send.now');
    Route::post('/send-pre-checkin', [AdminController::class, 'sendPreCheckinNow'])->name('send.pre_checkin');
    Route::post('/send-pre-checkout', [AdminController::class, 'sendPreCheckoutNow'])->name('send.pre_checkout');
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

---

## Proyek 2: Excel PDF Generator

### 2.1 Fungsi Utama

#### ProgressReportController

##### `index()` - Upload Form
```php
public function index() {
    return view('index');
}
```

##### `process(Request $request)` - Generate PDF
```php
public function process(Request $request) {
    $validated = $request->validate([
        'file_progress' => 'required|mimes:xlsx,xls',
        'file_ppk' => 'required|mimes:xlsx,xls',
        'action' => 'nullable|string'
    ]);
    
    // Parse files
    $progressRows = Excel::toArray([], $validated['file_progress'])[0];
    $ppkRows = Excel::toArray([], $validated['file_ppk'])[0];
    
    // Merge data
    $mergedData = $this->buildMergedReport($progressRows, $ppkRows);
    
    // Generate PDF
    $pdf = Pdf::loadView('pdf_template', ['items' => $mergedData]);
    $pdf->setPaper('a4', 'landscape');
    
    return $pdf->download('Beban_Kerja_Petugas_SE2026.pdf');
}
```

##### `buildMergedReport(array $progressRows, array $ppkRows): array`
**Alur Merge Logic:**

1. **Build PPK Index** - Create lookup dari PPK data
```php
$ppkIndex = $this->buildPpkIndex($ppkRows);
// Result: ["0001-01-12" => ["target" => 100, "realisasi" => 85]]
```

2. **Loop Progress Rows** - Untuk setiap row:
   - Skip header (index 0) & empty rows
   - Deteksi tipe petugas (PML/PPL)
   - Extract data: nama, partner, kode geografis
   - Extract target & realisasi
   - Cari di PPK index untuk override
   - Build output row

```php
public function buildMergedReport(array $progressRows, array $ppkRows): array {
    $ppkIndex = $this->buildPpkIndex($ppkRows);
    $result = [];
    
    foreach ($progressRows as $index => $row) {
        if ($index === 0 || empty(array_filter($row))) continue;
        
        $petugasType = $this->detectPetugasType($row);
        
        $pmlNama = $this->getRowValue($row, [1, 0, 2]);
        $pmlSobat = $this->getRowValue($row, [2, 3, 1]);
        $pplNama = $petugasType === 'PML' ? '' : $this->getRowValue($row, [3, 4, 1]);
        $pplSobat = $petugasType === 'PML' ? '' : $this->getRowValue($row, [4, 5, 2]);
        
        $kodeKec = $this->getRowValue($row, [5]);
        $kodeDesa = $this->getRowValue($row, [6]);
        $kodeSls = $this->getRowValue($row, [7]);
        
        $target = $this->normalizeNumber($this->getRowValue($row, [8]));
        $realisasi = $this->normalizeNumber($this->getRowValue($row, [9]));
        
        $key = $this->resolveRowKey($kodeSls, $kodeDesa, $kodeKec);
        
        // Override dengan PPK jika ada
        if ($key && isset($ppkIndex[$key])) {
            $target = $ppkIndex[$key]['target'];
            $realisasi = $ppkIndex[$key]['realisasi'];
        }
        
        $result[] = [
            'pml_nama' => $pmlNama,
            'pml_sobat' => $pmlSobat,
            'ppl_nama' => $pplNama,
            'ppl_sobat' => $pplSobat,
            'kode_kec' => $kodeKec,
            'kode_desa' => $kodeDesa,
            'kode_sls' => $kodeSls,
            'target' => $target,
            'realisasi' => $realisasi,
            'keterangan' => $this->getRowValue($row, [11])
        ];
    }
    
    return $result;
}
```

##### Helper Methods
```php
private function buildPpkIndex(array $rows): array {
    $index = [];
    foreach ($rows as $row) {
        if (empty($row)) continue;
        $key = $this->resolveRowKey($row[7], $row[6], $row[5]);
        if ($key) {
            $index[$key] = [
                'target' => (int) ($row[8] ?? 0),
                'realisasi' => (int) ($row[9] ?? 0)
            ];
        }
    }
    return $index;
}

private function detectPetugasType(array $row): string {
    $rowText = implode(' ', $row);
    if (strpos($rowText, 'PML') !== false) return 'PML';
    if (strpos($rowText, 'PPL') !== false) return 'PPL';
    return 'PML';
}

private function getRowValue(array $row, array $indexes): string {
    foreach ($indexes as $idx) {
        if (!empty($row[$idx])) return (string) $row[$idx];
    }
    return '';
}

private function normalizeNumber($value): int {
    $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
    return (int) $cleaned ?: 0;
}

private function resolveRowKey($kodeSls, $kodeDesa, $kodeKec): ?string {
    if (!$kodeSls || !$kodeDesa || !$kodeKec) return null;
    return "$kodeSls-$kodeDesa-$kodeKec";
}
```

---

## Stack Teknologi

### Backend
- **Framework:** Laravel 12.0
- **Database:** SQLite / MySQL
- **Queue:** Redis / Database
- **HTTP Client:** Laravel HTTP Client
- **PDF:** Barryvdh DomPDF
- **Excel:** Maatwebsite Excel

### Frontend
- **Templating:** Blade
- **CSS:** Tailwind CSS / Bootstrap
- **JavaScript:** Alpine.js / Vue.js

### External Services
- **WhatsApp API Providers:**
  - Foonte (https://fonnte.com)
  - WaSender (https://wasender.com)
  - Infobip (https://infobip.com)

### DevTools
- **Version Control:** Git
- **Package Manager:** Composer (PHP), NPM (Node)
- **Testing:** PHPUnit / Pest
- **IDE:** VS Code / PhpStorm

---

## API Endpoints Lengkap

### Pengingat Absen

#### Employee Management
```
POST   /employees
       Request: {"name": "Budi", "phone_number": "0812345678"}
       Response: {"status": "success", "message": "..."}

PATCH  /employees/{id}
       Request: {"name": "Budi Updated", "phone_number": "0812345679", "is_active": true}
       Response: {"status": "success", ...}

DELETE /employees/{id}
       Response: {"status": "success", ...}

POST   /employees/import
       Request: multipart/form-data (file: employee_file.xlsx)
       Response: {"status": "success", "message": "45 imported, 2 skipped"}

GET    /employees/export
       Response: CSV file download (employees-YYYYMMDD-HHmmss.csv)
```

#### Settings Management
```
POST   /settings
       Request: {
         "check_in_time": "07:30",
         "check_out_time": "16:00",
         "pre_reminder_minutes": 30,
         "template_checkin": "...",
         "closing_word": "Semangat kerja!"
       }
       Response: {"status": "success", ...}

POST   /set-default-times
       Response: {"status": "success", "message": "Default times saved"}
```

#### Message Sending
```
POST   /send-now
       Response: {"status": "success", "message": "Sent to 42/45 employees"}

POST   /send-pre-checkin
       Response: {"status": "success", "message": "Pre-reminder sent to 45/45. 28 minutes left"}

POST   /send-pre-checkout
       Response: {"status": "success", "message": "Pre-reminder sent to 45/45. 356 minutes left"}
```

### Excel PDF Generator
```
GET    /
       Response: Upload form view

POST   /process
       Request: multipart/form-data {
         "file_progress": Excel file,
         "file_ppk": Excel file,
         "action": "pdf"
       }
       Response: PDF file download
```

---

## Alur Data & Workflow

### Workflow 1: Manual Send Immediate (Pengingat Absen)

```
┌──────────────────────────────────────────────────────┐
│ USER: Click "Send Now" Button                        │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ AdminController::sendNow()                           │
│ 1. Query: SELECT * FROM employees WHERE is_active   │
│ 2. For each employee:                               │
│    - Pick random template                            │
│    - Replace placeholders: {name}, {kata}, etc      │
│    - Create WaLog record (status: pending)          │
│    - Dispatch SendWhatsAppJob (SYNC)               │
│    - Increment counter                              │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ SendWhatsAppJob::handle()                            │
│ 1. Call WhatsAppService::sendMessage()              │
│ 2. Handle response                                   │
│ 3. Update WaLog: status = 'sent' or 'failed'       │
│ 4. If rate_limited: release() with delay            │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ WhatsAppService::sendMessage()                       │
│ 1. Normalize phone: 0812345678 → 812345678         │
│ 2. Validate config (URL & token)                    │
│ 3. Build provider-specific payload                  │
│ 4. HTTP POST to WhatsApp provider API               │
│ 5. Handle response (200, 429, 401, etc)            │
│ 6. Return result array                              │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ WhatsApp Provider (Foonte/WaSender/Infobip)         │
│ 1. Validate API key                                  │
│ 2. Check phone format                                │
│ 3. Queue message                                     │
│ 4. Send to WhatsApp endpoint                        │
│ 5. Return status                                     │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ DATABASE UPDATES                                     │
│ - WaLog: status = 'sent', sent_at = now()           │
│ - Message delivered                                  │
└──────────────────────────────────────────────────────┘
```

### Workflow 2: Scheduled Reminder (Pengingat Absen)

```
┌──────────────────────────────────────────────────────┐
│ SCHEDULER: Minute = check_in_time (e.g., 07:30)    │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ Kernel.php: $schedule->command('send:wa-reminder')  │
│            →everyMinute()                            │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ SendWaReminder Command                               │
│ 1. Get check_in_time & check_out_time from settings │
│ 2. If now = check_in_time:                          │
│    - Loop active employees                          │
│    - Dispatch SendWhatsAppJob (type: 'checkin')    │
│ 3. If now = check_out_time: (same for checkout)    │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ Job Queue (async execution)                          │
│ - Queue worker: php artisan queue:listen            │
│ - Process jobs from queue table/Redis               │
│ - Execute SendWhatsAppJob::handle()                 │
└──────────────────────────────────────────────────────┘
```

### Workflow 3: Pre-Reminder with Pantun (Pengingat Absen)

```
┌──────────────────────────────────────────────────────┐
│ USER: Click "Send Pre-Reminder Masuk"              │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ AdminController::sendPreCheckinNow()                │
│ 1. Get template_pre_checkin from settings           │
│ 2. Calculate: minutesLeft = (07:30 - now) minutes  │
│ 3. For each employee:                              │
│    - Replace {name}, {minutes_left} placeholders   │
│    - Query: SELECT * FROM pantuns WHERE type='masuk' │
│    - Append random pantun to message               │
│    - Create WaLog record                            │
│    - Dispatch SendWhatsAppJob (SYNC)               │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ Example Message:                                     │
│                                                      │
│ Halo Budi, 28 menit lagi waktunya absen masuk.      │
│                                                      │
│ "Pagi yang cerah, pagi yang indah,                  │
│  Matahari terbit menyingsing terang..."            │
│                                                      │
│ Semangat kerja!                                      │
└──────────────────────────────────────────────────────┘
```

### Workflow 4: Excel to PDF Merge (Excel PDF Generator)

```
┌──────────────────────────────────────────────────────┐
│ USER: Upload 2 Excel files                          │
│ 1. file_progress (Progress Report)                  │
│ 2. file_ppk (PPK Report)                            │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ ProgressReportController::process()                 │
│ 1. Validate file format & size                      │
│ 2. Parse both files: Excel::toArray()              │
│ 3. Call buildMergedReport($progressRows, $ppkRows) │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ buildMergedReport() - Merge Logic                   │
│ 1. ppkIndex = buildPpkIndex($ppkRows)              │
│    → ["0001-01-12" => {target: 100, realisasi: 85}] │
│ 2. For each progress row:                          │
│    a. Detect PML or PPL                            │
│    b. Extract: names, partners, codes              │
│    c. Extract: target, realisasi                   │
│    d. Lookup in ppkIndex & override if found       │
│    e. Add to result array                          │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ PDF Generation                                       │
│ 1. Load view: pdf_template with $items data        │
│ 2. Pdf::loadView() → Render HTML                   │
│ 3. Set paper: A4 Landscape                         │
│ 4. Download: Beban_Kerja_Petugas_SE2026.pdf       │
└──────────────────────────────────────────────────────┘
          ↓
┌──────────────────────────────────────────────────────┐
│ USER: PDF file downloaded                           │
└──────────────────────────────────────────────────────┘
```

---

## Common Issues & Troubleshooting

### Issue 1: WhatsApp Messages Not Sending

**Symptoms:** Messages stuck in "pending" status

**Solutions:**
1. Check `.env` configuration
   ```bash
   WHATSAPP_URL=https://api.fonnte.com
   WHATSAPP_KEY=your_actual_token
   ```

2. Verify queue worker running
   ```bash
   ps aux | grep "queue:listen"
   # If not, start: php artisan queue:listen
   ```

3. Test WhatsAppService directly
   ```php
   php artisan tinker
   >>> $svc = app(App\Services\WhatsAppService::class);
   >>> $result = $svc->sendMessage('62812345678', 'Test');
   >>> dd($result);
   ```

4. Check wa_logs table
   ```php
   >>> DB::table('wa_logs')->latest()->first();
   ```

5. Check SSL errors
   ```
   Error: "cURL error 60: SSL certificate problem"
   Fix: Update CA certificates or disable SSL verification in local environment
   ```

### Issue 2: Import Excel Fails

**Symptoms:** "File kosong atau tidak dapat dibaca"

**Solutions:**
1. Check file format (must be: csv, xlsx, xls, ods)
2. Check file size (max 5MB)
3. Ensure column format:
   ```
   Row 0: Nama Pegawai | Nomor Telepon
   Row 1+: Data rows
   ```
4. Format phone numbers as TEXT in Excel
5. Remove empty rows

### Issue 3: Scheduled Commands Not Running

**Symptoms:** Pre-reminders not sent at scheduled time

**Solutions:**
1. Check scheduler is running
   ```bash
   # Linux/Mac: Add to crontab
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   
   # Docker/Windows: Check if command is registered
   php artisan schedule:list
   ```

2. Verify command is defined in `Kernel.php`
3. Test command manually
   ```bash
   php artisan send:wa-reminder
   ```

### Issue 4: PDF Generation Empty or Corrupted

**Symptoms:** PDF downloads but is blank

**Solutions:**
1. Check view exists: `resources/views/pdf_template.blade.php`
2. Verify data passed to template
   ```php
   dd($processedData);  // Add before PDF generation
   ```
3. Check Blade syntax errors
   ```blade
   {{ $item->name ?? 'N/A' }}  // Use null coalescing
   ```
4. Increase memory limit
   ```php
   ini_set('memory_limit', '512M');
   ```

---

## Best Practices & Optimizations

### Database Optimization
```sql
-- Add indexes for common queries
CREATE INDEX idx_employees_active ON employees(is_active);
CREATE INDEX idx_wa_logs_employee ON wa_logs(employee_id);
CREATE INDEX idx_wa_logs_created ON wa_logs(created_at);
CREATE INDEX idx_settings_key ON settings(key);
CREATE INDEX idx_pantuns_type ON pantuns(type);
```

### Queue Optimization
```php
// Use daemon worker for production
php artisan queue:work --daemon --queue=default

// Monitor queue
php artisan queue:monitor

// Set retry policy
php artisan queue:failed:forget {id}
```

### Rate Limiting
```php
// Add delay between sends
SendWhatsAppJob::dispatch(...)
    ->delay(now()->addSeconds(5));

// Exponential backoff for retries
// Already handled in SendWhatsAppJob::handle()
```

### File Upload Optimization
```php
// Clean up temp files after processing
Storage::deleteDirectory('temp/uploads');

// Implement progress tracking
// Use chunked uploads for large files
```

### Caching Settings
```php
// Cache settings to avoid repeated DB queries
Cache::remember('settings', 3600, function () {
    return Setting::all()->keyBy('key')->toArray();
});
```

---

## Quick Commands Reference

### Pengingat Absen

```bash
# Setup
cd ~/pengingat-absen
composer install
php artisan migrate --force
npm install && npm run build

# Development
php artisan serve                    # :8000
php artisan queue:listen            # Terminal 2
npm run dev                          # Terminal 3

# Testing
php artisan tinker
>>> $emp = App\Models\Employee::first();
>>> app(App\Services\WhatsAppService::class)->sendMessage($emp->phone_number, 'Test');
>>> DB::table('wa_logs')->orderByDesc('created_at')->limit(5)->get();

# Database
php artisan migrate:reset
php artisan migrate --seed
php artisan db:seed

# Commands
php artisan send:wa-reminder        # Test scheduler
php artisan test                    # Run tests
php artisan optimize                # Optimize for production
```

### Excel PDF Generator

```bash
# Setup
cd ~/excel-pdf-generator
composer install
php artisan migrate --force
npm install && npm run build

# Development
php artisan serve
npm run dev

# Testing
# Upload sample files via UI at http://localhost:8000
```

### Git & Deployment

```bash
# Branch management
git checkout -b feature/new-feature
git push -u origin feature/new-feature

# Deployment
php artisan down                    # Maintenance mode
php artisan migrate --force
php artisan config:cache
php artisan view:cache
php artisan up                      # Back online

# Rollback
php artisan migrate:rollback
git revert {commit}
```

---

## Environment Configuration

### .env (Pengingat Absen)
```env
APP_NAME="Pengingat Absen"
APP_ENV=production
APP_KEY=base64:xxxxx
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

WHATSAPP_URL=https://api.fonnte.com
WHATSAPP_KEY=your_token_here
WHATSAPP_FROM=business_account_id
WHATSAPP_PROVIDER=foonte

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
MAIL_ENCRYPTION=tls
```

### .env (Excel PDF Generator)
```env
APP_NAME="Excel PDF Generator"
APP_ENV=production
APP_KEY=base64:xxxxx
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

FILE_UPLOAD_MAX=50M
```

---

## File Summary

| File | Purpose | Status |
|------|---------|--------|
| `DOKUMENTASI_CODEBASE_LENGKAP.md` | Komprehensif documentation (ini) | ✅ Complete |
| `QUICK_REFERENCE.md` | Quick function reference | ✅ Available |
| `API_DOCUMENTATION.md` | Detailed API specs | ✅ Available |
| `TROUBLESHOOTING_GUIDE.md` | Common issues & solutions | ✅ Available |

---

## Last Updated
**Date:** 18 Agustus 2026  
**Version:** 1.0  
**Author:** Generated by Documentation Generator

---

## Contact & Support

**WhatsApp Providers:**
- Foonte: https://fonnte.com/
- WaSender: https://wasender.com/
- Infobip: https://www.infobip.com/

**Laravel Docs:**
- Official: https://laravel.com/docs
- Jobs & Queues: https://laravel.com/docs/queues
- Database: https://laravel.com/docs/database

**PHP Documentation:**
- Official: https://www.php.net/
- Laravel API: https://laravel.com/api/

---

**End of Documentation**
