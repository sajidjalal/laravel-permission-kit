<?php

namespace sj\PermissionKit;

use Illuminate\Support\ServiceProvider;
use sj\PermissionKit\Helpers\PermissionHelper;

class PermissionKitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/permission-kit.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'permission-kit');
        }

        // Bind PermissionHelper to container
        $this->app->singleton('permission-kit', function ($app) {
            return new PermissionHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $configPath = __DIR__ . '/../config/permission-kit.php';
        $migrationsPath = __DIR__ . '/../database/migrations';

        // Publish config
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('permission-kit.php'),
            ], 'permission-kit-config');
        }

        // Publish migrations
        if (is_dir($migrationsPath)) {
            $this->publishes([
                $migrationsPath => database_path('migrations'),
            ], 'permission-kit-migrations');
        }
    }
}
