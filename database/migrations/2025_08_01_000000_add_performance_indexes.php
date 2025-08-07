<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'subscriptions_user_status_index');
            $table->index(['master_class_id', 'status'], 'subscriptions_course_status_index');
            $table->index(['started_at'], 'subscriptions_started_at_index');
            $table->index(['progress'], 'subscriptions_progress_index');
        });

        Schema::table('master_classes', function (Blueprint $table) {
            $table->index(['status'], 'master_classes_status_index');
            $table->index(['price'], 'master_classes_price_index');
            $table->index(['created_at'], 'master_classes_created_at_index');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->index(['master_class_id', 'position'], 'chapters_course_position_index');
        });

        Schema::table('chapter_progress', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'chapter_progress_user_status_index');
            $table->index(['subscription_id'], 'chapter_progress_subscription_index');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_user_status_index');
            $table->dropIndex('subscriptions_course_status_index');
            $table->dropIndex('subscriptions_started_at_index');
            $table->dropIndex('subscriptions_progress_index');
        });

        Schema::table('master_classes', function (Blueprint $table) {
            $table->dropIndex('master_classes_status_index');
            $table->dropIndex('master_classes_price_index');
            $table->dropIndex('master_classes_created_at_index');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex('chapters_course_position_index');
        });

        Schema::table('chapter_progress', function (Blueprint $table) {
            $table->dropIndex('chapter_progress_user_status_index');
            $table->dropIndex('chapter_progress_subscription_index');
        });
    }
};
