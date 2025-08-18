<?php

namespace App\Models;

use App\Enums\VerificationCodeStatusEnum;
use App\Enums\VerificationCodeTypeEnum;
use Database\Factories\VerificationCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    /** @use HasFactory<VerificationCodeFactory> */
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (VerificationCode $code) {
            $code->code = $code->generateUniqueCode();
            $code->expires_at = now()->addHours(24);
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

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

    public function markAsUsed(string $ipAddress = null, string $userAgent = null): void
    {
        $this->update([
            'status' => VerificationCodeStatusEnum::Used,
            'used_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
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

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'status' => VerificationCodeStatusEnum::class,
            'type' => VerificationCodeTypeEnum::class,
        ];
    }
}
