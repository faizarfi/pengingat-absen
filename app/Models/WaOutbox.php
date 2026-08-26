<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaOutbox extends Model
{
    protected $table = 'wa_outbox';

    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SENT       = 'sent';
    const STATUS_FAILED     = 'failed';
    const STATUS_RETRY      = 'retry';
    const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'employee_id',
        'phone_number',
        'message',
        'type',
        'status',
        'attempts',
        'scheduled_at',
        'processing_at',
        'sent_at',
        'last_error',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'processing_at' => 'datetime',
        'sent_at'       => 'datetime',
        'attempts'      => 'integer',
    ];

    // ── Relationships ──

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Scopes ──

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRetry($query)
    {
        return $query->where('status', self::STATUS_RETRY);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeReadyToSend($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_RETRY])
                     ->where(function ($q) {
                         $q->whereNull('scheduled_at')
                           ->orWhere('scheduled_at', '<=', now());
                     });
    }

    // ── Helpers ──

    public function markProcessing(): self
    {
        $this->update([
            'status'        => self::STATUS_PROCESSING,
            'processing_at' => now(),
        ]);
        return $this;
    }

    public function markSent(): self
    {
        $this->update([
            'status'  => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
        return $this;
    }

    public function markFailed(string $error = ''): self
    {
        $maxRetry = (int) config('whatsapp.max_retry', 3);
        $newAttempts = $this->attempts + 1;

        $this->update([
            'status'     => $newAttempts >= $maxRetry ? self::STATUS_FAILED : self::STATUS_RETRY,
            'attempts'   => $newAttempts,
            'last_error' => $error,
        ]);
        return $this;
    }

    public function markCancelled(): self
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
        return $this;
    }
}
