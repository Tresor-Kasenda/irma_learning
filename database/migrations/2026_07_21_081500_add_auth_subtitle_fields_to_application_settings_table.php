<?php

declare(strict_types=1);

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
        Schema::table('application_settings', function (Blueprint $table) {
            $table->string('auth_page_subtitle')->nullable()->after('catalog_information_items');
            $table->string('auth_login_subtitle')->nullable()->after('auth_page_subtitle');
            $table->string('auth_register_subtitle')->nullable()->after('auth_login_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn([
                'auth_page_subtitle',
                'auth_login_subtitle',
                'auth_register_subtitle',
            ]);
        });
    }
};
