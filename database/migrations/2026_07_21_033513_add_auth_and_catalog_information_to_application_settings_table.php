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
            $table->string('auth_login_title')->nullable()->after('home_features');
            $table->string('auth_register_title')->nullable()->after('auth_login_title');
            $table->string('catalog_information_heading')->nullable()->after('auth_register_title');
            $table->json('catalog_information_items')->nullable()->after('catalog_information_heading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn([
                'auth_login_title',
                'auth_register_title',
                'catalog_information_heading',
                'catalog_information_items',
            ]);
        });
    }
};
