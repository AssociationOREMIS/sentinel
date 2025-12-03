<?php

namespace Oremis\Sentinel\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAbility
{
    public function handle(Request $request, Closure $next, ...$requiredAbilities)
    {
        $tokenAbilities = $request->attributes->get('abilities', []);

        // Full access: wildcard
        if (in_array('*', $tokenAbilities, true)) {
            return $next($request);
        }

        foreach ($requiredAbilities as $ability) {
            foreach ($tokenAbilities as $tokenAbility) {
                if (\Illuminate\Support\Str::is($tokenAbility, $ability)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Forbidden: missing required ability');
    }
}
