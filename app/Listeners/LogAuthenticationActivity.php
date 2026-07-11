<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

final readonly class LogAuthenticationActivity
{
    public function __construct(private Request $request) {}

    public function onLogin(Login $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->withProperties($this->context())
            ->event('login')
            ->log('Connexion réussie.');
    }

    public function onLogout(Logout $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->withProperties($this->context())
            ->event('logout')
            ->log('Déconnexion.');
    }

    public function onFailed(Failed $event): void
    {
        activity('auth')
            ->withProperties([
                ...$this->context(),
                'email' => $event->credentials['email'] ?? $event->credentials['username'] ?? null,
            ])
            ->event('login_failed')
            ->log('Tentative de connexion échouée.');
    }

    /**
     * @return array<string, string|null>
     */
    private function context(): array
    {
        return [
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ];
    }
}
