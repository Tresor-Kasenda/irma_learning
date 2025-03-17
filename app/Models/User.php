<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PermissionEnum;
use App\Enums\UserRoleEnum;
use Database\Factories\UserFactory;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

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

    public function examFinals(): HasMany
    {
        return $this->hasMany(ExamFinal::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class, 'student_id');
    }

    public function evaluatedResults(): HasMany
    {
        return $this->hasMany(ExamResult::class, 'evaluated_by');
    }

    public function hasPermission(PermissionEnum $permission): bool
    {
        if ($this->isRoot()) {
            return true;
        }

        return match ($this->role) {
            UserRoleEnum::ADMIN->value => $this->getAdminPermissions()->contains($permission),
            UserRoleEnum::MANAGER->value => $this->getManagerPermissions()->contains($permission),
            default => false,
        };
    }

    public function isRoot(): bool
    {
        return $this->role === 'ROOT';
    }

    private function getAdminPermissions(): Collection
    {
        return collect([
            PermissionEnum::VIEW_DASHBOARD,
            PermissionEnum::MANAGE_CONTENT,
            PermissionEnum::VIEW_REPORTS,
            PermissionEnum::VIEW_MASTER_CLASS,
            PermissionEnum::CREATE_MASTER_CLASS,
            PermissionEnum::UPDATE_MASTER_CLASS,
            PermissionEnum::VIEW_CHAPTER,
            PermissionEnum::CREATE_CHAPTER,
            PermissionEnum::UPDATE_CHAPTER,
            PermissionEnum::VIEW_SUBSCRIPTION,
            PermissionEnum::CREATE_SUBSCRIPTION,
            PermissionEnum::UPDATE_SUBSCRIPTION,
            PermissionEnum::VIEW_EXAM,
            PermissionEnum::CREATE_EXAM,
            PermissionEnum::UPDATE_EXAM,
            PermissionEnum::SUBMIT_EXAM,
            PermissionEnum::MANAGE_USERS,
        ]);
    }

    private function getManagerPermissions(): Collection
    {
        return collect([
            PermissionEnum::VIEW_DASHBOARD,
            PermissionEnum::MANAGE_USERS,
            PermissionEnum::MANAGE_CONTENT,
            PermissionEnum::VIEW_REPORTS,
        ]);
    }

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

    public function masterclasses()
    {
        return $this->belongsToMany(
            Masterclass::class,
            'user_master_classe',
            'user_id',
            'master_class_id'
        )
            ->withPivot('reference_code')
            ->withTimestamps();
    }

    /**
     * @throws Exception
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin() || $this->isManager() || $this->isSuperAdmin();
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

    public function isSuperAdmin(): bool
    {
        return $this->isRoot();
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
