<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PermissionEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use Database\Factories\UserFactory;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'status',
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

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function hasPermission(PermissionEnum $permission): bool
    {
        if ($this->isRoot()) {
            return true;
        }

        return match ($this->role) {
            UserRoleEnum::ADMIN->value => $this
                ->getAdminPermissions()
                ->contains($permission->value),
            default => false,
        };
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
            return $this->isAdmin() || $this->isSuperAdmin();
        }

        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoleEnum::ADMIN->value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isRoot();
    }

    public function isStudent(): bool
    {
        return $this->role === UserRoleEnum::STUDENT->value;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
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
            'status' => UserStatusEnum::class
        ];
    }
}
