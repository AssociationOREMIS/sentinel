<?php

namespace Oremis\Sentinel;

use Illuminate\Support\ServiceProvider;
use Oremis\Sentinel\Services\TokenAbilityService;
use Oremis\Sentinel\Middleware\CheckAbility;
use Oremis\Sentinel\Middleware\CheckRemoteToken;

class SentinelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/sentinel.php', 'sentinel');

        // Bind the TokenAbility service
        $this->app->singleton('token_ability', function () {
            return new TokenAbilityService();
        });
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/sentinel.php' => config_path('sentinel.php'),
        ], 'sentinel-config');

        // Register route middleware aliases
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];

        $router->aliasMiddleware('remote.token', CheckRemoteToken::class);
        $router->aliasMiddleware('ability', CheckAbility::class);
    }
}
