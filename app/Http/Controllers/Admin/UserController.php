<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = in_array($request->integer('per_page'), [10, 25, 50, 100], true)
            ? $request->integer('per_page')
            : 10;

        $users = User::query()
            ->withCount(['enrollments', 'certificates'])
            ->when($request->string('search')->isNotEmpty(), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn (Builder $query): Builder => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn (Builder $query): Builder => $query->where('role', $request->string('role')->toString()))
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $user): array => [
                ...$user->only(['id', 'name', 'email', 'avatar_url', 'must_change_password', 'created_at']),
                'role' => $user->role->value,
                'role_label' => $user->role->getLabel(),
                'status' => $user->status->value,
                'status_label' => $user->status->getLabel(),
                'enrollments_count' => $user->enrollments_count,
                'certificates_count' => $user->certificates_count,
            ]);

        $canManageRoot = $request->user()?->isRoot() ?? false;

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search', 'role', 'status', 'per_page'),
            'roleOptions' => collect(UserRoleEnum::cases())->map(fn (UserRoleEnum $role): array => ['value' => $role->value, 'label' => $role->getLabel()]),
            'assignableRoleOptions' => collect(UserRoleEnum::cases())
                ->reject(fn (UserRoleEnum $role): bool => $role === UserRoleEnum::ROOT && ! $canManageRoot)
                ->map(fn (UserRoleEnum $role): array => ['value' => $role->value, 'label' => $role->getLabel()])
                ->values(),
            'statusOptions' => collect(UserStatusEnum::cases())->map(fn (UserStatusEnum $status): array => ['value' => $status->value, 'label' => $status->getLabel()]),
            'canManageRoot' => $canManageRoot,
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user) && $request->validated('status') !== UserStatusEnum::ACTIVE->value, 422, 'Vous ne pouvez pas suspendre votre propre compte.');

        $user->update($request->validated());

        return back()->with('success', 'Rôle et accès de l’utilisateur mis à jour.');
    }
}
