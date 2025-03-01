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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained();
            $table->foreignId('chapter_id')->constrained();
            $table->foreignId('student_id')->constrained('users');
            $table->foreignId('evaluated_by')->constrained('users');
            $table->decimal('score', 5, 2);
            $table->text('feedback')->nullable();
            $table->enum('status', ['passed', 'failed', 'pending']);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
