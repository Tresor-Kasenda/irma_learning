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
        Schema::table('chapters', function (Blueprint $table) {
            $table->string('processing_status')->nullable()->after('markdown_file');
            $table->text('processing_error')->nullable()->after('processing_status');
            $table->json('processing_metadata')->nullable()->after('processing_error');
            $table->timestamp('processing_started_at')->nullable()->after('processing_metadata');
            $table->timestamp('processed_at')->nullable()->after('processing_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn([
                'processing_status',
                'processing_error',
                'processing_metadata',
                'processing_started_at',
                'processed_at',
            ]);
        });
    }
};
