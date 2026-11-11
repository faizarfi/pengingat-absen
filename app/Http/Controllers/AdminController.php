<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $employeeStatuses = [];
        $now = now();

        if ($employees->isNotEmpty()) {
            $employeeIds = $employees->pluck('id')->all();

            // 1. Ambil status outbox terbaru hari ini (Modern WA Desktop Agent v3.0)
            $latestOutbox = WaOutbox::whereIn('employee_id', $employeeIds)
                ->whereDate('created_at', Carbon::today())
                ->orderByDesc('id')
                ->get()
                ->unique('employee_id')
                ->keyBy('employee_id');

            // 2. Fallback ambil wa_logs lama jika outbox kosong
            $latestLogs = DB::table('wa_logs')
                ->whereIn('employee_id', $employeeIds)
                ->whereDate('created_at', Carbon::today())
                ->orderByDesc('created_at')
                ->get()
                ->unique('employee_id')
                ->keyBy('employee_id');

            foreach ($employees as $emp) {
                $outbox = $latestOutbox[$emp->id] ?? null;

                if ($outbox) {
                    if ($outbox->status === WaOutbox::STATUS_SENT) {
                        $employeeStatuses[$emp->id] = [
                            'label' => 'Sudah terkirim',
                            'variant' => 'success',
                            'detail' => $outbox->sent_at ? Carbon::parse($outbox->sent_at)->format('H:i:s') : null,
                        ];
                    } elseif ($outbox->status === WaOutbox::STATUS_PROCESSING) {
                        $employeeStatuses[$emp->id] = [
                            'label' => 'Sedang dikirim',
                            'variant' => 'info',
                            'detail' => 'Proses WA...',
                        ];
                    } elseif (in_array($outbox->status, [WaOutbox::STATUS_PENDING, WaOutbox::STATUS_RETRY])) {
                        $scheduled = $outbox->scheduled_at ? Carbon::parse($outbox->scheduled_at) : null;
                        $remain = $scheduled ? max(0, $scheduled->diffInSeconds($now, false)) : null;
                        $employeeStatuses[$emp->id] = [
                            'label' => 'Menunggu antrean',
                            'variant' => 'warning',
                            'detail' => $remain !== null ? "{$remain} detik" : 'Antrean...',
                        ];
                    } elseif ($outbox->status === WaOutbox::STATUS_FAILED) {
                        $employeeStatuses[$emp->id] = [
                            'label' => 'Gagal kirim',
                            'variant' => 'danger',
                            'detail' => $outbox->last_error ? Str::limit($outbox->last_error, 25) : 'Error',
                        ];
                    } elseif ($outbox->status === WaOutbox::STATUS_CANCELLED) {
                        $employeeStatuses[$emp->id] = [
                            'label' => 'Dibatalkan',
                            'variant' => 'neutral',
                            'detail' => 'Batal',
                        ];
                    }
                } elseif (isset($latestLogs[$emp->id])) {
                    $log = $latestLogs[$emp->id];
                    $logStatus = $log->status ?? ($log->sent_at ? 'sent' : ($log->scheduled_at ? 'pending' : null));

                    if ($logStatus === 'sent') {
                        $employeeStatuses[$emp->id] = [
                            'label' => 'Sudah terkirim',
                            'variant' => 'success',
                            'detail' => $log->sent_at ? Carbon::parse($log->sent_at)->format('H:i:s') : null,
                        ];
                    } elseif ($logStatus === 'pending') {
                        $scheduled = $log->scheduled_at ? Carbon::parse($log->scheduled_at) : null;
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
                            'detail' => 'Gagal',
                        ];
                    }
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
        $totalSentToday = WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count();
        if ($totalSentToday === 0) {
            $totalSentToday = DB::table('wa_logs')
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'sent')
                ->count();
        }

        // ── Agent Monitoring ──
        $waConnection = $this->buildWhatsappConnectionState();
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
            'waConnection',
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
            'name' => 'required|string|max:255',
            'panggilan' => 'nullable|string|max:50',
            'phone_number' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $data['phone_number']);
        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }
        $data['phone_number'] = $phone;
        $data['panggilan'] = $data['panggilan'] ?? 'Yth.';

        if (Employee::where('phone_number', $phone)->exists()) {
            return redirect()->back()->with('error', "Nomor WhatsApp (62{$phone}) sudah terdaftar untuk pegawai lain.");
        }

        Employee::create($data + ['is_active' => true]);

        return redirect()->back()->with('status', 'Karyawan baru berhasil ditambahkan.');
    }

    public function updateEmployee(Request $request, int|string $id)
    {
        $employee = Employee::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'panggilan' => 'nullable|string|max:50',
            'phone_number' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $data['phone_number']);
        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }
        $data['phone_number'] = $phone;
        $data['panggilan'] = $data['panggilan'] ?? 'Yth.';

        if (Employee::where('phone_number', $phone)->where('id', '!=', $employee->id)->exists()) {
            return redirect()->back()->with('error', "Nomor WhatsApp (62{$phone}) sudah digunakan pegawai lain.");
        }

        $employee->update($data);

        return redirect()->back()->with('status', 'Karyawan berhasil diperbarui.');
    }

    public function deleteEmployee(int|string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->back()->with('status', 'Karyawan berhasil dihapus.');
    }

    /**
     * Hapus banyak pegawai sekaligus (bulk delete).
     */
    public function deleteSelectedEmployees(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        /** @var array<int> $ids */
        $ids = $request->input('employee_ids', []);
        $count = Employee::whereIn('id', $ids, 'and', false)->delete();

        Log::info("Bulk delete: {$count} pegawai dihapus", ['ids' => $ids]);

        return redirect()->route('admin.dashboard')
            ->with('status', "{$count} pegawai berhasil dihapus.");
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

            Log::info('Excel import started', [
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

                // Cek nomor duplikat agar tidak tertimpa atau dobel kirim
                if (Employee::where('phone_number', $phone)->exists()) {
                    $skipped++;
                    continue;
                }

                Employee::create([
                    'name' => $name,
                    'panggilan' => $panggilan !== '' ? $panggilan : 'Yth.',
                    'phone_number' => $phone,
                    'is_active' => true,
                ]);

                $imported++;
            }

            Log::info('Excel import completed', [
                'imported' => $imported,
                'skipped' => $skipped,
            ]);

            if ($imported === 0) {
                return redirect()->route('admin.dashboard')->with('warning', 'Tidak ada data yang berhasil diimport. Cek format file Anda.');
            }

            return redirect()->route('admin.dashboard')->with('status', "Import selesai: {$imported} baris berhasil, {$skipped} baris dilewati.");
        } catch (\Exception $e) {
            Log::error('Excel import failed', [
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

    public function exportOutbox(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = WaOutbox::with('employee')->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $records = $query->limit(5000)->get();

        $filename = 'rekap-outbox-wa-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'No',
                'ID Outbox',
                'Waktu Dibuat',
                'Nama Pegawai',
                'Nomor WhatsApp',
                'Jenis Pesan',
                'Status',
                'Waktu Terkirim',
                'Keterangan / Error',
            ]);

            $i = 1;
            foreach ($records as $item) {
                $typeLabel = match($item->type) {
                    'pre_checkin' => 'Pengingat Absen Masuk',
                    'pre_checkout' => 'Pengingat Absen Pulang',
                    'checkin' => 'Absen Masuk (On-time)',
                    'checkout' => 'Absen Pulang (On-time)',
                    'manual' => 'Broadcast / Manual',
                    'custom' => 'Pesan Khusus',
                    default => ucfirst($item->type),
                };

                $statusLabel = match($item->status) {
                    WaOutbox::STATUS_SENT => 'Terkirim',
                    WaOutbox::STATUS_PENDING => 'Menunggu Antrean',
                    WaOutbox::STATUS_PROCESSING => 'Sedang Diproses',
                    WaOutbox::STATUS_FAILED => 'Gagal',
                    WaOutbox::STATUS_RETRY => 'Coba Ulang',
                    WaOutbox::STATUS_CANCELLED => 'Dibatalkan',
                    default => ucfirst($item->status),
                };

                $phone = (string) $item->phone_number;
                $displayPhone = str_starts_with($phone, '0') || str_starts_with($phone, '+') ? $phone : '0' . $phone;

                fputcsv($handle, [
                    $i,
                    $item->id,
                    $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '-',
                    $item->employee ? ($item->employee->panggilan . ' ' . $item->employee->name) : 'Tidak Diketahui',
                    $displayPhone,
                    $typeLabel,
                    $statusLabel,
                    $item->sent_at ? $item->sent_at->format('Y-m-d H:i:s') : '-',
                    $item->last_error ?: '-',
                ]);
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

        return redirect()->back()->with('status', 'Pengaturan berhasil disimpan!');
    }

    public function sendNow(Request $request)
    {
        $wa = app(WhatsAppService::class);
        $selectedIds = $request->input('employee_ids');
        $query = Employee::where('is_active', '=', true, 'and');
        $isFiltered = !empty($selectedIds) && is_array($selectedIds);
        if ($isFiltered) {
            $query->whereIn('id', $selectedIds);
        }
        $employees = $query->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $orgName = Setting::get('organization_name', 'BPS Kabupaten Karanganyar');
        
        // Use custom message from request if submitted, otherwise fallback to template setting
        $customMessage = $request->input('message') ?: Setting::get('template_broadcast', "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}");

        $guardKey = 'wa_click_manual_' . md5($customMessage . '|' . implode(',', $employees->pluck('id')->all()));
        if (!$this->acquireClickGuard($guardKey, 15)) {
            return redirect()->back()->with('error', 'Permintaan broadcast yang sama baru saja diproses. Mohon tunggu 15 detik sebelum mengirim ulang.');
        }

        $queued = 0;
        $skipped = 0;

        foreach ($employees->shuffle() as $emp) {
            if ($this->hasPendingOrRecentOutbox($emp->id, 'manual', 0)) {
                $skipped++;
                continue;
            }

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
                Log::error('sendNow failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        if ($queued === 0 && $skipped > 0) {
            return redirect()->back()->with('error', "Tidak ada broadcast baru ditambahkan ({$skipped} pegawai masih memiliki antrean pesan aktif).");
        }

        $targetMsg = $isFiltered ? "{$queued} pegawai terpilih" : "{$queued}/{$employees->count()} karyawan";
        $skipNote = $skipped > 0 ? " ({$skipped} dilewati karena masih dalam antrean)" : '';
        return redirect()->back()->with('status', "Broadcast berhasil dimasukkan ke antrean: {$targetMsg}{$skipNote}.");
    }

    public function sendPreCheckinNow(Request $request)
    {
        $wa = app(WhatsAppService::class);
        $selectedIds = $request->input('employee_ids');
        $query = Employee::where('is_active', '=', true, 'and');
        $isFiltered = !empty($selectedIds) && is_array($selectedIds);
        if ($isFiltered) {
            $query->whereIn('id', $selectedIds);
        }
        $employees = $query->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $template = Setting::get('template_pre_checkin', "{name},\n\nIni adalah pengingat absen masuk.\nJam masuk kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon segera lakukan absen masuk. Jangan lupa absen ya!\n\nTerima kasih atas perhatian Anda.\n\nHormat kami,\n{organization}");
        $checkIn = Setting::get('check_in_time', '07:30');

        $target = Carbon::createFromFormat('H:i', $checkIn);

        // compute global minutes-left for the redirect summary
        $nowGlobal = Carbon::now();
        $targetTodayGlobal = (clone $target)->setDate($nowGlobal->year, $nowGlobal->month, $nowGlobal->day);
        $minutesLeftGlobal = (int) max(0, ceil(($targetTodayGlobal->getTimestamp() - $nowGlobal->getTimestamp()) / 60));

        $guardKey = 'wa_click_pre_checkin_' . md5(implode(',', $employees->pluck('id')->all()));
        if (!$this->acquireClickGuard($guardKey, 15)) {
            return redirect()->back()->with('error', 'Pengingat masuk baru saja diproses. Mohon tunggu 15 detik sebelum mengirim ulang.');
        }

        $queued = 0;
        $skipped = 0;
        foreach ($employees->shuffle() as $emp) {
            if ($this->hasPendingOrRecentOutbox($emp->id, 'pre_checkin', 10)) {
                $skipped++;
                continue;
            }

            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);

            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            // Prepare message and replace placeholders (keep {minutes_left} dynamic)
            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $targetToday->format('H:i'), Setting::get('organization_name', 'BPS Kabupaten Karanganyar')],
                $template
            );

            // If template doesn't include the {minutes_left} placeholder, append a fallback sentence
            if (strpos($text, '{minutes_left}') === false) {
                $fallback = "Tersisa waktu kurang lebih {minutes_left} menit menuju jam masuk kerja.";
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
                Log::error('sendPreCheckinNow failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        if ($queued === 0 && $skipped > 0) {
            return redirect()->back()->with('error', "Tidak ada antrean baru ditambahkan ({$skipped} pegawai sudah ada di antrean atau baru saja menerima pengingat masuk).");
        }

        $targetMsg = $isFiltered ? "{$queued} pegawai terpilih" : "{$queued}/{$employees->count()} karyawan";
        $skipNote = $skipped > 0 ? " ({$skipped} dilewati karena sudah diproses)" : '';
        return redirect()->back()->with('status', "Pengingat masuk dimasukkan ke antrean: {$targetMsg}{$skipNote}. Sisa menit: {$minutesLeftGlobal}");
    }

    public function sendPreCheckoutNow(Request $request)
    {
        $wa = app(WhatsAppService::class);
        $selectedIds = $request->input('employee_ids');
        $query = Employee::where('is_active', '=', true, 'and');
        $isFiltered = !empty($selectedIds) && is_array($selectedIds);
        if ($isFiltered) {
            $query->whereIn('id', $selectedIds);
        }
        $employees = $query->get();
        $kata = Setting::get('closing_word', 'Semangat kerja!');
        $template = Setting::get('template_pre_checkout', "{name},\n\nIni adalah pengingat absen pulang.\nJam pulang kerja Anda adalah pukul {target_time} WIB. Tersisa waktu kurang lebih {minutes_left} menit.\n\nMohon jangan lupa melakukan absen pulang sebelum meninggalkan kantor.\n\nTerima kasih atas dedikasi dan kerja keras Anda hari ini.\n\nHormat kami,\n{organization}");
        $isFriday = now()->isFriday();
        $checkOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');

        $target = Carbon::createFromFormat('H:i', $checkOut);

        // compute global minutes-left for the redirect summary
        $nowGlobal = Carbon::now();
        $targetTodayGlobal = (clone $target)->setDate($nowGlobal->year, $nowGlobal->month, $nowGlobal->day);
        $minutesLeftGlobal = (int) max(0, ceil(($targetTodayGlobal->getTimestamp() - $nowGlobal->getTimestamp()) / 60));

        $guardKey = 'wa_click_pre_checkout_' . md5(implode(',', $employees->pluck('id')->all()));
        if (!$this->acquireClickGuard($guardKey, 15)) {
            return redirect()->back()->with('error', 'Pengingat pulang baru saja diproses. Mohon tunggu 15 detik sebelum mengirim ulang.');
        }

        $queued = 0;
        $skipped = 0;
        foreach ($employees->shuffle() as $emp) {
            if ($this->hasPendingOrRecentOutbox($emp->id, 'pre_checkout', 10)) {
                $skipped++;
                continue;
            }

            $now = Carbon::now();
            $targetToday = $target->setDate($now->year, $now->month, $now->day);

            $minutesLeft = (int) max(0, $now->diffInMinutes($targetToday));

            $panggilan = $emp->panggilan ?? 'Yth.';
            $namaLengkap = $panggilan . ' ' . $emp->name;
            $text = str_replace(
                ['{name}', '{kata}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $targetToday->format('H:i'), Setting::get('organization_name', 'BPS Kabupaten Karanganyar')],
                $template
            );

            if (strpos($text, '{minutes_left}') === false) {
                $fallback = "Tersisa waktu kurang lebih {minutes_left} menit menuju jam penyelesaian tugas hari ini.";
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
                Log::error('sendPreCheckoutNow failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
            }
        }

        if ($queued === 0 && $skipped > 0) {
            return redirect()->back()->with('error', "Tidak ada antrean baru ditambahkan ({$skipped} pegawai sudah ada di antrean atau baru saja menerima pengingat pulang).");
        }

        $targetMsg = $isFiltered ? "{$queued} pegawai terpilih" : "{$queued}/{$employees->count()} karyawan";
        $skipNote = $skipped > 0 ? " ({$skipped} dilewati karena sudah diproses)" : '';
        return redirect()->back()->with('status', "Pengingat pulang dimasukkan ke antrean: {$targetMsg}{$skipNote}. Sisa menit: {$minutesLeftGlobal}");
    }

    /**
     * POST /admin/send-selected
     * Kirim pesan (masuk/pulang/custom) ke daftar pegawai tertentu yang dipilih lewat checkbox.
     */
    public function sendSelectedEmployees(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'type'         => 'required|in:pre_checkin,pre_checkout,custom',
            'message'      => 'nullable|string',
        ]);

        $type = $request->input('type');
        if ($type === 'pre_checkin') {
            return $this->sendPreCheckinNow($request);
        } elseif ($type === 'pre_checkout') {
            return $this->sendPreCheckoutNow($request);
        } else {
            return $this->sendNow($request);
        }
    }

    public function sendSingleEmployee(Request $request, int|string $id)
    {
        $emp = Employee::findOrFail($id);
        $type = $request->input('type', 'custom');
        $customMessage = $request->input('message');

        $guardKey = 'wa_click_single_' . $emp->id . '_' . md5($type . '|' . ($customMessage ?? ''));
        if (!$this->acquireClickGuard($guardKey, 5)) {
            return redirect()->back()->with('error', "Pesan untuk {$emp->name} baru saja dimasukkan ke antrean. Tunggu sebentar.");
        }

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
                ['{name}', '{kata}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $targetToday->format('H:i'), $org],
                $template
            );

            if (strpos($text, '{minutes_left}') === false) {
                $text = trim($text) . "\n\nTersisa waktu kurang lebih {minutes_left} menit menuju jam masuk.";
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
                ['{name}', '{kata}', '{target_time}', '{organization}'],
                [$namaLengkap, $kata, $targetToday->format('H:i'), $org],
                $template
            );

            if (strpos($text, '{minutes_left}') === false) {
                $text = trim($text) . "\n\nTersisa waktu kurang lebih {minutes_left} menit menuju jam penyelesaian tugas hari ini.";
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
            Log::error('sendSingleEmployee failed', ['employee_id' => $emp->id, 'error' => $e->getMessage()]);
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

    public function retrySingleOutbox(int|string $id)
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

    /**
     * Hapus log outbox yang dipilih (bulk delete).
     */
    public function deleteSelectedOutbox(Request $request)
    {
        $request->validate([
            'outbox_ids'   => 'required|array|min:1',
            'outbox_ids.*' => 'integer|exists:wa_outbox,id',
        ]);

        /** @var array<int> $ids */
        $ids = $request->input('outbox_ids', []);
        $count = WaOutbox::whereIn('id', $ids, 'and', false)->delete();

        Log::info("Outbox bulk delete: {$count} log outbox dihapus", ['ids' => $ids]);

        return redirect()->back()->with('status', "{$count} riwayat antrean & log berhasil dihapus.");
    }

    /**
     * Hapus satu baris log outbox.
     */
    public function deleteSingleOutbox(int|string $id)
    {
        $msg = WaOutbox::findOrFail($id);
        $msg->delete();

        return redirect()->back()->with('status', "Log antrean ID #{$id} berhasil dihapus.");
    }

    /**
     * Bersihkan semua log outbox & reset cache aplikasi.
     */
    public function clearAllOutbox(Request $request)
    {
        $count = WaOutbox::count();
        WaOutbox::query()->delete();

        // Reset cache pengiriman batch dan status
        \Illuminate\Support\Facades\Cache::forget('wa_batch_active');
        \Illuminate\Support\Facades\Cache::forget('wa_batch_start');

        Log::info("Outbox clear-all: {$count} log dibersihkan");

        return redirect()->back()->with('status', "Berhasil membersihkan seluruh riwayat antrean ({$count} data) dan mereset cache.");
    }

    public function syncHolidays()
    {
        $count = app(\App\Services\HolidayService::class)->syncNationalHolidays(date('Y'));
        return redirect()->back()->with('status', "Berhasil sinkronisasi {$count} hari libur nasional tahun " . date('Y') . ".");
    }

    /**
     * Ganti password akun admin yang sedang login.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password'     => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'new_password.min'                  => 'Password baru minimal harus 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password baru tidak sesuai.',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        Log::info('Admin password changed successfully', ['user_id' => $user->id]);

        return redirect()->back()->with('status', 'Password admin berhasil diperbarui!');
    }

    /**
     * GET /admin/whatsapp/status
     * Status koneksi WA Agent & QR code live untuk Dashboard.
     */
    public function whatsappStatus()
    {
        return response()->json([
            'success'    => true,
            'connection' => $this->buildWhatsappConnectionState(),
            'outbox'     => [
                'pending'         => WaOutbox::pending()->count(),
                'processing'      => WaOutbox::processing()->count(),
                'sent_today'      => WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count(),
                'failed'          => WaOutbox::failed()->today()->count(),
                'retry'           => WaOutbox::retry()->count(),
                'employees_total' => Employee::count(),
            ],
        ]);
    }

    /**
     * POST /admin/whatsapp/disconnect
     * Putus koneksi WA saat ini dan siapkan QR code baru untuk ganti nomor.
     */
    public function disconnectWhatsapp(Request $request)
    {
        return $this->queueAgentCommand(
            $request,
            'logout',
            'Perintah putuskan koneksi & ganti nomor dikirim ke WA Agent. QR Code baru akan muncul dalam beberapa detik.'
        );
    }

    /**
     * POST /admin/whatsapp/reconnect
     * Restart koneksi WhatsApp Web pada agent tanpa menghapus sesi tersimpan.
     */
    public function reconnectWhatsapp(Request $request)
    {
        return $this->queueAgentCommand(
            $request,
            'reconnect',
            'Perintah hubungkan ulang dikirim ke WA Agent. Mohon tunggu sebentar.'
        );
    }

    private function queueAgentCommand(Request $request, string $command, string $successMsg)
    {
        $agent = WaAgentHeartbeat::where('agent_name', 'default')->first();
        if (!$agent || !$agent->isOnline()) {
            $err = 'WA Agent di PC kantor sedang offline. Pastikan node wa-desktop-agent/wa-agent.js sedang berjalan.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $err], 422)
                : redirect()->back()->with('error', $err);
        }

        Cache::put('wa_agent_command_default', $command, now()->addSeconds(60));
        Cache::put('wa_agent_pending_cmd_default', $command, now()->addSeconds(20));

        if ($command === 'logout') {
            Cache::forget('wa_agent_qr_default');
        }

        Log::info("Admin triggered WA agent command: {$command}");

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $successMsg, 'command' => $command])
            : redirect()->back()->with('status', $successMsg);
    }

    private function buildWhatsappConnectionState(): array
    {
        $agent = WaAgentHeartbeat::where('agent_name', 'default')->first();
        $agentOnline = $agent ? $agent->isOnline() : false;
        $whatsappReady = $agentOnline && $agent->whatsapp_ready;
        $meta = is_array($agent?->metadata) ? $agent->metadata : [];
        $pendingCommand = Cache::get('wa_agent_pending_cmd_default');
        $qrCache = $agentOnline ? Cache::get('wa_agent_qr_default') : null;

        $state = 'offline';
        if ($agentOnline) {
            if ($pendingCommand === 'logout') {
                $state = 'logging_out';
            } elseif ($pendingCommand === 'reconnect') {
                $state = 'initializing';
            } else {
                $state = $meta['state'] ?? ($whatsappReady ? 'ready' : 'initializing');
            }
        }

        if ($agentOnline && !$whatsappReady && !empty($qrCache['data_url']) && !in_array($state, ['logging_out', 'authenticated'], true)) {
            $state = 'qr';
        }

        return [
            'agent_online'    => $agentOnline,
            'whatsapp_ready'  => $whatsappReady,
            'state'           => $state,
            'phone'           => $meta['phone'] ?? null,
            'pushname'        => $meta['pushname'] ?? null,
            'platform'        => $meta['platform'] ?? null,
            'loading_percent' => (int) ($meta['loading_percent'] ?? 0),
            'loading_message' => $meta['loading_message'] ?? null,
            'delay_range'     => $meta['delay_range'] ?? null,
            'qr_data_url'     => ($agentOnline && !$whatsappReady && $state === 'qr') ? ($qrCache['data_url'] ?? null) : null,
            'qr_updated_at'   => $qrCache['updated_at'] ?? ($meta['qr_updated_at'] ?? null),
            'last_seen_at'    => $agent?->last_seen_at?->toIso8601String(),
            'last_seen_human' => $agent?->last_seen_at?->diffForHumans(),
            'pending_command' => $pendingCommand,
        ];
    }

    /**
     * Cegah pengiriman ganda akibat klik tombol beruntun dalam hitungan detik.
     */
    private function acquireClickGuard(string $key, int $seconds = 15): bool
    {
        return Cache::add($key, true, $seconds);
    }

    /**
     * Periksa apakah pegawai sudah memiliki antrean aktif (atau baru saja dikirimi tipe yang sama).
     */
    private function hasPendingOrRecentOutbox(int $employeeId, string $type, int $recentMinutes = 10): bool
    {
        return WaOutbox::where('employee_id', $employeeId)
            ->where('type', $type)
            ->where(function ($q) use ($recentMinutes) {
                $q->whereIn('status', [
                    WaOutbox::STATUS_PENDING,
                    WaOutbox::STATUS_PROCESSING,
                    WaOutbox::STATUS_RETRY,
                ]);

                if ($recentMinutes > 0) {
                    $q->orWhere(function ($sq) use ($recentMinutes) {
                        $sq->where('status', WaOutbox::STATUS_SENT)
                           ->where('sent_at', '>=', now()->subMinutes($recentMinutes));
                    });
                }
            })
            ->exists();
    }
}
