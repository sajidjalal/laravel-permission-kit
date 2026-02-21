<?php

namespace InsureTech\RolePermission;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RolePermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/role-permission.php', 'role-permission');

        $this->defineConstants();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'role-permission');

        $this->registerRoutes();

        $this->publishes([
            __DIR__ . '/../config/role-permission.php' => config_path('role-permission.php'),
        ], 'role-permission-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/role-permission'),
        ], 'role-permission-views');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'role-permission-migrations');

        $this->publishes([
            __DIR__ . '/../public' => public_path(config('role-permission.asset_path')),
        ], 'role-permission-assets');
    }

    protected function registerRoutes(): void
    {
        $middleware = config('role-permission.middleware', ['web']);
        $prefix = config('role-permission.route_prefix', '');

        Route::middleware($middleware)
            ->prefix($prefix)
            ->group(__DIR__ . '/../routes/web.php');
    }

    protected function defineConstants(): void
    {
        $constants = config('role-permission.constants', []);

        foreach ($constants as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}
