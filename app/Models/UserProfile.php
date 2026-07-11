<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsAllActivity;
use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    use LogsAllActivity;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'preferences' => 'array',
        ];
    }
}
