<?php

namespace App\Http\Middleware;

use App\Models\McpToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class McpAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = (string) $request->bearerToken();

        if ($plain === '') {
            return $this->unauthorized('Missing bearer token.');
        }

        $token = McpToken::findActive($plain);
        if (! $token) {
            return $this->unauthorized('Invalid or revoked token.');
        }

        $token->markUsed();
        $request->attributes->set('mcp_token', $token);

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id'      => null,
            'error'   => [
                'code'    => -32001,
                'message' => $message,
            ],
        ], 401, ['WWW-Authenticate' => 'Bearer']);
    }
}
