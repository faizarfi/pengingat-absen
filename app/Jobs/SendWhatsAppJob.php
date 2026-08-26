<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $logId;
    public int $employeeId;
    public string $phone;
    public string $message;
    public string $type;
    public int $attempts = 0;

    public function __construct(int $logId, int $employeeId, string $phone, string $message, string $type = 'manual')
    {
        $this->logId = $logId;
        $this->employeeId = $employeeId;
        $this->phone = $phone;
        $this->message = $message;
        $this->type = $type;
    }

    public function handle(WhatsAppService $wa): void
    {
        $result = $wa->sendMessage($this->phone, $this->message);

        // If rate limited, don't retry - just mark as failed
        if (isset($result['error']) && $result['error'] === 'rate_limited') {
            DB::table('wa_logs')
                ->where('id', $this->logId)
                ->update([
                    'status' => 'rate_limited',
                    'updated_at' => now(),
                ]);

            Log::warning('SendWhatsAppJob rate limited - not retrying', [
                'employee_id' => $this->employeeId,
                'phone' => $this->phone,
                'type' => $this->type,
                'log_id' => $this->logId,
                'retry_after' => $result['retry_after'] ?? 60,
            ]);

            // Release job back to queue with delay based on retry_after
            $retryAfter = $result['retry_after'] ?? 60;
            $this->release($retryAfter);
            return;
        }

        // If successful
        if ($result['success']) {
            DB::table('wa_logs')
                ->where('id', $this->logId)
                ->update([
                    'sent_at' => now(),
                    'status' => 'sent',
                    'updated_at' => now(),
                ]);
            Log::info('SendWhatsAppJob sent successfully', [
                'employee_id' => $this->employeeId,
                'phone' => $this->phone,
            ]);
            return;
        }

        // Other failures - mark as failed
        DB::table('wa_logs')
            ->where('id', $this->logId)
            ->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);

        Log::warning('SendWhatsAppJob failed', [
            'employee_id' => $this->employeeId,
            'phone' => $this->phone,
            'type' => $this->type,
            'log_id' => $this->logId,
            'error' => isset($result['error']) ? $result['error'] : 'unknown',
            'result' => $result,
        ]);
    }

    public function failed(\Exception $exception)
    {
        // Guard against uninitialized typed properties when job is unserialized
        try {
            $rp = new \ReflectionProperty($this, 'logId');
            $isInitialized = $rp->isInitialized($this);
        } catch (\ReflectionException $e) {
            $isInitialized = false;
        }

        if ($isInitialized) {
            DB::table('wa_logs')
                ->where('id', $this->logId)
                ->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);
        }

        Log::error('SendWhatsAppJob permanently failed', [
            'employee_id' => $this->employeeId ?? null,
            'phone' => $this->phone ?? null,
            'log_id' => $isInitialized ? $this->logId : null,
            'exception' => $exception->getMessage(),
        ]);
    }
}

