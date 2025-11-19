<?php

namespace App\Models;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'formation_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_transaction_id',
        'payment_gateway',
        'payment_gateway_response',
        'amount_paid',
        'currency',
        'enrollment_date',
        'completion_date',
        'last_accessed_at',
        'progress_percentage',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'refund_transaction_id',
        'payment_notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $enrollment): void {
            if (empty($enrollment->payment_transaction_id)) {
                $enrollment->payment_transaction_id = 'pi_' . bin2hex(random_bytes(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', EnrollmentStatusEnum::Active);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', EnrollmentPaymentEnum::PAID);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', EnrollmentStatusEnum::Completed);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function markAsPaid(array $paymentData = []): void
    {
        $this->update([
            'payment_status' => EnrollmentPaymentEnum::PAID,
            'payment_processed_at' => now(),
            'status' => EnrollmentStatusEnum::Active,
            'payment_transaction_id' => $paymentData['transaction_id'] ?? null,
            'payment_method' => $paymentData['method'] ?? null,
            'payment_gateway' => $paymentData['gateway'] ?? null,
            'payment_gateway_response' => $paymentData['gateway_response'] ?? null,
        ]);
    }


    public function refund(string $reason = ''): void
    {
        $this->update([
            'payment_status' => EnrollmentPaymentEnum::REFUNDED,
            'status' => EnrollmentStatusEnum::Suspended,
            'payment_notes' => ($this->payment_notes ? $this->payment_notes . "\n\n" : '') .
                'REMBOURSEMENT: ' . $reason . ' (' . now()->format('d/m/Y H:i') . ')',
        ]);
    }

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'datetime',
            'completion_date' => 'datetime',
            'last_accessed_at' => 'datetime',
            'amount_paid' => 'decimal:2',
            'progress_percentage' => 'decimal:2',
            'status' => EnrollmentStatusEnum::class,
            'payment_status' => EnrollmentPaymentEnum::class,
            'payment_gateway_response' => 'array',
            'payment_processed_at' => 'datetime',
        ];
    }
}
