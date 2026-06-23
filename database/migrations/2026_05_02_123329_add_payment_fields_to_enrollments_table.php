<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('payment_transaction_id')->nullable()->after('payment_method');
            $table->string('payment_gateway')->nullable()->after('payment_transaction_id');
            $table->json('payment_gateway_response')->nullable()->after('payment_gateway');
            $table->timestamp('payment_processed_at')->nullable()->after('payment_gateway_response');
            $table->string('currency', 10)->nullable()->default('XAF')->after('amount_paid');
            $table->timestamp('refunded_at')->nullable()->after('completion_date');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refunded_at');
            $table->text('refund_reason')->nullable()->after('refund_amount');
            $table->string('refund_transaction_id')->nullable()->after('refund_reason');
            $table->text('payment_notes')->nullable()->after('refund_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_transaction_id',
                'payment_gateway',
                'payment_gateway_response',
                'payment_processed_at',
                'currency',
                'refunded_at',
                'refund_amount',
                'refund_reason',
                'refund_transaction_id',
                'payment_notes',
            ]);
        });
    }
};
