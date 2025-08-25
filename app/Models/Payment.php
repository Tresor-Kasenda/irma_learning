<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;
    use LogsActivity;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'processed_at' => now()
        ]);

        Enrollment::where('user_id', $this->user_id)
            ->where('formation_id', $this->formation_id)
            ->update([
                'payment_status' => 'paid',
                'amount_paid' => $this->amount
            ]);
    }

    public function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'processed_at' => 'datetime',
            'status' => PaymentStatusEnum::class,
        ];
    }
}
