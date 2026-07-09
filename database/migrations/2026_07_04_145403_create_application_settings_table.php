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
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('IRMA Learning');
            $table->string('app_tagline')->nullable();
            $table->string('support_email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 20)->default('#a23362');
            $table->string('default_currency', 3)->default('USD');
            $table->boolean('allow_registration')->default(true);
            $table->text('maintenance_message')->nullable();
            $table->string('certificate_signature_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};
