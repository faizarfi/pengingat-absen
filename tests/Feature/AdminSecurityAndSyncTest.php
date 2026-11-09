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
}
