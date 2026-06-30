<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->index(['user_id', 'status', 'payment_status'], 'enrollments_user_status_payment_index');
            $table->index(['user_id', 'formation_id'], 'enrollments_user_formation_index');
        });

        Schema::table('user_progress', function (Blueprint $table): void {
            $table->index(['user_id', 'trackable_type', 'status'], 'user_progress_user_type_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropIndex('enrollments_user_status_payment_index');
            $table->dropIndex('enrollments_user_formation_index');
        });

        Schema::table('user_progress', function (Blueprint $table): void {
            $table->dropIndex('user_progress_user_type_status_index');
        });
    }
};
