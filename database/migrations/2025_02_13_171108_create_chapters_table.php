<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained();
            $table->string('title');
            $table->longText('content');
            $table->enum('content_type', ['text', 'video', 'audio', 'pdf', 'interactive'])->default('text');
            $table->string('media_url')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('order_position');
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->longText('description')->nullable()->comment('Description of the content');
            $table->json('metadata')->nullable()->comment('Additional content metadata');
            $table->timestamps();

            $table->index(['section_id', 'order_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
