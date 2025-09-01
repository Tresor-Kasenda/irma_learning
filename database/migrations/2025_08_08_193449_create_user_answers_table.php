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
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained();
            $table->foreignId('question_id')->constrained();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options');
            $table->text('answer_text')->nullable()->comment('For text/essay questions');
            $table->boolean('is_correct')->default(false);
            $table->json('selected_options')->nullable()->after('selected_option_id');
            $table->integer('points_earned')->default(0)->after('is_correct');
            $table->timestamps();

            $table->index(['exam_attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
    }
};
