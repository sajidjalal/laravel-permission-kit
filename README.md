# InsureTech Role & Permission Package

This package contains the Role/Permission module extracted from InsureTech CRM.

## Install

### Option A: Local path (recommended while developing)
Add this to your app's `composer.json`:

```json
"repositories": [
  { "type": "path", "url": "packages/insuretech/role-permission" }
],
"require": {
  "sajidjalal/laravel-permission-kit": "*"
}
```

Then run:

```bash
composer update
```

### Option B: Git/VCS
Push this package to a repo and require it normally:

```bash
composer require sajidjalal/laravel-permission-kit
```

## Publish Assets / Config / Migrations

```bash
php artisan vendor:publish --tag=role-permission-config
php artisan vendor:publish --tag=role-permission-assets
php artisan vendor:publish --tag=role-permission-views
php artisan vendor:publish --tag=role-permission-migrations
```

Run migrations:

```bash
php artisan migrate
```

## Seed Data (Optional but needed for UI)

This package ships seeders for roles, master_menu, and role_permissions. Run them if you want the default CRM menu/permissions:

```bash
php artisan db:seed --class="Database\\Seeders\\MetaDataSeeder\\MasterMenuSeeder"
php artisan db:seed --class="Database\\Seeders\\MetaDataSeeder\\RoleSeeder"
php artisan db:seed --class="Database\\Seeders\\MetaDataSeeder\\RolePermissionsSeeder"
```

## Required Host App Functions

The extracted code still calls existing global helpers from the host app. Make sure these exist:

- `getCurrentUser()`
- `getBrokerInfo()`
- `getFinancialYearList()`
- `data_table_updated_query_filter()`
- `saveErrorLog()`
- `trackBrokerErrorLog()`
- `flushQueryCache()` (provided by this package, but may already exist in your app)

## Config

See `config/role-permission.php` for:

- route prefix and middleware
- asset path
- default constants (role ids, UI formats, etc)

## Routes

The package registers:

- `GET /roles`
- `POST /get-roles`
- `POST|ANY /role-add-edit`
- `POST|ANY /role-delete`
- `GET /permission`
- `POST /add-update-permission`
- `GET /get-role-permission`

Middleware and prefix are configurable in `config/role-permission.php`.
