<?php

declare(strict_types=1);

use App\Enums\TrainingStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->integer('duration')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('status')->default(TrainingStatusEnum::PUBLISHED->value);
            $table->mediumText('content')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
