<?php

declare(strict_types=1);

use App\Models\Section;
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
            $table->foreignIdFor(Section::class)->constrained();
            $table->string('title')->unique();
            $table->longText('content');
            $table->string('content_type')->default('text');
            $table->string('media_url')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('order_position')->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->longText('description')->nullable()->comment('Description of the content');
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
