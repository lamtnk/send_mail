<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->unsignedTinyInteger('role')->default(0)->comment('0: default, 1: admin'); // Thêm cột role
            $table->unsignedTinyInteger('department')->nullable(); // Thêm cột department, cho phép null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
