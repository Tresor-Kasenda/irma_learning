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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    public function createdFormations(): HasMany
    {
        return $this->hasMany(Formation::class, 'created_by');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function answers(): HasManyThrough
    {
        return $this->hasManyThrough(UserAnswer::class, ExamAttempt::class);
    }

    public function scopeActive($query): Model
    {
        return $query->where('status', 'active');
    }

    public function scopeStudents($query): Model
    {
        return $query->where('role', 'student');
    }

    public function scopeInstructors($query): Model
    {
        return $query->where('role', 'instructor');
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

    public function hasCompletedFormation(Formation $formation): bool
    {
        return $this->enrollments()
            ->where('formation_id', $formation->id)
            ->where('status', 'completed')
            ->exists();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
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
        return $this->role === UserRoleEnum::ROOT;
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
        return $this->role === UserRoleEnum::ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isRoot();
    }

    public function isStudent(): bool
    {
        return $this->role === UserRoleEnum::STUDENT;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }


    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(UserRoleEnum $role): bool
    {
        return $this->role === $role;
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
