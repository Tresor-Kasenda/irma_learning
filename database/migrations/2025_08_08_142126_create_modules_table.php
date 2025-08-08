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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_position');
            $table->integer('estimated_duration')->nullable()->comment('Duration in minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['formation_id', 'order_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
