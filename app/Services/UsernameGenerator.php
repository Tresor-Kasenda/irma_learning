<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

final class UsernameGenerator
{
    public function forEmail(string $email): string
    {
        $base = Str::of($email)
            ->before('@')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value();

        $base = $base === '' ? 'apprenant' : Str::limit($base, 30, '');
        $username = $base;
        $suffix = 2;

        while (User::query()->where('username', $username)->exists()) {
            $suffixString = (string) $suffix;
            $username = Str::limit($base, 30 - mb_strlen($suffixString) - 1, '').'-'.$suffixString;
            $suffix++;
        }

        return $username;
    }
}
