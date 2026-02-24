<?php

namespace Database\Seeders\MetaDataSeeder;

use SajidJalal\\RolePermission\\Models\\RolesModel;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    // php artisan db:seed --class="Database\Seeders\MetaDataSeeder\RoleSeeder"
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => 1,
                'role_name' => 'super admin',
                'role_prefix' => "SUPER",
                'display_name' => 'Super Admin',
                'description' => null,
                'status' => 1,
                'is_admin' => 0,
                'sequence' => 0,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'role_name' => 'admin',
                'role_prefix' => "ADM",
                'display_name' => 'Admin',
                'description' => null,
                'is_admin' => 1,
                'status' => 1,
                'sequence' => 0,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'role_name' => 'pos',
                'role_prefix' => "POS",
                'display_name' => 'POS',
                'description' => null,
                'status' => 1,
                'is_admin' => 0,
                'sequence' => 0,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'role_name' => 'customer',
                'role_prefix' => "CUST",
                'display_name' => 'Customer',
                'description' => null,
                'status' => 1,
                'is_admin' => 0,
                'sequence' => 0,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'role_name' => 'digital partner',
                'role_prefix' => "DP",
                'display_name' => 'Digital Partner',
                'description' => null,
                'status' => 1,
                'is_admin' => 0,
                'sequence' => 0,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        // Using upsert for updating or inserting records
        RolesModel::upsert(
            $roles,
            ['id'],
            ['role_name', 'role_prefix', 'display_name', 'description', 'status', 'is_admin', 'sequence', 'updated_at', 'deleted_at']
        );
    }
}

