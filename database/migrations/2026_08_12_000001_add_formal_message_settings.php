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
        // Insert formal message settings for BPS Karanganyar
        DB::table('settings')->updateOrInsert(
            ['key' => 'organization_name'],
            ['value' => 'BPS Karanganyar', 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'template_pre_checkin_formal'],
            ['value' => "Dengan hormat,\n\n{name},\n\nKami dari {organization_name} ingin mengingatkan bahwa dalam 30 menit lagi adalah waktu untuk absensi pagi. Mohon untuk segera melakukan absensi tepat waktu.\n\n{pantun}\n\n{kata}", 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'template_checkin_formal'],
            ['value' => "Dengan hormat,\n\n{name},\n\nSudah saatnya waktu absensi pagi tiba. Kami dari {organization_name} mengingatkan agar segera melakukan absensi. Terima kasih atas perhatian dan kepatuhan Anda.\n\n{pantun}\n\n{kata}", 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'template_checkout_formal'],
            ['value' => "Dengan hormat,\n\n{name},\n\nSudah saatnya waktu absensi pulang. Kami dari {organization_name} mengingatkan agar segera melakukan absensi pulang. Terima kasih atas dedikasi kerja keras Anda hari ini.\n\n{pantun}\n\n{kata}", 'updated_at' => now()]
        );

        // Update closing word to formal version if not customized
        $closingWord = DB::table('settings')->where('key', 'closing_word')->first();
        if (!$closingWord || $closingWord->value === 'Semangat kerja!') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'closing_word'],
                ['value' => 'Hormat kami, BPS Karanganyar', 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove formal settings if needed
        DB::table('settings')->whereIn('key', [
            'organization_name',
            'template_pre_checkin_formal',
            'template_checkin_formal',
            'template_checkout_formal',
        ])->delete();
    }
};
