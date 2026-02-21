<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_menu', function (Blueprint $table) {
            $table->id();
            $table->string('permissions_name', 50);
            $table->string('display_permissions_name', 50);
            $table->string('menu_for', 50)->default('admin');
            $table->string('sub_menu_for', 50)->nullable();
            $table->string('group_name', 50)->nullable();
            $table->string('menu_name', 80); 
            $table->unsignedInteger('sequence')->nullable();
            $table->string('url', 100);
            $table->string('menu_description', 150)->nullable();
            $table->string('icon', 80)->nullable();
            $table->string('fa_icon', 80)->nullable();
            $table->unsignedInteger('parent_id')->default(0)->index();
            $table->tinyInteger('is_menu_show')->default(1);
            $table->tinyInteger('is_permission_show')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->index()->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint for parent_id if it references the same table
            // $table->foreign('parent_id')->references('id')->on('master_menu')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_menu');
    }
};
