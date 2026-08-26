<?php

return [
    // ── Legacy Fonnte/Provider (akan dihapus setelah migrasi stabil) ──
    'url'          => env('WHATSAPP_API_URL', ''),
    'key'          => env('WHATSAPP_API_KEY', ''),
    'from'         => env('WHATSAPP_FROM', ''),
    'admin_number' => env('ADMIN_WA_NUMBER', ''),

    // ── WhatsApp Desktop Automation ──
    // Driver: 'desktop' (baru, via outbox + agent) atau 'api' (legacy Fonnte)
    'driver'             => env('WA_DRIVER', 'desktop'),

    // Agent settings
    'agent_enabled'      => env('WA_AGENT_ENABLED', true),
    'agent_token'        => env('WA_AGENT_TOKEN', 'change-this-token'),
    'send_delay'         => (int) env('WA_SEND_DELAY', 5),
    'max_retry'          => (int) env('WA_MAX_RETRY', 3),
    'agent_timeout'      => (int) env('WA_AGENT_TIMEOUT', 30),

    // Agent REST API (jika agent di PC berbeda)
    'agent_api_enabled'  => env('WA_AGENT_API_ENABLED', true),
    'heartbeat_timeout'  => (int) env('WA_AGENT_HEARTBEAT_TIMEOUT', 60),
];
