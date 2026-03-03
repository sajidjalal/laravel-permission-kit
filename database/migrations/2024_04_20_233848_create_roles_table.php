<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SajidJalal\PermissionKit\Models\MasterMenuModel;
use SajidJalal\PermissionKit\Models\RolePermissionsModel;
use SajidJalal\PermissionKit\Models\RolesModel;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporting_role_id')->default(0)->nullable();
            $table->string('role_name', 80);
            $table->string('display_name', 100)->nullable();
            $table->string('role_prefix', 25)->nullable();
            $table->string('description', 250)->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('sequence')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index on frequently queried columns
            $table->index('status');
            $table->index('is_admin');
            $table->index(['created_by', 'created_at']);
        });

        Schema::create('master_menu', function (Blueprint $table) {
            $table->id();
            $table->string('permissions_name', 50);
            $table->string('display_permissions_name', 50);
            $table->string('menu_for', 50)->default('admin');
            $table->string('sub_menu_for', 50)->nullable();
            $table->string('group_name', 50)->nullable();
            $table->string('menu_name', 80);
            $table->unsignedSmallInteger('sequence')->nullable();
            $table->string('url', 100);
            $table->string('menu_description', 150)->nullable();
            $table->string('icon', 80)->nullable();
            $table->string('fa_icon', 80)->nullable();
            $table->unsignedBigInteger('parent_id')->default(0)->nullable();
            $table->boolean('is_menu_show')->default(true);
            $table->boolean('is_permission_show')->default(true);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('parent_id');
            $table->index('status');
            $table->index(['menu_for', 'status']);
            $table->index(['group_name', 'status']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_id');
            $table->boolean('create')->default(false);
            $table->boolean('read')->default(false);
            $table->boolean('update')->default(false);
            $table->boolean('delete')->default(false);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Constraints
            $table->unique(['role_id', 'menu_id']);
            $table->index('status');
        });

        $roles = [
            [
                'id' => 1,
                'role_name' => 'super admin',
                'role_prefix' => "Super",
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


        ];

        // Using upsert for updating or inserting records
        RolesModel::upsert(
            $roles,
            ['id'],
            ['role_name', 'role_prefix', 'display_name', 'description', 'status', 'is_admin', 'sequence', 'updated_at', 'deleted_at']
        );

        $master_menu = [
            [
                'id' => 1,
                'permissions_name' => 'group',
                'display_permissions_name' => 'Permission Management',
                'menu_for' => 'admin',
                'sub_menu_for' => null,
                'group_name' => 'Application',
                'menu_name' => 'Roles & Permission',
                'sequence' => 1,
                'url' => 'permission',
                'menu_description' => null,
                'icon' => 'images/svg/roles-and-permission.svg',
                'fa_icon' => 'fa fa-thumb-tack',
                'parent_id' => 0,
                'is_menu_show' => 1,
                'is_permission_show' => 1,
                'status' => 1,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => '2026-02-24 18:03:22',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'permissions_name' => 'role_management',
                'display_permissions_name' => 'Role Management',
                'menu_for' => 'admin',
                'sub_menu_for' => null,
                'group_name' => 'Application',
                'menu_name' => 'Roles List',
                'sequence' => 1,
                'url' => 'roles',
                'menu_description' => null,
                'icon' => 'images/svg/icon-sprite.svg#stroke-learning',
                'fa_icon' => 'fa fa-thumb-tack',
                'parent_id' => 1,
                'is_menu_show' => 1,
                'is_permission_show' => 1,
                'status' => 1,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => '2026-02-24 18:03:22',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ];

        MasterMenuModel::upsert(
            $master_menu,
            ['id'],
            ['permissions_name', 'display_permissions_name', 'menu_for', 'sub_menu_for', 'group_name', 'menu_name', 'sequence', 'url', 'menu_description', 'icon', 'fa_icon', 'parent_id', 'is_menu_show', 'is_permission_show', 'status', 'updated_at', 'deleted_at']
        );

        $role_permissions = [
            [
                'id' => 1,
                'role_id' => 1,
                'menu_id' => 1,
                'create' => 1,
                'read' => 1,
                'update' => 1,
                'delete' => 1,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'role_id' => 1,
                'menu_id' => 2,
                'create' => 1,
                'read' => 1,
                'update' => 1,
                'delete' => 1,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        RolePermissionsModel::upsert(
            $role_permissions,
            ['id'],
            ['role_id', 'menu_id', 'create', 'read', 'update', 'delete', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at']
        );
    }

    public function down(): void
    {
        // Drop in reverse order (foreign keys first)
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('master_menu');
        Schema::dropIfExists('roles');
    }
};
