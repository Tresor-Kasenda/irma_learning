<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoleEnum;
use Database\Factories\UserFactory;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class);
    }

    public function isSubscribedTo(MasterClass $masterClass): bool
    {
        return $this->subscriptions()
            ->where('master_class_id', $masterClass->id)
            ->exists();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(ChapterProgress::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->isRoot();
    }

    public function isRoot(): bool
    {
        return $this->role === 'ROOT';
    }

    /**
     * @throws Exception
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin() || $this->isManager() || $this->isRoot();
        }

        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoleEnum::ADMIN->value;
    }

    public function isManager(): bool
    {
        return $this->role === UserRoleEnum::MANAGER->value;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRoleEnum::STUDENT->value;
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
        ];
    }
}
