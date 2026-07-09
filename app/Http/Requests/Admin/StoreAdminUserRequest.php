<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isRoot();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignableRoles = UserRoleEnum::assignable($this->user()?->isRoot() ?? false);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(array_map(fn (UserRoleEnum $role): string => $role->value, $assignableRoles))],
            'status' => ['required', Rule::enum(UserStatusEnum::class)],
            'must_change_password' => ['required', 'boolean'],
        ];
    }
}
