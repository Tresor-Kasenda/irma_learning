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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('formation_id')->constrained();
            $table->string('certificate_number')->unique();
            $table->timestamp('issue_date')->useCurrent();
            $table->timestamp('expiry_date')->nullable();
            $table->string('verification_hash', 64)->unique();
            $table->string('status')->default('active');
            $table->string('file_path')->nullable();
            $table->decimal('final_score', 5, 2);
            $table->json('metadata')->nullable()->comment('Additional certificate data');
            $table->timestamps();

            $table->index(['certificate_number']);
            $table->index(['verification_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
