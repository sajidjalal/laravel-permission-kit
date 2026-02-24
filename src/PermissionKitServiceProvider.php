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
            $config = include $configPath;
            
            if (is_array($config)) {
                $this->mergeConfigFrom($configPath, 'permission-kit');
            }
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
        $configPath = __DIR__ . '/../config/permission-kit.php';
        
        if (file_exists($configPath)) {
            // Publish config
            $this->publishes([
                $configPath => config_path('permission-kit.php'),
            ], 'permission-kit-config');
        }

        
        // Publish migrations
        if ($this->app->runningInConsole()) {
            $migrationsPath = __DIR__ . '/../database/migrations';
            
            if (is_dir($migrationsPath)) {
                $this->publishes([
                    $migrationsPath => database_path('migrations'),
                ], 'permission-kit-migrations');
            }
        }
    }
}
