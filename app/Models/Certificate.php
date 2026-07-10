<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CertificateStatusEnum;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'formation_id',
        'issue_date',
        'expiry_date',
        'status',
        'file_path',
        'final_score',
        'metadata',
    ];

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
        return $query->where('status', CertificateStatusEnum::ACTIVE);
    }

    public function scopeValid($query)
    {
        return $query->where('status', CertificateStatusEnum::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            });
    }

    public function isValid(): bool
    {
        return $this->status === CertificateStatusEnum::ACTIVE &&
            ($this->expiry_date === null || $this->expiry_date->isFuture());
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('certificates.download', ['certificate' => $this]);
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', ['hash' => $this->verification_hash]);
    }

    public function revoke(string $reason = ''): void
    {
        $this->update([
            'status' => CertificateStatusEnum::REVOKED,
            'metadata' => array_merge($this->metadata ?? [], [
                'revoked_at' => now()->toIso8601String(),
                'revoke_reason' => $reason,
            ]),
        ]);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function ($certificate) {
            $certificate->certificate_number = $certificate->generateCertificateNumber();
            $certificate->verification_hash = $certificate->generateVerificationHash();
        });
    }

    protected function generateCertificateNumber(): string
    {
        return sprintf('CERT-%s-%s', now()->format('Y'), (string) Str::ulid());
    }

    protected function generateVerificationHash(): string
    {
        return hash('sha256', $this->user_id.$this->formation_id.now()->timestamp.Str::random(10));
    }

    protected function casts(): array
    {
        return [
            'issue_date' => 'datetime',
            'expiry_date' => 'datetime',
            'final_score' => 'decimal:2',
            'metadata' => 'array',
            'status' => CertificateStatusEnum::class,
        ];
    }
}
