<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add late checkin reminder template (15 minutes before checkin)
        DB::table('settings')->updateOrInsert(
            ['key' => 'template_late_checkin_formal'],
            ['value' => "Dengan hormat,\n\n{name},\n\nWaktu absensi pagi tinggal 15 menit lagi. Kami dari {organization_name} mengingatkan agar Anda segera melakukan absensi pagi. Jangan sampai ketinggalan waktu absensi ya.\n\n{pantun}\n\n{kata}", 'updated_at' => now()]
        );

        // Add late checkout reminder template (15 minutes after checkout)
        DB::table('settings')->updateOrInsert(
            ['key' => 'template_late_checkout_formal'],
            ['value' => "Dengan hormat,\n\n{name},\n\nWaktu absensi pulang telah lewat 15 menit. Kami dari {organization_name} mengingatkan agar segera melakukan absensi pulang. Terima kasih atas perhatian Anda dalam melengkapi data kehadiran.\n\n{pantun}\n\n{kata}", 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'template_late_checkin_formal',
            'template_late_checkout_formal',
        ])->delete();
    }
};
