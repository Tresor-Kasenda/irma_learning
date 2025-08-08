<?php

namespace App\Models;

use App\Enums\CertificateStatusEnum;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($certificate) {
            $certificate->certificate_number = $certificate->generateCertificateNumber();
            $certificate->verification_hash = $certificate->generateVerificationHash();
        });
    }

    protected function generateCertificateNumber(): string
    {
        return 'CERT-' . date('Y') . '-' . str_pad(
                Certificate::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    protected function generateVerificationHash(): string
    {
        return hash('sha256', $this->user_id . $this->formation_id . now()->timestamp . Str::random(10));
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
        return $query->where('status', 'active');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            });
    }

    public function isValid(): bool
    {
        return $this->status === 'active' &&
            ($this->expiry_date === null || $this->expiry_date->isFuture());
    }

    public function getDownloadUrlAttribute(): string
    {
        //return route('certificates.download', ['certificate' => $this->certificate_number]);
    }

    public function getVerificationUrlAttribute(): string
    {
        //return route('certificates.verify', ['hash' => $this->verification_hash]);
    }

    protected function casts(): array
    {
        return [
            'issue_date' => 'datetime',
            'expiry_date' => 'datetime',
            'final_score' => 'decimal:2',
            'metadata' => 'array',
            'status' => CertificateStatusEnum::class
        ];
    }
}
