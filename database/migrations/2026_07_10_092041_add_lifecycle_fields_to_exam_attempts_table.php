<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('status', 32)->default('in_progress')->change();
            $table->timestamp('expires_at')->nullable()->after('started_at')->index();
            $table->timestamp('last_activity_at')->nullable()->after('expires_at');
            $table->timestamp('reopened_at')->nullable()->after('last_activity_at');
            $table->foreignId('reopened_by')->nullable()->after('reopened_at')->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('reopen_count')->default(0)->after('reopened_by');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE exam_attempts DROP CONSTRAINT IF EXISTS exam_attempts_status_check');
            DB::statement("ALTER TABLE exam_attempts ADD CONSTRAINT exam_attempts_status_check CHECK (status IN ('in_progress', 'completed', 'failed', 'cancelled', 'expired'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('exam_attempts')->where('status', 'expired')->update(['status' => 'cancelled']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE exam_attempts DROP CONSTRAINT IF EXISTS exam_attempts_status_check');
        }

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropForeign(['reopened_by']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn([
                'expires_at',
                'last_activity_at',
                'reopened_at',
                'reopened_by',
                'reopen_count',
            ]);
            $table->enum('status', ['in_progress', 'completed', 'failed', 'cancelled'])
                ->default('in_progress')
                ->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE exam_attempts ADD CONSTRAINT exam_attempts_status_check CHECK (status IN ('in_progress', 'completed', 'failed', 'cancelled'))");
        }
    }
};
