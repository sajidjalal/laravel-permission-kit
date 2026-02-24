<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use SajidJalal\PermissionKit\Database\Seeders\RolePermissionsSeeder as SeedersRolePermissionsSeeder;

class DatabaseSeeder extends Seeder
{
     public function run(): void
    {
        $this->call([
            SeedersRolePermissionsSeeder::class,
        ]);
    }
}
