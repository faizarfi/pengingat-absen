<?php

namespace App\Http\Controllers;

use App\Models\WaOutbox;
use App\Models\WaAgentHeartbeat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AgentApiController extends Controller
{
    /**
     * GET /api/agent/messages
     * Ambil pesan pending yang siap dikirim.
     */
    public function getMessages(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);

        $messages = WaOutbox::readyToSend()
            ->orderBy('scheduled_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit(min($limit, 50))
            ->get(['id', 'employee_id', 'phone_number', 'message', 'type', 'status', 'attempts', 'scheduled_at']);

        return response()->json([
            'success' => true,
            'count'   => $messages->count(),
            'data'    => $messages,
        ]);
    }

    /**
     * POST /api/agent/messages/{id}/processing
     * Tandai pesan sedang diproses oleh agent.
     */
    public function markProcessing(int $id): JsonResponse
    {
        $outbox = WaOutbox::find($id);

        if (!$outbox) {
            return response()->json(['success' => false, 'error' => 'Message not found'], 404);
        }

        if (!in_array($outbox->status, [WaOutbox::STATUS_PENDING, WaOutbox::STATUS_RETRY])) {
            return response()->json([
                'success' => false,
                'error'   => "Cannot process message with status: {$outbox->status}",
            ], 422);
        }

        $outbox->markProcessing();

        Log::info('Agent: message marked processing', ['outbox_id' => $id]);

        return response()->json(['success' => true, 'status' => $outbox->status]);
    }

    /**
     * POST /api/agent/messages/{id}/sent
     * Tandai pesan berhasil terkirim.
     */
    public function markSent(int $id, \App\Services\WhatsAppService $waService): JsonResponse
    {
        $outbox = WaOutbox::find($id);

        if (!$outbox) {
            return response()->json(['success' => false, 'error' => 'Message not found'], 404);
        }

        $outbox->markSent();

        Log::info('Agent: message sent successfully', [
            'outbox_id' => $id,
            'phone'     => $outbox->phone_number,
        ]);

        // Cek jika seluruh antrean batch telah selesai
        $waService->checkAndSendBatchCompletionReport();

        return response()->json(['success' => true, 'status' => $outbox->status]);
    }

    /**
     * POST /api/agent/messages/{id}/failed
     * Tandai pesan gagal dikirim.
     */
    public function markFailed(Request $request, int $id, \App\Services\WhatsAppService $waService): JsonResponse
    {
        $outbox = WaOutbox::find($id);

        if (!$outbox) {
            return response()->json(['success' => false, 'error' => 'Message not found'], 404);
        }

        $error = $request->input('error', 'Unknown error');
        $outbox->markFailed($error);

        Log::warning('Agent: message failed', [
            'outbox_id' => $id,
            'phone'     => $outbox->phone_number,
            'error'     => $error,
            'attempts'  => $outbox->attempts,
            'status'    => $outbox->status,
        ]);

        // Cek jika seluruh antrean batch telah selesai
        $waService->checkAndSendBatchCompletionReport();

        return response()->json([
            'success'  => true,
            'status'   => $outbox->status,
            'attempts' => $outbox->attempts,
        ]);
    }

    /**
     * POST /api/agent/heartbeat
     * Agent mengirimkan heartbeat untuk monitoring.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $agentName = $request->input('agent_name', 'default');
        $whatsappReady = (bool) $request->input('whatsapp_ready', true);
        $metadata = $request->input('metadata', []);

        $heartbeat = WaAgentHeartbeat::beat($agentName, $whatsappReady, $metadata);

        return response()->json([
            'success'      => true,
            'agent_name'   => $heartbeat->agent_name,
            'last_seen_at' => $heartbeat->last_seen_at->toDateTimeString(),
        ]);
    }

    /**
     * GET /api/agent/status
     * Cek status agent dan statistik outbox.
     */
    public function status(): JsonResponse
    {
        $agent = WaAgentHeartbeat::where('agent_name', 'default')->first();

        $stats = [
            'pending'    => WaOutbox::pending()->count(),
            'processing' => WaOutbox::processing()->count(),
            'sent_today' => WaOutbox::where('status', WaOutbox::STATUS_SENT)->today()->count(),
            'failed'     => WaOutbox::failed()->today()->count(),
            'retry'      => WaOutbox::retry()->count(),
        ];

        return response()->json([
            'success' => true,
            'agent'   => $agent ? [
                'name'            => $agent->agent_name,
                'status'          => $agent->isOnline() ? 'online' : 'offline',
                'whatsapp_ready'  => $agent->whatsapp_ready,
                'last_seen_at'    => $agent->last_seen_at?->toDateTimeString(),
            ] : null,
            'stats' => $stats,
        ]);
    }
}
