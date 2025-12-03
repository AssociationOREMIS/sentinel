<?php

namespace Oremis\Sentinel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckRemoteToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            abort(401, 'Missing access token');
        }

        $baseUrl   = rtrim(config('sentinel.base_url'), '/');
        $endpoint  = '/' . ltrim(config('sentinel.validate_endpoint'), '/');
        $cacheTtl  = (int) config('sentinel.cache_ttl', 15);

        $cacheKey = 'sentinel_token_validation_' . hash('sha256', $token);

        // Try cache first
        if ($cached = cache()->get($cacheKey)) {
            $request->attributes->set('abilities', $cached['abilities']);
            $request->attributes->set('token_user_id', $cached['user_id'] ?? null);
            $request->attributes->set('token_service_id', $cached['service_id'] ?? null);

            return $next($request);
        }

        // Remote validation via Identity Provider
        $response = Http::get($baseUrl . $endpoint, [
            'token' => $token,
        ]);

        $payload = $response->json('data', []);

        if ($response->failed() || ($payload['valid'] ?? false) !== true) {
            abort(401, 'Invalid or expired token');
        }

        $data = [
            'abilities'  => $payload['abilities'] ?? [],
            'user_id'    => $payload['user_id'] ?? null,
            'service_id' => $payload['service_id'] ?? null,
        ];

        // Cache validation result
        cache()->put($cacheKey, $data, $cacheTtl);

        // Inject into the request
        $request->attributes->set('abilities', $data['abilities']);
        $request->attributes->set('token_user_id', $data['user_id']);
        $request->attributes->set('token_service_id', $data['service_id']);

        return $next($request);
    }
}
