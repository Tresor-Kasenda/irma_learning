<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');
        $actor = $this->user();

        return $actor?->isRoot() || ($target instanceof User && ! $target->isRoot());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignableRoles = $this->user()?->isRoot()
            ? UserRoleEnum::cases()
            : array_filter(UserRoleEnum::cases(), fn (UserRoleEnum $role): bool => $role !== UserRoleEnum::ROOT);

        return [
            'role' => ['required', Rule::in(array_map(fn (UserRoleEnum $role): string => $role->value, $assignableRoles))],
            'status' => ['required', Rule::enum(UserStatusEnum::class)],
            'must_change_password' => ['required', 'boolean'],
        ];
    }
}
