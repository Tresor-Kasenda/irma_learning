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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\EventType::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('town');
            $table->string('address');
            $table->date('date');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->integer('duration');
            $table->string('image');
            $table->decimal('tarif_membre', 8, 2)->nullable();
            $table->decimal('tarif_non_membre', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
