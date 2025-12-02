<?php

namespace Oremis\Sentinel\Services;

class TokenAbilityService
{
    /**
     * Get all abilities from the current request token.
     */
    public function abilities(): array
    {
        return request()->attributes->get('abilities', []);
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
            if (in_array($ability, $tokenAbilities, true)) {
                return true;
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
            abort(403, 'Forbidden: insufficient ability');
        }
    }

    /**
     * Get the user_id linked to the token (for audit).
     */
    public function userId(): ?int
    {
        return request()->attributes->get('token_user_id');
    }
}
