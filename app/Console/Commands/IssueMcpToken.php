<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:issue-mcp-token {email : Adresse e-mail du compte à autoriser} {--name=IRMA Learning MCP : Nom affiché pour ce jeton} {--expires-in=30 : Durée de validité en jours (0 pour ne pas expirer)}')]
#[Description('Crée un jeton Sanctum limité à la lecture pour le serveur MCP IRMA Learning.')]
final class IssueMcpToken extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::query()
            ->where('email', $this->argument('email'))
            ->first();

        if (! $user instanceof User) {
            $this->error('Aucun utilisateur ne correspond à cette adresse e-mail.');

            return self::FAILURE;
        }

        if ($user->status !== UserStatusEnum::ACTIVE) {
            $this->error('Seuls les comptes actifs peuvent recevoir un jeton MCP.');

            return self::FAILURE;
        }

        $expiresIn = (int) $this->option('expires-in');

        if ($expiresIn < 0) {
            $this->error("L'option --expires-in doit être positive ou nulle.");

            return self::FAILURE;
        }

        $token = $user->createToken(
            name: (string) $this->option('name'),
            abilities: ['mcp:read'],
            expiresAt: $expiresIn === 0 ? null : now()->addDays($expiresIn),
        );

        $this->warn('Conservez ce jeton maintenant : il ne sera plus affiché.');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
