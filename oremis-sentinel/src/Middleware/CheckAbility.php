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
            if (in_array($ability, $tokenAbilities, true)) {
                return $next($request);
            }
        }

        abort(403, 'Forbidden: missing required ability');
    }
}
