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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')
                ->nullable()
                ->unique()
                ->after('payment_transaction_id');

            $table->unique(['user_id', 'formation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'formation_id']);
            $table->dropUnique(['stripe_checkout_session_id']);
            $table->dropColumn('stripe_checkout_session_id');
        });
    }
};
