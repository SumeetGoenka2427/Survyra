<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if (! $plain) {
            return response()->json(['error' => 'API key required.'], 401);
        }

        $apiKey = ApiKey::where('key_hash', hash('sha256', $plain))
            ->where('is_active', true)
            ->first();

        if (! $apiKey || ($apiKey->expires_at && $apiKey->expires_at->isPast())) {
            return response()->json(['error' => 'Invalid or expired API key.'], 401);
        }

        $apiKey->update(['last_used_at' => now()]);
        $request->attributes->set('api_client', $apiKey->client);

        return $next($request);
    }
}
