<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropUnique('sections_title_unique');
            $table->unique(['formation_id', 'title'], 'sections_formation_title_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropUnique('sections_formation_title_unique');
            $table->unique('title', 'sections_title_unique');
        });
    }
};
