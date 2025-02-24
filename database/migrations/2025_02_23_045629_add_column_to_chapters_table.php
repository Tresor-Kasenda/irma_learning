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
        Schema::table('chapters', function (Blueprint $table) {
            $table->boolean('is_final_chapter')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('reference_code')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('is_final_chapter');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('reference_code')->nullable()->unique();
        });
    }
};
