<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('formation_id')->constrained();
            $table->timestamp('enrollment_date')->useCurrent();
            $table->timestamp('completion_date')->nullable();
            $table->enum('status', ['active', 'completed', 'suspended', 'cancelled'])->default('active');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->decimal('amount_paid', 8, 2)->default(0);

            $table->string('payment_transaction_id')->nullable()->after('amount_paid');
            $table->string('payment_method')->nullable()->after('payment_transaction_id');
            $table->string('payment_gateway')->nullable()->after('payment_method');
            $table->timestamp('payment_processed_at')->nullable()->after('payment_gateway');
            $table->json('payment_gateway_response')->nullable()->after('payment_processed_at');
            $table->text('payment_notes')->nullable()->after('payment_gateway_response');

            $table->index('payment_transaction_id');
            $table->index(['payment_status', 'payment_processed_at']);

            $table->timestamps();

            $table->unique(['user_id', 'formation_id']);
            $table->index(['status', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
