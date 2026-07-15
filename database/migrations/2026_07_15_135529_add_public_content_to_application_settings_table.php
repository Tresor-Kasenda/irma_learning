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
        Schema::table('application_settings', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('support_email');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
            $table->string('contact_address_primary')->nullable()->after('contact_phone');
            $table->string('contact_address_secondary')->nullable()->after('contact_address_primary');
            $table->string('home_hero_title')->nullable()->after('contact_address_secondary');
            $table->string('home_hero_subtitle')->nullable()->after('home_hero_title');
            $table->json('home_features')->nullable()->after('home_hero_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_email',
                'contact_phone',
                'contact_address_primary',
                'contact_address_secondary',
                'home_hero_title',
                'home_hero_subtitle',
                'home_features',
            ]);
        });
    }
};
