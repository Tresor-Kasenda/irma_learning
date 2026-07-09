<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class MakeAdmin extends Command
{
    protected $signature = 'make:admin
                            {--name= : The name of the user}
                            {--username= : The username of the user}
                            {--email= : A valid and unique email address}
                            {--password= : The password for the user (min. 8 characters)}
                            {--role= : The role of the user}';

    protected $description = 'Create admin user for application';

    public function handle(): int
    {
        $user = User::create($this->getUserData());

        $this->components->info("Success! {$user->email} may now log in.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function roles(): array
    {
        return [
            'admin' => 'Admin',
            'instructor' => 'Instructor',
            'root' => 'Root',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getUserData(): array
    {
        return [
            'name' => $this->option('name') ?? text(
                label: 'Name',
                required: true,
            ),
            'username' => $this->option('username') ?? text(
                label: 'Username',
                required: true,
            ),
            'email' => $this->option('email') ?? text(
                label: 'Email address',
                required: true,
                validate: fn (string $email): ?string => match (true) {
                    ! filter_var($email, FILTER_VALIDATE_EMAIL) => 'The email address must be valid.',
                    User::where('email', $email)->exists() => 'A user with this email address already exists',
                    default => null,
                },
            ),
            'role' => $this->option('role') ?? select(
                label: 'Role',
                options: $this->roles(),
                hint: 'Select role of user',
                required: true
            ),
            'password' => Hash::make($this->option('password') ?? password(
                label: 'Password',
                required: true,
            )),
        ];
    }
}
