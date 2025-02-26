<?php

namespace App\Console\Commands;

use App\Models\User;
use Filament\Commands\MakeUserCommand;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeAdmin extends MakeUserCommand
{
    protected $signature = 'make:admin
                            {--name= : The name of the user}
                            {--email= : A valid and unique email address}
                            {--password= : The password for the user (min. 8 characters)}';

    protected $description = 'Create admin user for application';

    public function handle(): int
    {
        parent::handle();

        return static::SUCCESS;
    }

    /**
     * Create admin user and assign admin role
     */
    protected function createUser(): Authenticatable
    {
        return $this->getUserModel()::create($this->getUserData());
    }

    protected function getUserData(): array
    {
        return [
            'name' => $this->options['name'] ?? text(
                    label: 'Name',
                    required: true,
                ),

            'email' => $this->options['email'] ?? text(
                    label: 'Email address',
                    required: true,
                    validate: fn(string $email): ?string => match (true) {
                        !filter_var($email, FILTER_VALIDATE_EMAIL) => 'The email address must be valid.',
                        User::where('email', $email)->exists() => 'A user with this email address already exists',
                        default => null,
                    },
                ),

            'role' => $this->options['role'] ?? select(
                    label: 'Role',
                    options: $this->roles(),
                    hint: 'Select role of user',
                    required: true
                ),

            'password' => Hash::make($this->options['password'] ?? password(
                label: 'Password',
                required: true,
            )),
        ];
    }

    public function roles(): array
    {
        return [
            'ADMIN',
            'SUPPORT',
            'MANAGER'
        ];
    }
}
