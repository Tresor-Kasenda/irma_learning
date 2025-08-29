<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formation_access_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained();
            $table->string('code')->unique();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['formation_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_access_codes');
    }
};
