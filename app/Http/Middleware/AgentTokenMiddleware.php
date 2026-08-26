<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentTokenMiddleware
{
    /**
     * Validasi Bearer Token untuk WA Desktop Agent API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configToken = config('whatsapp.agent_token', '');

        if (empty($configToken) || $configToken === 'change-this-token') {
            return response()->json([
                'error' => 'Agent token not configured on server',
            ], 500);
        }

        $bearerToken = $request->bearerToken();

        if (empty($bearerToken) || $bearerToken !== $configToken) {
            return response()->json([
                'error' => 'Unauthorized — invalid or missing agent token',
            ], 401);
        }

        return $next($request);
    }
}
