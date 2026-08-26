<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaAgentHeartbeat extends Model
{
    protected $table = 'wa_agent_heartbeats';

    protected $fillable = [
        'agent_name',
        'status',
        'whatsapp_ready',
        'last_seen_at',
        'metadata',
    ];

    protected $casts = [
        'last_seen_at'   => 'datetime',
        'whatsapp_ready' => 'boolean',
        'metadata'       => 'array',
    ];

    /**
     * Check if agent is considered online based on heartbeat timeout.
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen_at) {
            return false;
        }

        $timeout = (int) config('whatsapp.heartbeat_timeout', 60);
        return $this->last_seen_at->diffInSeconds(now()) <= $timeout;
    }

    /**
     * Update or create heartbeat for an agent.
     */
    public static function beat(string $agentName = 'default', bool $whatsappReady = true, array $metadata = []): self
    {
        return static::updateOrCreate(
            ['agent_name' => $agentName],
            [
                'status'          => 'online',
                'whatsapp_ready'  => $whatsappReady,
                'last_seen_at'    => now(),
                'metadata'        => $metadata,
            ]
        );
    }
}
