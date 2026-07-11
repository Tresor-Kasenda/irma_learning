<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Concerns\LogsAllActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use LogsAllActivity;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'status',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function answers(): HasManyThrough
    {
        return $this->hasManyThrough(UserAnswer::class, ExamAttempt::class);
    }

    public function isEnrolledIn(Formation $formation): bool
    {
        return $this->formations()->where('formation_id', $formation->id)->exists();
    }

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(Formation::class, 'enrollments')
            ->withPivot(['status', 'payment_status', 'progress_percentage', 'enrollment_date'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function isAdmin(): bool
    {
        return $this->role->value === UserRoleEnum::ADMIN->value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isRoot();
    }

    public function isRoot(): bool
    {
        return $this->role->value === UserRoleEnum::ROOT->value;
    }

    public function hasStudent(): bool
    {
        if ($this->isStudent()) {
            return true;
        }

        return false;
    }

    public function isStudent(): bool
    {
        return $this->role->value === UserRoleEnum::STUDENT->value;
    }

    /**
     * @return list<string>
     */
    protected function activityLogExcept(): array
    {
        return ['password', 'remember_token'];
    }

    /**
     * Resolve the public URL for the user's avatar, falling back to the default image.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->avatar === null) {
                return asset('images/avatar.webp');
            }

            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }

            return Storage::disk('public')->url($this->avatar);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'role' => UserRoleEnum::class,
            'status' => UserStatusEnum::class,
        ];
    }
}
