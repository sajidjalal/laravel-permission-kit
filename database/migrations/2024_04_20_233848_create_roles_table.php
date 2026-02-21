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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->integer('reporting_role_id')->default(0);
            $table->string('role_name', 80);
            $table->string('display_name', 100)->nullable();
            $table->string('role_prefix',10)->nullable();
            $table->string('description', 250)->nullable();
            $table->tinyInteger('is_admin')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('sequence')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
