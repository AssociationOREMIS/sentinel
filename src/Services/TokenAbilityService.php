<?php

namespace Oremis\Sentinel\Services;

use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TokenAbilityService
{
    /**
     * Get all abilities from the current request token.
     */
    public function abilities(): array
    {
        return Request::instance()->attributes->get('abilities', []);
    }

    /**
     * Check if the current token has at least one of the given abilities.
     *
     * @param  string|array  $abilities
     */
    public function can(string|array $abilities): bool
    {
        $tokenAbilities = $this->abilities();

        // Full access wildcard
        if (in_array('*', $tokenAbilities, true)) {
            return true;
        }

        foreach ((array) $abilities as $ability) {
            foreach ($tokenAbilities as $tokenAbility) {
                if (\Illuminate\Support\Str::is($tokenAbility, $ability)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Require at least one of the given abilities, or abort(403).
     *
     * @param  string|array  $abilities
     */
    public function require(string|array $abilities): void
    {
        if (! $this->can($abilities)) {
            throw new HttpException(403, 'Forbidden: insufficient ability');
        }
    }

    /**
     * Get the user_id linked to the token (for audit).
     */
    public function userId(): ?int
    {
        return Request::instance()->attributes->get('token_user_id');
    }

    /**
     * Get the service_id linked to the token (for audit).
     */
    public function serviceId(): ?int
    {
        return Request::instance()->attributes->get('token_service_id');
    }
}
