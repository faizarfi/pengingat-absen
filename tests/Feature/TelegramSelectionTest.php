<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use App\Services\HolidayService;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TelegramSelectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'pengingat_absen',
            'telegram.bot_token' => 'dummy_token',
            'telegram.admin_chat_id' => '123456',
        ]);
    }

    public function test_pilih_command_shows_selector(): void
    {
        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with(
                '123456',
                $this->stringContains('Pilih Pegawai Pengingat Absen'),
                $this->callback(function ($markup) {
                    return isset($markup['inline_keyboard']);
                })
            )
            ->willReturn(true);

        $controller = app(TelegramWebhookController::class);
        $update = [
            'update_id' => 1,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/pilih',
            ],
        ];

        $controller->processUpdate(
            $update,
            $mockTelegram,
            app(WhatsAppService::class),
            app(HolidayService::class)
        );
    }

    public function test_callback_query_toggle_employee(): void
    {
        Cache::forget('telegram_sel_123456');

        $emp = Employee::first(['*']);
        $this->assertNotNull($emp);

        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('answerCallbackQuery')
            ->with('cb_1', 'Pilihan diperbarui')
            ->willReturn(true);

        $mockTelegram->expects($this->once())
            ->method('editMessageText')
            ->willReturn(true);

        $controller = app(TelegramWebhookController::class);
        $update = [
            'update_id' => 2,
            'callback_query' => [
                'id' => 'cb_1',
                'data' => "tg_toggle:{$emp->id}",
                'message' => [
                    'message_id' => 999,
                    'chat' => ['id' => 123456],
                ],
            ],
        ];

        $controller->processUpdate(
            $update,
            $mockTelegram,
            app(WhatsAppService::class),
            app(HolidayService::class)
        );

        $selected = Cache::get('telegram_sel_123456');
        $this->assertContains($emp->id, $selected);
    }

    public function test_masuk_with_specific_employee_name(): void
    {
        $emp = Employee::first(['*']);
        $this->assertNotNull($emp);

        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with(
                '123456',
                $this->stringContains($emp->name)
            )
            ->willReturn(true);

        $mockWa = $this->createMock(WhatsAppService::class);
        $mockWa->expects($this->once())
            ->method('send')
            ->with($emp->id, $this->anything(), $this->anything(), 'pre_checkin')
            ->willReturn(['success' => true]);

        $controller = app(TelegramWebhookController::class);
        $update = [
            'update_id' => 3,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => "/masuk {$emp->name}",
            ],
        ];

        $controller->processUpdate(
            $update,
            $mockTelegram,
            $mockWa,
            app(HolidayService::class)
        );
    }

    public function test_pegawai_list_command(): void
    {
        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with(
                '123456',
                $this->stringContains('DAFTAR PEGAWAI AKTIF')
            )
            ->willReturn(true);

        $controller = app(TelegramWebhookController::class);
        $update = [
            'update_id' => 4,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/pegawai',
            ],
        ];

        $controller->processUpdate(
            $update,
            $mockTelegram,
            app(WhatsAppService::class),
            app(HolidayService::class)
        );
    }

    public function test_callback_action_masuk(): void
    {
        $emp = Employee::first(['*']);
        $this->assertNotNull($emp);
        Cache::put('telegram_sel_123456', [$emp->id]);

        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('answerCallbackQuery')
            ->with('cb_2', $this->stringContains('Memproses'))
            ->willReturn(true);

        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with('123456', $this->stringContains($emp->name))
            ->willReturn(true);

        $mockWa = $this->createMock(WhatsAppService::class);
        $mockWa->expects($this->once())
            ->method('send')
            ->with($emp->id, $this->anything(), $this->anything(), 'pre_checkin')
            ->willReturn(['success' => true]);

        $controller = app(TelegramWebhookController::class);
        $update = [
            'update_id' => 5,
            'callback_query' => [
                'id' => 'cb_2',
                'data' => 'tg_action:masuk',
                'message' => [
                    'message_id' => 999,
                    'chat' => ['id' => 123456],
                ],
            ],
        ];

        $controller->processUpdate(
            $update,
            $mockTelegram,
            $mockWa,
            app(HolidayService::class)
        );
    }

    public function test_pulang_with_specific_employee(): void
    {
        $emp = Employee::first(['*']);
        $this->assertNotNull($emp);

        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with('123456', $this->stringContains($emp->name))
            ->willReturn(true);

        $mockWa = $this->createMock(WhatsAppService::class);
        $mockWa->expects($this->once())
            ->method('send')
            ->with($emp->id, $this->anything(), $this->anything(), 'pre_checkout')
            ->willReturn(['success' => true]);

        $controller = app(TelegramWebhookController::class);
        $update = [
            'update_id' => 6,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => "/pulang {$emp->name}",
            ],
        ];

        $controller->processUpdate(
            $update,
            $mockTelegram,
            $mockWa,
            app(HolidayService::class)
        );
    }
}
