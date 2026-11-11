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

    public function test_no_retry_error_marks_outbox_failed_immediately(): void
    {
        $emp = Employee::first() ?? Employee::create([
            'name' => 'Nomor Salah',
            'phone_number' => '81200001111',
            'is_active' => true,
        ]);

        $outbox = WaOutbox::create([
            'employee_id' => $emp->id,
            'phone_number' => '6281200001111',
            'message' => 'Test',
            'type' => 'manual',
            'status' => WaOutbox::STATUS_PROCESSING,
            'attempts' => 0,
        ]);

        $outbox->markFailed('[NO RETRY] Nomor tidak terdaftar di WhatsApp');
        $outbox->refresh();

        $this->assertEquals(WaOutbox::STATUS_FAILED, $outbox->status);
        $this->assertEquals(1, $outbox->attempts);
    }

    public function test_recover_stuck_processing_only_resets_stale_items(): void
    {
        $emp = Employee::first() ?? Employee::create([
            'name' => 'Stuck Test',
            'phone_number' => '81200002222',
            'is_active' => true,
        ]);

        $stale = WaOutbox::create([
            'employee_id' => $emp->id,
            'phone_number' => '6281200002222',
            'message' => 'Stale',
            'type' => 'manual',
            'status' => WaOutbox::STATUS_PROCESSING,
            'processing_at' => now()->subMinutes(10),
        ]);

        $fresh = WaOutbox::create([
            'employee_id' => $emp->id,
            'phone_number' => '6281200002222',
            'message' => 'Fresh',
            'type' => 'manual',
            'status' => WaOutbox::STATUS_PROCESSING,
            'processing_at' => now()->subMinutes(1),
        ]);

        $recovered = WaOutbox::recoverStuckProcessing(5);

        $stale->refresh();
        $fresh->refresh();

        $this->assertGreaterThanOrEqual(1, $recovered);
        $this->assertEquals(WaOutbox::STATUS_RETRY, $stale->status);
        $this->assertEquals(WaOutbox::STATUS_PROCESSING, $fresh->status);
    }

    public function test_double_click_pre_checkout_prevents_duplicate_outbox(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $emp = Employee::create([
            'name' => 'Anto Anti Dobel',
            'panggilan' => 'Bapak',
            'phone_number' => '81255556666',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post('/admin/send-pre-checkout', [
            'employee_ids' => [$emp->id],
        ])->assertSessionHas('status');

        $second = $this->actingAs($admin)->post('/admin/send-pre-checkout', [
            'employee_ids' => [$emp->id],
        ]);
        $second->assertSessionHas('error');

        $count = WaOutbox::where('employee_id', $emp->id)->where('type', 'pre_checkout')->count();
        $this->assertEquals(1, $count);
    }

    public function test_scheduler_build_message_replaces_target_time_and_supports_pre_checkout(): void
    {
        $emp = Employee::first() ?? Employee::create([
            'name' => 'Dewi Scheduler',
            'panggilan' => 'Ibu',
            'phone_number' => '81277778888',
            'is_active' => true,
        ]);

        $cmd = new class extends \App\Console\Commands\SendWaReminder {
            public function exposeBuildMessage(Employee $e, string $t): string
            {
                return $this->buildMessage($e, $t);
            }
        };

        $msgCheckin = $cmd->exposeBuildMessage($emp, 'pre_checkin');
        $this->assertStringNotContainsString('{target_time}', $msgCheckin);
        $this->assertStringNotContainsString('{name}', $msgCheckin);

        $msgCheckout = $cmd->exposeBuildMessage($emp, 'pre_checkout');
        $this->assertStringNotContainsString('{target_time}', $msgCheckout);
        $this->assertStringContainsString('{minutes_left}', $msgCheckout);
    }

    public function test_dashboard_qr_and_disconnect_lifecycle(): void
    {
        config(['whatsapp.agent_token' => 'test-agent-token']);
        $agentHeaders = ['Authorization' => 'Bearer test-agent-token'];
        $admin = User::first() ?? User::factory()->create();

        // 1. Agent mengirim heartbeat dengan QR code
        $qrSample = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
        $hbQr = $this->postJson('/api/agent/heartbeat', [
            'agent_name'     => 'default',
            'whatsapp_ready' => false,
            'qr_data_url'    => $qrSample,
            'metadata'       => ['state' => 'qr', 'loading_percent' => 0],
        ], $agentHeaders);
        $hbQr->assertStatus(200)->assertJson(['success' => true, 'command' => null]);

        // 2. Dashboard /admin/whatsapp/status harus mengembalikan QR tersebut
        $statusRes = $this->actingAs($admin)->getJson('/admin/whatsapp/status');
        $statusRes->assertStatus(200)
            ->assertJsonPath('connection.agent_online', true)
            ->assertJsonPath('connection.whatsapp_ready', false)
            ->assertJsonPath('connection.state', 'qr')
            ->assertJsonPath('connection.qr_data_url', $qrSample);

        // 3. Setelah discan (ready), QR dibersihkan dan nomor akun tampil
        $hbReady = $this->postJson('/api/agent/heartbeat', [
            'agent_name'     => 'default',
            'whatsapp_ready' => true,
            'clear_qr'       => true,
            'metadata'       => [
                'state'    => 'ready',
                'phone'    => '6285183250327',
                'pushname' => 'Bot BPS Karanganyar',
            ],
        ], $agentHeaders);
        $hbReady->assertStatus(200);

        $statusReady = $this->actingAs($admin)->getJson('/admin/whatsapp/status');
        $statusReady->assertStatus(200)
            ->assertJsonPath('connection.whatsapp_ready', true)
            ->assertJsonPath('connection.state', 'ready')
            ->assertJsonPath('connection.phone', '6285183250327')
            ->assertJsonPath('connection.pushname', 'Bot BPS Karanganyar')
            ->assertJsonPath('connection.qr_data_url', null);

        // 4. Admin klik Putus & Ganti Nomor via dashboard -> command 'logout' diambil oleh agent heartbeat berikutnya
        $discRes = $this->actingAs($admin)->postJson('/admin/whatsapp/disconnect');
        $discRes->assertStatus(200)->assertJsonPath('success', true)->assertJsonPath('command', 'logout');

        $hbNext = $this->postJson('/api/agent/heartbeat', [
            'agent_name'     => 'default',
            'whatsapp_ready' => true,
        ], $agentHeaders);
        $hbNext->assertStatus(200)->assertJson(['command' => 'logout']);

        // 5. Pastikan halaman dashboard render bersih beserta panel Koneksi WhatsApp
        $dash = $this->actingAs($admin)->get('/admin');
        $dash->assertStatus(200)->assertSee('Koneksi WhatsApp Pengirim');
    }

    public function test_disconnect_fails_gracefully_when_agent_is_offline(): void
    {
        $admin = User::first() ?? User::factory()->create();

        \App\Models\WaAgentHeartbeat::where('agent_name', 'default')->update([
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $res = $this->actingAs($admin)->postJson('/admin/whatsapp/disconnect');
        $res->assertStatus(422)->assertJson(['success' => false]);
    }
}
