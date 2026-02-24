<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
    }

    public function down(): void
    {
        // Drop in reverse order (foreign keys first)
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('master_menu');
        Schema::dropIfExists('roles');
    }
};
