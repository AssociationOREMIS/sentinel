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

            return $next($request);
        }

        // Remote validation via Identity Provider
        $response = Http::get($baseUrl . $endpoint, [
            'token' => $token,
        ]);

        if ($response->failed() || $response->json('valid') !== true) {
            abort(401, 'Invalid or expired token');
        }

        $data = [
            'abilities' => $response->json('abilities', []),
            'user_id'   => $response->json('user_id'),
        ];

        // Cache validation result
        cache()->put($cacheKey, $data, $cacheTtl);

        // Inject into the request
        $request->attributes->set('abilities', $data['abilities']);
        $request->attributes->set('token_user_id', $data['user_id']);

        return $next($request);
    }
}
