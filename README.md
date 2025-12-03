# OREMIS Sentinel

**Sentinel** is a Laravel package designed for the OREMIS ecosystem. It provides a centralized mechanism for validating API tokens against the OREMIS Identity Provider (Data) and managing access control via abilities.

This package allows client applications (like **App**, **Pio**, etc.) to offload authentication and authorization logic to a central authority while maintaining high performance through local caching.

## Features

- **Remote Token Validation**: Validates Bearer tokens against the OREMIS Identity Provider.
- **Ability-Based Access Control**: Checks if a token has the required permissions (abilities).
- **Context Awareness**: Distinguishes between User tokens (`user_id`) and Service Account tokens (`service_id`).
- **Performance**: Caches validation results locally to minimize network requests.
- **Laravel Integration**: Provides Middleware, Facades, and a Service Provider for seamless integration.

## Installation

Install the package via Composer:

```bash
composer require oremis/sentinel
```

The package will automatically register its Service Provider and Facade.

## Configuration

Publish the configuration file to your application:

```bash
php artisan vendor:publish --tag=sentinel-config
```

This will create `config/sentinel.php`. You can configure the behavior using environment variables in your `.env` file:

```dotenv
# The base URL of the OREMIS Identity Provider
SENTINEL_BASE_URL=https://data.oremis.dev

# The endpoint used to validate tokens
SENTINEL_VALIDATE_ENDPOINT=/api/validate-token

# How long (in seconds) to cache the validation result
SENTINEL_CACHE_TTL=15
```

## Middleware Registration (Laravel 11+)

In Laravel 11, you may need to manually register the middleware aliases in your `bootstrap/app.php` file to use them as string aliases in your routes.

```php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'remote.token'     => \Oremis\Sentinel\Middleware\CheckRemoteToken::class,
            'sentinel.ability' => \Oremis\Sentinel\Middleware\CheckAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
```

## How It Works

1.  **Incoming Request**: An API request arrives at your application with a `Authorization: Bearer <token>` header.
2.  **Middleware Interception**: The `remote.token` middleware intercepts the request.
3.  **Cache Check**: It checks if the token's validity is already cached locally.
4.  **Remote Validation**: If not cached, it sends a request to the configured Identity Provider (`SENTINEL_BASE_URL`).
    - The IDP returns the token's validity, associated abilities, and the owner (User or Service).
5.  **Context Injection**: The middleware injects the `abilities`, `token_user_id`, and `token_service_id` into the request attributes.
6.  **Authorization**: The `sentinel.ability` middleware (if used) checks if the injected abilities match the route requirements.

## Usage

### 1. Protecting Routes

Apply the middleware to your API routes. You typically need `remote.token` to validate the user, and optionally `sentinel.ability` to enforce permissions.

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['remote.token'])->group(function () {

    // Route accessible to any valid token
    Route::get('/profile', function () {
        // ...
    });

    // Route requiring specific abilities
    Route::middleware('sentinel.ability:ca.gdpr:create')->post('/gdpr-records', function () {
        // ...
    });

    // Route requiring ANY of the listed abilities
    Route::middleware('sentinel.ability:admin,editor')->group(function () {
        // ...
    });
});
```

### 2. Using the Facade

You can use the `TokenAbility` facade within your controllers or services to check permissions or retrieve context.

```php
use Oremis\Sentinel\Facades\TokenAbility;

public function store()
{
    // Check for a specific ability
    if (TokenAbility::can('ca.gdpr:delete')) {
        // ...
    }

    // Enforce an ability (throws 403 if missing)
    TokenAbility::require('ca.gdpr:create');

    // Get the ID of the authenticated user (if it's a user token)
    $userId = TokenAbility::userId();

    // Get the ID of the service account (if it's a service token)
    $serviceId = TokenAbility::serviceId();

    // Get all abilities
    $abilities = TokenAbility::abilities();
}
```

### 3. Response Structure

The package expects the Identity Provider to return data in the following format:

```json
{
  "data": {
    "valid": true,
    "abilities": ["ca.gdpr:create", "user.read"],
    "service_id": 2,
    "user_id": null
  }
}
```

- **valid**: Boolean indicating if the token is active.
- **abilities**: Array of permission strings.
- **service_id**: Integer ID if the token belongs to a service account (otherwise null).
- **user_id**: Integer ID if the token belongs to a user (otherwise null).

## License

AGPL-3.0-or-later
