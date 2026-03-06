🛡️ Laravel Permission Kit — RBAC for Laravel

A lightweight, configurable, and database-driven Role-Based Access
Control (RBAC) package for Laravel.

This package helps manage roles, permissions, and menu-based access
control in Laravel applications.

------------------------------------------------------------------------

✨ Features

-   Role Based Access Control (RBAC)
-   Menu & Sub Menu permission structure
-   Super Admin bypass support
-   Custom table names support
-   Cache optimized permission checks
-   Simple Facade based API
-   Lightweight and easy to integrate
-   Works with existing Laravel projects

------------------------------------------------------------------------

🚀 Installation

Install the package using composer.

    composer require sajidjalal/laravel-permission-kit

------------------------------------------------------------------------

⚙️ Publish Configuration

Publish the configuration file.

    php artisan vendor:publish --provider="SajidJalal\PermissionKit\PermissionKitServiceProvider"

This will create the config file:

config/permission-kit.php

------------------------------------------------------------------------

🗄️ Run Migration

Run the migration command to create required tables.

    php artisan migrate

------------------------------------------------------------------------

⚙️ Configuration

You can customize the package configuration from:

config/permission-kit.php

Example configuration:

    return [

        'tables' => [
            'master_menu'      => 'rbac_master_menu',
            'roles'            => 'rbac_roles',
            'role_permissions' => 'rbac_role_permissions',
        ],

        'roles' => [
            'super_admin_id' => env('SUPER_ADMIN_ROLE_ID', 1),
        ],

        'cache' => [
            'ttl' => env('PERMISSION_CACHE_TTL', 1440),
        ],

    ];

------------------------------------------------------------------------

📚 Usage

Import the Permission facade.

    use SajidJalal\PermissionKit\Facades\Permission;

------------------------------------------------------------------------

📌 Get All Menus

Fetch complete menu structure with permission hierarchy.

    $menus = Permission::getAllMenus();

------------------------------------------------------------------------

🔐 Check Role Permission

Check if a role has permission for specific actions.

Check Read Permission

    Permission::checkRoleHasPermission(1, 'role_management', ['read']);

Check Multiple Permissions

    Permission::checkRoleHasPermission(1, 'role_management', ['read','create']);

Check Any Permission

    Permission::checkRoleHasPermission(1, 'role_management', []);

If the role has any permission, the method will return true.

------------------------------------------------------------------------

📋 Available Permission Actions

    $actions = ['read', 'create', 'update', 'delete'];

These correspond to the columns inside the role_permissions table.

------------------------------------------------------------------------

👑 Super Admin Support

Super Admin bypasses all permission checks.

Set the Super Admin role in .env.

    SUPER_ADMIN_ROLE_ID=1

------------------------------------------------------------------------

⚡ Permission Cache

Permission checks are cached to improve performance.

    PERMISSION_CACHE_TTL=1440

(Default: 1440 minutes / 24 hours)

------------------------------------------------------------------------

🧩 Database Tables

This package creates the following tables.

  Table                   Description
  ----------------------- ----------------------------------
  rbac_master_menu        Stores menus and permission keys
  rbac_roles              Stores application roles
  rbac_role_permissions   Stores role wise permissions

------------------------------------------------------------------------

  ⚠️ Note

  This package is currently under development.
  Some features and APIs may change in upcoming versions.

------------------------------------------------------------------------

📄 License

This package is open-sourced software licensed under the MIT license.
