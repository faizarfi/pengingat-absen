<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\WaOutbox;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWaReminder extends Command
{
    protected $signature = 'wa:send-reminders';
    protected $description = 'Kirim pengingat absen via WhatsApp sesuai jadwal';

    public function handle(WhatsAppService $wa)
    {
        $now = now()->format('H:i');
        $isFriday = now()->isFriday();

        $checkIn = Setting::get('check_in_time', '07:30');
        $checkOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');
        $preReminderMinutes = (int) Setting::get('pre_reminder_minutes', 30);

        // Calculate pre-reminder time (30 menit sebelum jam absen)
        $checkInTime = \Carbon\Carbon::createFromFormat('H:i', $checkIn);
        $preReminderTime = $checkInTime->copy()->subMinutes($preReminderMinutes)->format('H:i');

        // Calculate late reminder time (15 menit sebelum jam absen - masih waktu untuk absen)
        $lateReminderTime = $checkInTime->copy()->subMinutes(15)->format('H:i');

        // Calculate late checkout reminder time (15 menit setelah jam pulang - pengingat terlambat pulang)
        $checkOutTime = \Carbon\Carbon::createFromFormat('H:i', $checkOut);
        $lateCheckoutReminderTime = $checkOutTime->copy()->addMinutes(15)->format('H:i');

        $type = null;
        if ($now === $preReminderTime) $type = 'pre_checkin';
        if ($now === $lateReminderTime) $type = 'late_checkin_reminder';
        if ($now === $checkIn) $type = 'checkin';
        if ($now === $lateCheckoutReminderTime) $type = 'late_checkout_reminder';
        if ($now === $checkOut) $type = 'checkout';

        if (!$type) {
            return 0;
        }

        $employees = Employee::where('is_active', true)->get();
        $queued = 0;

        foreach ($employees as $emp) {
            // Cek apakah sudah ada pengiriman hari ini untuk tipe ini
            $existsInOutbox = WaOutbox::whereDate('created_at', now()->toDateString())
                ->where('employee_id', $emp->id)
                ->where('type', $type)
                ->exists();

            // Backward compat: cek juga wa_logs lama
            $existsInLogs = DB::table('wa_logs')
                ->whereDate('created_at', now()->toDateString())
                ->where('employee_id', $emp->id)
                ->where('type', $type)
                ->exists();

            if ($existsInOutbox || $existsInLogs) {
                continue;
            }

            $message = $this->buildMessage($emp, $type);

            // Gunakan WhatsAppService->send() yang akan routing ke driver yang tepat
            $result = $wa->send($emp->id, $emp->phone_number, $message, $type);

            if (isset($result['outbox_id']) || ($result['success'] ?? false)) {
                $queued++;
            }

            Log::info('SendWaReminder: pesan dimasukkan ke outbox', [
                'employee_id' => $emp->id,
                'type'        => $type,
                'driver'      => $result['driver'] ?? 'unknown',
                'outbox_id'   => $result['outbox_id'] ?? null,
            ]);
        }

        $this->info("Berhasil memasukkan {$queued} pesan ke outbox untuk tipe: {$type}");
        return 0;
    }

    protected function buildMessage($emp, ?string $type = null)
    {
        $organizationName = Setting::get('organization_name', 'BPS Karanganyar');
        $kata = Setting::get('closing_word', 'Hormat kami, ' . $organizationName);
        $panggilan = $emp->panggilan ?? 'Yth.';
        $namaLengkap = $panggilan . ' ' . $emp->name;

        // Determine type from current time if not provided
        if (!$type) {
            $now = now()->format('H:i');
            $isFriday = now()->isFriday();
            $checkIn = Setting::get('check_in_time', '07:30');
            $checkOut = $isFriday ? Setting::get('check_out_time_friday', '16:30') : Setting::get('check_out_time', '16:00');
            $preReminderMinutes = (int) Setting::get('pre_reminder_minutes', 30);
            $checkInTime = \Carbon\Carbon::createFromFormat('H:i', $checkIn);
            $preReminderTime = $checkInTime->copy()->subMinutes($preReminderMinutes)->format('H:i');
            $lateReminderTime = $checkInTime->copy()->subMinutes(15)->format('H:i');
            $checkOutTime = \Carbon\Carbon::createFromFormat('H:i', $checkOut);
            $lateCheckoutReminderTime = $checkOutTime->copy()->addMinutes(15)->format('H:i');

            if ($now === $preReminderTime) $type = 'pre_checkin';
            if ($now === $lateReminderTime) $type = 'late_checkin_reminder';
            if ($now === $checkIn) $type = 'checkin';
            if ($now === $lateCheckoutReminderTime) $type = 'late_checkout_reminder';
            if ($now === $checkOut) $type = 'checkout';
        }

        // Formal and polite templates
        if ($type === 'pre_checkin') {
            $template = Setting::get(
                'template_pre_checkin_formal',
                "Dengan hormat,\n\n" .
                "{name},\n\n" .
                "Kami dari {organization_name} ingin mengingatkan bahwa dalam 30 menit lagi adalah waktu untuk absensi pagi. " .
                "Mohon untuk segera melakukan absensi tepat waktu.\n\n" .
                "{pantun}\n\n" .
                "{kata}"
            );
        } elseif ($type === 'late_checkin_reminder') {
            $template = Setting::get(
                'template_late_checkin_formal',
                "Dengan hormat,\n\n" .
                "{name},\n\n" .
                "Waktu absensi pagi tinggal 15 menit lagi. Kami dari {organization_name} mengingatkan agar Anda segera melakukan absensi pagi. " .
                "Jangan sampai ketinggalan waktu absensi ya.\n\n" .
                "{pantun}\n\n" .
                "{kata}"
            );
        } elseif ($type === 'checkin') {
            $template = Setting::get(
                'template_checkin_formal',
                "Dengan hormat,\n\n" .
                "{name},\n\n" .
                "Sudah saatnya waktu absensi pagi tiba. Kami dari {organization_name} mengingatkan agar segera melakukan absensi. " .
                "Terima kasih atas perhatian dan kepatuhan Anda.\n\n" .
                "{pantun}\n\n" .
                "{kata}"
            );
        } elseif ($type === 'late_checkout_reminder') {
            $template = Setting::get(
                'template_late_checkout_formal',
                "Dengan hormat,\n\n" .
                "{name},\n\n" .
                "Waktu absensi pulang telah lewat 15 menit. Kami dari {organization_name} mengingatkan agar segera melakukan absensi pulang. " .
                "Terima kasih atas perhatian Anda dalam melengkapi data kehadiran.\n\n" .
                "{pantun}\n\n" .
                "{kata}"
            );
        } else {
            $template = Setting::get(
                'template_checkout_formal',
                "Dengan hormat,\n\n" .
                "{name},\n\n" .
                "Sudah saatnya waktu absensi pulang. Kami dari {organization_name} mengingatkan agar segera melakukan absensi pulang. " .
                "Terima kasih atas dedikasi kerja keras Anda hari ini.\n\n" .
                "{pantun}\n\n" .
                "{kata}"
            );
        }

        $pantun = $this->generatePantunFormal();

        $message = str_replace(
            ['{name}', '{kata}', '{pantun}', '{organization_name}'],
            [$namaLengkap, $kata, $pantun, $organizationName],
            $template
        );

        return $message;
    }

    protected function generatePantunFormal()
    {
        $pantuns = [
            // Pantun yang lebih sopan dan profesional
            'Bekerja dengan sungguh-sungguh,\nAkan membawa hasil yang maksimal.',
            'Tepat waktu adalah prioritas,\nMenjaga kepercayaan organisasi.',
            'Absen yang teratur dan rapi,\nTanda profesional dalam bekerja.',
            'Kehadiran penuh setiap hari,\nBentuk komitmen terhadap tugas.',
            'Tanggung jawab adalah kunci,\nUntuk mencapai kesuksesan bersama.',
            'Disiplin dalam setiap langkah,\nMenciptakan lingkungan kerja yang baik.',
            'Kehadiran membentuk prestasi,\nBersama membangun organisasi maju.',
            'Kerja sama yang solid dan kuat,\nMewujudkan visi institusi kami.',
        ];

        return $pantuns[array_rand($pantuns)];
    }
}
