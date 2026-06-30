<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('questions')
            ->whereIn('question_type', ['text', 'essay'])
            ->update(['question_type' => 'single_choice']);
    }

    public function down(): void
    {
        // No rollback — data was already converted
    }
};
