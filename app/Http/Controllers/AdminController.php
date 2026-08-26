<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Jobs\SendWhatsAppJob;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\WaOutbox;
use App\Models\WaAgentHeartbeat;
use App\Services\WhatsAppService;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        $latestLogs = [];

        if ($employees->isNotEmpty()) {
            $latestLogs = DB::table('wa_logs')
                ->whereIn('employee_id', $employees->pluck('id')->all())
                ->orderByDesc('created_at')
                ->get()
                ->unique('employee_id')
                ->keyBy('employee_id');
        }

        $employeeStatuses = [];
        $now = now();

        foreach ($employees as $emp) {
            $log = $latestLogs[$emp->id] ?? null;

            if ($log) {
                $logStatus = property_exists($log, 'status') ? $log->status : null;

                if ($logStatus === null) {
                    if (property_exists($log, 'sent_at') && $log->sent_at !== null) {
                        $logStatus = 'sent';
                    } elseif (property_exists($log, 'scheduled_at') && $log->scheduled_at !== null) {
                        $logStatus = 'pending';
                    }
                }

                if ($logStatus === 'sent') {
                    $employeeStatuses[$emp->id] = [
                        'label' => 'Sudah terkirim',
                        'variant' => 'success',
                        'detail' => property_exists($log, 'sent_at') && $log->sent_at ? \Illuminate\Support\Carbon::parse($log->sent_at)->format('H:i:s') : null,
                    ];
                } elseif ($logStatus === 'pending') {
                    $scheduled = property_exists($log, 'scheduled_at') && $log->scheduled_at ? \Illuminate\Support\Carbon::parse($log->scheduled_at) : null;
                    $remain = $scheduled ? max(0, $scheduled->diffInSeconds($now, false)) : null;
                    $employeeStatuses[$emp->id] = [
                        'label' => 'Menunggu antrean',
                        'variant' => 'warning',
                        'detail' => $remain !== null ? "{$remain} detik" : 'Menunggu...',
                    ];
                } elseif ($logStatus === 'failed') {
                    $employeeStatuses[$emp->id] = [
                        'label' => 'Gagal kirim',
                        'variant' => 'danger',
                        'detail' => property_exists($log, 'sent_at') && $log->sent_at ? \Illuminate\Support\Carbon::parse($log->sent_at)->format('H:i:s') : null,
                    ];
                }
            }
        }

        $checkIn = Setting::get('check_in_time', '07:30');
        $checkOut = Setting::get('check_out_time', '16:00');
        $checkOutFriday = Setting::get('check_out_time_friday', '16:30');
        $todayCheckOut = now()->isFriday() ? $checkOutFriday : $checkOut;

        $templateIn = Setting::get('template_checkin', 'Halo {name}, sudah waktunya absen pagi. Silahkan jangan lupa absen ya. {kata}');
        $templateOut = Setting::get('template_checkout', 'Halo {name}, sudah waktunya absen pulang. Silahkan jangan lupa absen ya. {kata}');
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $organization = Setting::get('organization_name', 'BPS Kabupaten Karanganyar');
        $preReminderMinutes = Setting::get('pre_reminder_minutes', 30);
        $templatePreCheckin = Setting::get('template_pre_checkin', "{name},\n\nIni adalah pengingat absen masuk.\nJam masuk kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon segera lakukan absen masuk. Jangan lupa absen ya!\n\nTerima kasih atas perhatian Anda.\n\nHormat kami,\n{organization}");
        $templatePreCheckout = Setting::get('template_pre_checkout', "{name},\n\nIni adalah pengingat absen pulang.\nJam pulang kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon jangan lupa melakukan absen pulang sebelum meninggalkan kantor.\n\nTerima kasih atas dedikasi dan kerja keras Anda hari ini.\n\nHormat kami,\n{organization}");

        $templateBroadcast = Setting::get('template_broadcast', "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}");

        $totalActive = $employees->where('is_active', true)->count();
        $totalSentToday = DB::table('wa_logs')
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'sent')
            ->count();

        // ── Agent Monitoring ──
        $agent = WaAgentHeartbeat::where('agent_name', 'default')->first();
        $agentStatus = $agent ? ($agent->isOnline() ? 'online' : 'offline') : 'not_configured';
        $whatsappReady = $agent ? $agent->whatsapp_ready : false;
        $agentLastSeen = $agent?->last_seen_at;

        // ── Outbox Stats ──
        $outboxStats = [
            'pending'    => WaOutbox::pending()->count(),
            'processing' => WaOutbox::processing()->count(),
            'sent_today' => WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count(),
            'failed'     => WaOutbox::failed()->today()->count(),
            'retry'      => WaOutbox::retry()->count(),
        ];

        $waDriver = config('whatsapp.driver', 'desktop');

        // ── Recent Outbox Messages (Paginate 5 items per page) ──
        $outboxMessages = WaOutbox::with('employee')
            ->orderBy('id', 'desc')
            ->paginate(5, ['*'], 'outbox_page')
            ->fragment('outbox-section');

        $holidayService = app(\App\Services\HolidayService::class);
        $todayHoliday = $holidayService->getHolidayInfo(now());
        $isTodayHoliday = $holidayService->isHoliday(now());
        $todayHolidayName = $todayHoliday ? $todayHoliday->name : (now()->isWeekend() ? 'Libur Akhir Pekan (' . (now()->isSaturday() ? 'Sabtu' : 'Minggu') . ')' : null);

        $holidaysJson = \App\Models\Holiday::all()->mapWithKeys(function ($h) {
            return [$h->date->format('Y-m-d') => [
                'name' => $h->name,
                'is_national' => (bool) $h->is_national_holiday
            ]];
        })->toJson();

        $upcomingHolidays = \App\Models\Holiday::where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'employees',
            'checkIn',
            'checkOut',
            'checkOutFriday',
            'todayCheckOut',
            'templateIn',
            'templateOut',
            'kata',
            'organization',
            'employeeStatuses',
            'preReminderMinutes',
            'templatePreCheckin',
            'templatePreCheckout',
            'templateBroadcast',
            'totalActive',
            'totalSentToday',
            'agentStatus',
            'whatsappReady',
            'agentLastSeen',
            'outboxStats',
            'waDriver',
            'outboxMessages',
            'isTodayHoliday',
            'todayHolidayName',
            'holidaysJson',
            'upcomingHolidays'
        ));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'panggilan' => 'nullable|string|max:50',
            'phone_number' => 'required|string',
        ]);

        $data['panggilan'] = $data['panggilan'] ?? 'Yth.';
        Employee::create($data + ['is_active' => true]);

        return redirect()->back()->with('status', 'Karyawan baru berhasil ditambahkan.');
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required|string',
            'panggilan' => 'nullable|string|max:50',
            'phone_number' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['panggilan'] = $data['panggilan'] ?? 'Yth.';
        $employee->update($data);

        return redirect()->back()->with('status', 'Karyawan berhasil diperbarui.');
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->back()->with('status', 'Karyawan berhasil dihapus.');
    }

    public function importEmployees(Request $request)
    {
        try {
            if (!$request->hasFile('employee_file')) {
                return redirect()->route('admin.dashboard')->with('error', 'Silakan pilih file untuk diimport.');
            }

            $request->validate([
                'employee_file' => 'required|file|mimes:csv,xlsx,xls,ods|max:5120',
            ]);

            $file = $request->file('employee_file');
            $path = $file->getRealPath();

            \Log::info('Excel import started', [
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
            ]);

            $rows = Excel::toArray([], $path)[0] ?? [];

            if (empty($rows)) {
                return redirect()->route('admin.dashboard')->with('error', 'File kosong atau tidak dapat dibaca. Pastikan file memiliki data.');
            }

            $imported = 0;
            $skipped = 0;

            // Detect if file has 3 columns (nama, no_hp, panggilan) or 2 columns
            $hasHeaderRow = false;
            $hasPanggilanColumn = false;
            if (!empty($rows[0])) {
                $firstRow = $rows[0];
                $col0 = strtolower(trim((string) ($firstRow[0] ?? '')));
                $col1 = strtolower(trim((string) ($firstRow[1] ?? '')));
                $col2 = strtolower(trim((string) ($firstRow[2] ?? '')));
                if (preg_match('/name|nama/i', $col0) && preg_match('/phone|telepon|nomor|wa|hp/i', $col1)) {
                    $hasHeaderRow = true;
                    $hasPanggilanColumn = preg_match('/panggilan|sapaan|gelar|title/i', $col2);
                } elseif (isset($firstRow[2]) && $firstRow[2] !== '') {
                    $hasPanggilanColumn = true;
                }
            }

            foreach ($rows as $index => $row) {
                $name = trim((string) ($row[0] ?? ''));
                $phone = trim((string) ($row[1] ?? ''));
                $panggilan = $hasPanggilanColumn ? trim((string) ($row[2] ?? '')) : '';

                // Skip header row
                if ($index === 0 && $hasHeaderRow) {
                    continue;
                }

                if ($name === '' || $phone === '') {
                    $skipped++;
                    continue;
                }

                // Normalize phone number
                $phone = preg_replace('/[^0-9+]/', '', $phone);
                if (strpos($phone, '0') === 0) {
                    $phone = substr($phone, 1);
                }

                Employee::create([
                    'name' => $name,
                    'panggilan' => $panggilan !== '' ? $panggilan : 'Yth.',
                    'phone_number' => $phone,
                    'is_active' => true,
                ]);

                $imported++;
            }

            \Log::info('Excel import completed', [
                'imported' => $imported,
                'skipped' => $skipped,
            ]);

            if ($imported === 0) {
                return redirect()->route('admin.dashboard')->with('warning', 'Tidak ada data yang berhasil diimport. Cek format file Anda.');
            }

            return redirect()->route('admin.dashboard')->with('status', "Import selesai: {$imported} baris berhasil, {$skipped} baris dilewati.");
        } catch (\Exception $e) {
            \Log::error('Excel import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.dashboard')->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function exportEmployees()
    {
        $employees = Employee::all();
        $filename = 'employees-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($employees) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row in Indonesian to match template
            fputcsv($handle, ['No', 'Nama Pegawai', 'Nomor Telepon / WA', 'Panggilan']);

            $i = 1;
            foreach ($employees as $employee) {
                $phone = (string) $employee->phone_number;

                // Normalize display: if number starts with country code or +, keep; if starts with '8', prepend '0'
                if ($phone === '') {
                    $displayPhone = '';
                } elseif (strpos($phone, '+') === 0) {
                    $displayPhone = $phone;
                } elseif (strpos($phone, '0') === 0) {
                    $displayPhone = $phone;
                } elseif (strpos($phone, '8') === 0) {
                    $displayPhone = '0' . $phone;
                } else {
                    $displayPhone = '0' . $phone;
                }

                fputcsv($handle, [$i, $employee->name, $displayPhone, $employee->panggilan ?? 'Yth.']);
                $i++;
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function updateSetting(Request $request)
    {
        $data = $request->validate([
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'check_out_time_friday' => 'nullable|date_format:H:i',
            'pre_reminder_minutes' => 'nullable|integer|min:1|max:120',
        ]);

        Setting::set('check_in_time', $data['check_in_time']);
        Setting::set('check_out_time', $data['check_out_time']);
        if (isset($data['check_out_time_friday'])) {
            Setting::set('check_out_time_friday', $data['check_out_time_friday']);
        }

        if ($request->has('template_checkin')) {
            Setting::set('template_checkin', $request->input('template_checkin'));
        }
        if ($request->has('template_checkout')) {
            Setting::set('template_checkout', $request->input('template_checkout'));
        }
        if ($request->has('template_pre_checkin')) {
            Setting::set('template_pre_checkin', $request->input('template_pre_checkin'));
        }
        if ($request->has('template_pre_checkout')) {
            Setting::set('template_pre_checkout', $request->input('template_pre_checkout'));
        }
        if ($request->has('template_broadcast')) {
            Setting::set('template_broadcast', $request->input('template_broadcast'));
        }
        if ($request->has('organization_name')) {
            Setting::set('organization_name', $request->input('organization_name'));
        }
        if ($request->has('closing_word')) {
            Setting::set('closing_word', $request->input('closing_word'));
        }
        if (isset($data['pre_reminder_minutes'])) {
            Setting::set('pre_reminder_minutes', $data['pre_reminder_minutes']);
        }

        return redirect()->back()->with('status', 'Pengaturan berhasil diperbarui.');
    }

    public function sendNow(Request $request)
    {
        $wa = app(WhatsAppService::class);
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $orgName = Setting::get('organization_name', 'BPS Kabupaten Karanganyar');
        
        // Use custom message from request if submitted, otherwise fallback to template setting
        $customMessage = $request->input('message') ?: Setting::get('template_broadcast', "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}");

        $queued = 0;

        foreach ($employees as $emp) {
            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{organization}'],
                [$namaLengkap, $kata, $orgName],
                $customMessage
            );

            try {
                $wa->send($emp->id, $emp->phone_number, $text, 'manual');
                $queued++;
            } catch (\Exception $e) {
                \Log::error('sendNow failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('status', "Broadcast berhasil dimasukkan ke antrean: {$queued}/{$employees->count()} karyawan.");
    }

    public function sendPreCheckinNow(Request $request)
    {
        $wa = app(WhatsAppService::class);
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $template = Setting::get('template_pre_checkin', "{name},\n\nIni adalah pengingat absen masuk.\nJam masuk kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon segera lakukan absen masuk. Jangan lupa absen ya!\n\nTerima kasih atas perhatian Anda.\n\nHormat kami,\n{organization}");
        $checkIn = Setting::get('check_in_time', '07:30');

        $target = Carbon::createFromFormat('H:i', $checkIn);

        // compute global minutes-left for the redirect summary
        $nowGlobal = Carbon::now();
        $targetTodayGlobal = (clone $target)->setDate($nowGlobal->year, $nowGlobal->month, $nowGlobal->day);
        $minutesLeftGlobal = (int) max(0, ceil(($targetTodayGlobal->getTimestamp() - $nowGlobal->getTimestamp()) / 60));

        $queued = 0;
        foreach ($employees as $emp) {
            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);

            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            // Prepare message and replace placeholders
            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), Setting::get('organization_name', 'BPS Kabupaten Karanganyar')],
                $template
            );

            // If template doesn't include the {minutes_left} placeholder, append a fallback sentence
            if (strpos($template, '{minutes_left}') === false) {
                $fallback = "Tersisa waktu kurang lebih {$minutesLeft} menit menuju jam masuk.";
                $text = trim($text) . "\n\n" . $fallback;
            }

            // Append a random pantun of type 'masuk' after the salutation if available
            $pantun = DB::table('pantuns')->where('type', 'masuk')->inRandomOrder()->value('text');
            if ($pantun) {
                $pantun = str_replace('\\n', PHP_EOL, $pantun);

                if (str_contains($text, '{pantun}')) {
                    $text = str_replace('{pantun}', $pantun, $text);
                } else {
                    $lines = preg_split("/\r\n|\n|\r/", $text);
                    if (isset($lines[0]) && trim($lines[0]) !== '') {
                        array_splice($lines, 1, 0, ['', $pantun]);
                        $text = implode(PHP_EOL, $lines);
                    } else {
                        $text = trim($text) . PHP_EOL . PHP_EOL . $pantun;
                    }
                }
            }

            try {
                $wa->send($emp->id, $emp->phone_number, $text, 'pre_checkin');
                $queued++;
            } catch (\Exception $e) {
                \Log::error('sendPreCheckinNow failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('status', "Pengingat masuk dimasukkan ke antrean: {$queued}/{$employees->count()} karyawan. Sisa menit: {$minutesLeftGlobal}");
    }

    public function sendPreCheckoutNow(Request $request)
    {
        $wa = app(WhatsAppService::class);
        $employees = Employee::where('is_active', true)->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $template = Setting::get('template_pre_checkout', "{name},\n\nIni adalah pengingat absen pulang.\nJam pulang kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon jangan lupa melakukan absen pulang sebelum meninggalkan kantor.\n\nTerima kasih atas dedikasi dan kerja keras Anda hari ini.\n\nHormat kami,\n{organization}");
        $isFriday = now()->isFriday();
        $checkOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');

        $target = Carbon::createFromFormat('H:i', $checkOut);

        // compute global minutes-left for the redirect summary
        $nowGlobal = Carbon::now();
        $targetTodayGlobal = (clone $target)->setDate($nowGlobal->year, $nowGlobal->month, $nowGlobal->day);
        $minutesLeftGlobal = (int) max(0, ceil(($targetTodayGlobal->getTimestamp() - $nowGlobal->getTimestamp()) / 60));

        $queued = 0;
        foreach ($employees as $emp) {
            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);

            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), Setting::get('organization_name', 'BPS Kabupaten Karanganyar')],
                $template
            );

            if (strpos($template, '{minutes_left}') === false) {
                $fallback = "Tersisa waktu kurang lebih {$minutesLeft} menit menuju jam penyelesaian tugas hari ini.";
                $text = trim($text) . "\n\n" . $fallback;
            }

            // Append a random pantun of type 'pulang' after the salutation if available
            $pantun = DB::table('pantuns')->where('type', 'pulang')->inRandomOrder()->value('text');
            if ($pantun) {
                $pantun = str_replace('\\n', PHP_EOL, $pantun);

                if (str_contains($text, '{pantun}')) {
                    $text = str_replace('{pantun}', $pantun, $text);
                } else {
                    $lines = preg_split("/\r\n|\n|\r/", $text);
                    if (isset($lines[0]) && trim($lines[0]) !== '') {
                        array_splice($lines, 1, 0, ['', $pantun]);
                        $text = implode(PHP_EOL, $lines);
                    } else {
                        $text = trim($text) . PHP_EOL . PHP_EOL . $pantun;
                    }
                }
            }

            try {
                $wa->send($emp->id, $emp->phone_number, $text, 'pre_checkout');
                $queued++;
            } catch (\Exception $e) {
                \Log::error('sendPreCheckoutNow failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('status', "Pengingat pulang dimasukkan ke antrean: {$queued}/{$employees->count()} karyawan. Sisa menit: {$minutesLeftGlobal}");
    }

    public function sendSingleEmployee(Request $request, $id)
    {
        $emp = Employee::findOrFail($id);
        $type = $request->input('type', 'custom');
        $customMessage = $request->input('message');
        $wa = app(WhatsAppService::class);

        $panggilan = $emp->panggilan ?? 'Yth.';
        $namaLengkap = $panggilan . ' ' . $emp->name;
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $org = Setting::get('organization_name', 'BPS Kabupaten Karanganyar');

        if ($type === 'pre_checkin') {
            $template = Setting::get('template_pre_checkin', "{name},\n\nIni adalah pengingat absen masuk.\nJam masuk kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon segera lakukan absen masuk. Jangan lupa absen ya!\n\nTerima kasih atas perhatian Anda.\n\nHormat kami,\n{organization}");
            $checkIn = Setting::get('check_in_time', '07:30');
            $target = Carbon::createFromFormat('H:i', $checkIn);
            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);
            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), $org],
                $template
            );

            if (strpos($template, '{minutes_left}') === false) {
                $text = trim($text) . "\n\nTersisa waktu kurang lebih {$minutesLeft} menit menuju jam masuk.";
            }

            $pantun = DB::table('pantuns')->where('type', 'masuk')->inRandomOrder()->value('text');
            if ($pantun) {
                $pantun = str_replace('\\n', PHP_EOL, $pantun);
                if (str_contains($text, '{pantun}')) {
                    $text = str_replace('{pantun}', $pantun, $text);
                } else {
                    $lines = preg_split("/\r\n|\n|\r/", $text);
                    if (isset($lines[0]) && trim($lines[0]) !== '') {
                        array_splice($lines, 1, 0, ['', $pantun]);
                        $text = implode(PHP_EOL, $lines);
                    } else {
                        $text = trim($text) . PHP_EOL . PHP_EOL . $pantun;
                    }
                }
            }
        } elseif ($type === 'pre_checkout') {
            $template = Setting::get('template_pre_checkout', "{name},\n\nIni adalah pengingat absen pulang.\nJam pulang kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon jangan lupa melakukan absen pulang sebelum meninggalkan kantor.\n\nTerima kasih atas dedikasi dan kerja keras Anda hari ini.\n\nHormat kami,\n{organization}");
            $isFriday = now()->isFriday();
            $checkOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');
            $target = Carbon::createFromFormat('H:i', $checkOut);
            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);
            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            $text = str_replace(
                ['{name}', '{kata}', '{minutes_left}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $minutesLeft, $targetToday->format('H:i'), $org],
                $template
            );

            if (strpos($template, '{minutes_left}') === false) {
                $text = trim($text) . "\n\nTersisa waktu kurang lebih {$minutesLeft} menit menuju jam penyelesaian tugas hari ini.";
            }

            $pantun = DB::table('pantuns')->where('type', 'pulang')->inRandomOrder()->value('text');
            if ($pantun) {
                $pantun = str_replace('\\n', PHP_EOL, $pantun);
                if (str_contains($text, '{pantun}')) {
                    $text = str_replace('{pantun}', $pantun, $text);
                } else {
                    $lines = preg_split("/\r\n|\n|\r/", $text);
                    if (isset($lines[0]) && trim($lines[0]) !== '') {
                        array_splice($lines, 1, 0, ['', $pantun]);
                        $text = implode(PHP_EOL, $lines);
                    } else {
                        $text = trim($text) . PHP_EOL . PHP_EOL . $pantun;
                    }
                }
            }
        } else {
            $msgRaw = $customMessage ?: Setting::get('template_broadcast', "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}");
            $text = str_replace(
                ['{name}', '{kata}', '{organization}'],
                [$namaLengkap, $kata, $org],
                $msgRaw
            );
        }

        try {
            $wa->send($emp->id, $emp->phone_number, $text, $type);
            return redirect()->back()->with('status', "Pesan untuk {$namaLengkap} ({$emp->phone_number}) berhasil dimasukkan ke antrean Outbox!");
        } catch (\Exception $e) {
            \Log::error('sendSingleEmployee failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', "Gagal mengirim pesan: " . $e->getMessage());
        }
    }

    public function setDefaultTimes(Request $request)
    {
        Setting::set('check_in_time', '07:30');
        Setting::set('check_out_time', '16:00');
        Setting::set('check_out_time_friday', '16:30');
        Setting::set('pre_reminder_minutes', 30);

        return redirect()->back()->with('status', 'Waktu default disimpan: Masuk 07:30, Pulang Sen-Kam 16:00, Pulang Jumat 16:30.');
    }

    // ── Fitur Kontrol Antrean Outbox ──

    public function retryFailedOutbox()
    {
        $updated = WaOutbox::whereIn('status', [WaOutbox::STATUS_FAILED, WaOutbox::STATUS_RETRY])
            ->update([
                'status'        => WaOutbox::STATUS_PENDING,
                'attempts'      => 0,
                'last_error'    => null,
                'scheduled_at'  => now(),
                'updated_at'    => now(),
            ]);

        return redirect()->back()->with('status', "Berhasil memindahkan {$updated} pesan gagal kembali ke antrean pending.");
    }

    public function cancelPendingOutbox()
    {
        $updated = WaOutbox::whereIn('status', [WaOutbox::STATUS_PENDING, WaOutbox::STATUS_RETRY])
            ->update([
                'status'     => WaOutbox::STATUS_CANCELLED,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('status', "Berhasil membatalkan {$updated} pesan yang sedang menunggu antrean.");
    }

    public function retrySingleOutbox($id)
    {
        $msg = WaOutbox::findOrFail($id);
        $msg->update([
            'status'        => WaOutbox::STATUS_PENDING,
            'attempts'      => 0,
            'last_error'    => null,
            'scheduled_at'  => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->back()->with('status', "Pesan ID #{$id} berhasil dipindahkan kembali ke antrean.");
    }

    public function syncHolidays()
    {
        $count = app(\App\Services\HolidayService::class)->syncNationalHolidays(date('Y'));
        return redirect()->back()->with('status', "Berhasil sinkronisasi {$count} hari libur nasional tahun " . date('Y') . ".");
    }
}
