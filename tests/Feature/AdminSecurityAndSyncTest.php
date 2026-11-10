<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\WaOutbox;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class AdminSecurityAndSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_login_has_rate_limiting(): void
    {
        // 5 failed attempts allowed
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrongpass',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be blocked by rate limiter (HTTP 429 Too Many Requests)
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass',
        ]);
        $response->assertStatus(429);
    }

    public function test_admin_can_update_password(): void
    {
        $admin = User::first() ?? User::factory()->create([
            'email' => 'unique_test_admin@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $admin->password = Hash::make('oldpassword123');
        $admin->save();

        $response = $this->actingAs($admin)->post('/admin/profile/password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newsecretpassword123',
            'new_password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $admin->refresh();
        $this->assertTrue(Hash::check('newsecretpassword123', $admin->password));
    }

    public function test_admin_cannot_update_password_with_wrong_current_password(): void
    {
        $admin = User::first() ?? User::factory()->create([
            'email' => 'unique_test_admin2@example.com',
            'password' => Hash::make('oldpassword123'),
        ]);

        $admin->password = Hash::make('oldpassword123');
        $admin->save();

        $response = $this->actingAs($admin)->post('/admin/profile/password', [
            'current_password' => 'wrongcurrentpass',
            'new_password' => 'newsecretpassword123',
            'new_password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_cannot_create_employee_with_duplicate_phone(): void
    {
        $admin = User::first() ?? User::factory()->create();

        Employee::create([
            'name' => 'Budi Test Unik',
            'panggilan' => 'Pak',
            'phone_number' => '89999999999',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->from('/admin')->post('/admin/employees', [
            'name' => 'Budi Duplikat',
            'panggilan' => 'Pak',
            'phone_number' => '089999999999', // Format dengan 0, sama persis setelah dinormalisasi
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('employees', [
            'name' => 'Budi Duplikat',
        ]);
    }

    public function test_dashboard_displays_status_from_wa_outbox(): void
    {
        $admin = User::factory()->create();

        $emp = Employee::create([
            'name' => 'Siti',
            'panggilan' => 'Ibu',
            'phone_number' => '81299998888',
            'is_active' => true,
        ]);

        // Create an outbox record with SENT status
        WaOutbox::create([
            'employee_id' => $emp->id,
            'phone_number' => '6281299998888',
            'message' => 'Halo Siti',
            'type' => 'pre_checkin',
            'status' => WaOutbox::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Sudah terkirim');
    }

    public function test_admin_can_export_outbox_csv(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $emp = Employee::create([
            'name' => 'Ahmad Pegawai',
            'panggilan' => 'Bapak',
            'phone_number' => '81277776666',
            'is_active' => true,
        ]);

        WaOutbox::create([
            'employee_id' => $emp->id,
            'phone_number' => '6281277776666',
            'message' => 'Halo Ahmad',
            'type' => 'manual',
            'status' => WaOutbox::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/outbox/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('Ahmad Pegawai', $response->streamedContent());
    }

    public function test_pre_checkout_preserves_dynamic_minutes_placeholder_in_outbox(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $emp = Employee::create([
            'name' => 'Rina Dinamis',
            'panggilan' => 'Ibu',
            'phone_number' => '81211112222',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/send-pre-checkout', [
            'employee_ids' => [$emp->id],
        ]);

        $response->assertSessionHas('status');

        $outbox = WaOutbox::where('employee_id', $emp->id)->where('type', 'pre_checkout')->latest()->first();
        $this->assertNotNull($outbox);
        // Pastikan variabel {minutes_left} tetap dipertahankan agar dihitung real-time saat pesan dikirim oleh agent
        $this->assertStringContainsString('{minutes_left}', $outbox->message);
    }

    public function test_agent_api_provides_target_time_and_saves_final_message(): void
    {
        config(['whatsapp.agent_token' => 'test-agent-token']);
        $headers = ['Authorization' => 'Bearer test-agent-token'];

        $emp = Employee::first() ?? Employee::create([
            'name' => 'Bambang Agent',
            'phone_number' => '81233334444',
            'is_active' => true,
        ]);

        $outbox = WaOutbox::create([
            'employee_id' => $emp->id,
            'phone_number' => '6281233334444',
            'message' => 'Jam pulang kerja Anda pukul 16:00 WIB. Tersisa waktu kurang lebih {minutes_left} menit.',
            'type' => 'pre_checkout',
            'status' => WaOutbox::STATUS_PENDING,
        ]);

        // 1. Cek GET /api/agent/messages
        $res = $this->getJson('/api/agent/messages?limit=10', $headers);
        $res->assertStatus(200);
        $data = collect($res->json('data'))->firstWhere('id', $outbox->id);
        $this->assertNotNull($data);
        $this->assertNotEmpty($data['target_time']);

        // 2. Cek POST /api/agent/messages/{id}/sent dengan final_message
        $sentText = 'Jam pulang kerja Anda pukul 16:00 WIB. Tersisa waktu kurang lebih 5 menit.';
        $sentRes = $this->postJson("/api/agent/messages/{$outbox->id}/sent", [
            'final_message' => $sentText,
        ], $headers);
        $sentRes->assertStatus(200);

        $outbox->refresh();
        $this->assertEquals(WaOutbox::STATUS_SENT, $outbox->status);
        $this->assertEquals($sentText, $outbox->message);
    }
}
