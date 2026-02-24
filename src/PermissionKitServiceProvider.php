<?php

namespace SajidJalal\PermissionKit;

use Illuminate\Support\ServiceProvider;
use SajidJalal\PermissionKit\Helpers\PermissionHelper;

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
        $seedersPath = __DIR__ . '/../database/seeders';

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

        // Publish seeders
        if (is_dir($seedersPath)) {
            $this->publishes([
                $seedersPath => database_path('seeders'),
            ], 'permission-kit-seeders');
        }
    }
}
