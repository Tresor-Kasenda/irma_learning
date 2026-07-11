<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\VerificationCodeStatusEnum;
use App\Enums\VerificationCodeTypeEnum;
use App\Models\Concerns\LogsAllActivity;
use Database\Factories\VerificationCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class VerificationCode extends Model
{
    /** @use HasFactory<VerificationCodeFactory> */
    use HasFactory;

    use LogsAllActivity;

    public $incrementing = false;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', VerificationCodeStatusEnum::Pending)
            ->where('expires_at', '>', now());
    }

    public function scopeForFormation(Builder $query, int $formationId): Builder
    {
        return $query->where('formation_id', $formationId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isValid(): bool
    {
        return $this->status === VerificationCodeStatusEnum::Pending &&
            $this->expires_at > now();
    }

    public function markAsUsed(?string $ipAddress = null, ?string $userAgent = null): void
    {
        $this->update([
            'status' => VerificationCodeStatusEnum::Used,
            'used_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        $this->createEnrollmentAfterValidation();
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => VerificationCodeStatusEnum::Expired,
        ]);
    }

    public function getRemainingTimeAttribute(): string
    {
        if ($this->expires_at <= now()) {
            return 'Expiré';
        }

        return $this->expires_at->diffForHumans();
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (VerificationCode $code) {
            $code->code = $code->generateUniqueCode();
            $code->expires_at = now()->addHours(24);
        });
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'status' => VerificationCodeStatusEnum::class,
            'type' => VerificationCodeTypeEnum::class,
        ];
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = mb_strtoupper(Str::random(8));
        } while (self::where('code', $code)
            ->where('expires_at', '>', now())
            ->exists());

        return $code;
    }

    private function createEnrollmentAfterValidation(): void
    {
        $existingEnrollment = Enrollment::where('user_id', $this->user_id)
            ->where('formation_id', $this->formation_id)
            ->first();

        if (! $existingEnrollment) {
            Enrollment::query()
                ->create([
                    'user_id' => $this->user_id,
                    'formation_id' => $this->formation_id,
                    'status' => EnrollmentStatusEnum::ACTIVE,
                    'payment_status' => EnrollmentPaymentEnum::PAID,
                    'enrollment_date' => now(),
                    'progress_percentage' => 0,
                ]);
        }
    }
}
