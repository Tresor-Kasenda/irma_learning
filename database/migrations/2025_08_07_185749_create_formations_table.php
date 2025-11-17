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
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->mediumText('short_description')->nullable();
            $table->longText('description');
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('duration_hours');
            $table->string('difficulty_level')->default('beginner');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
