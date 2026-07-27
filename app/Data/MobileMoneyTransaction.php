<?php

declare(strict_types=1);

namespace App\Data;

final readonly class MobileMoneyTransaction
{
    public function __construct(
        public string $id,
        public string $referenceId,
        public int $amount,
        public string $currency,
        public string $status,
        public string $phoneNumber,
        public ?string $failureReason = null,
        public ?string $completedAt = null,
    ) {}

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
