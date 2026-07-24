<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Models\User;
use App\Services\LearnerNotificationService;
use App\Services\UsernameGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final class SocialAuthenticationController extends Controller
{
    /**
     * @var array<string, array{label: string, id_column: string}>
     */
    private const PROVIDERS = [
        'google' => [
            'label' => 'Google',
            'id_column' => 'google_id',
        ],
        'github' => [
            'label' => 'GitHub',
            'id_column' => 'github_id',
        ],
    ];

    public static function isConfigured(string $provider): bool
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        return is_string($clientId) && $clientId !== ''
            && is_string($clientSecret) && $clientSecret !== '';
    }

    public function redirect(string $provider): RedirectResponse
    {
        $definition = $this->definitionFor($provider);

        if (! $this->isConfigured($provider)) {
            return to_route('login')->withErrors([
                'email' => "La connexion avec {$definition['label']} est temporairement indisponible.",
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(
        Request $request,
        string $provider,
        LearnerNotificationService $notifications,
        UsernameGenerator $usernames,
    ): RedirectResponse {
        $definition = $this->definitionFor($provider);
        $providerLabel = $definition['label'];

        if (! $this->isConfigured($provider)) {
            return $this->redirectToLoginWithError("La connexion avec {$providerLabel} est temporairement indisponible.");
        }

        if ($request->filled('error')) {
            return $this->redirectToLoginWithError("La connexion avec {$providerLabel} a été annulée.");
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectToLoginWithError("La connexion avec {$providerLabel} est temporairement indisponible.");
        }

        $id = $socialUser->getId();
        $email = $socialUser->getEmail();
        $profile = $socialUser->getRaw();

        if (! is_string($id) || $id === '' || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->redirectToLoginWithError("{$providerLabel} ne nous a pas transmis d'identité valide.");
        }

        if (! $this->hasVerifiedEmail($provider, $profile)) {
            return $this->redirectToLoginWithError("L'adresse e-mail de votre compte {$providerLabel} doit être vérifiée.");
        }

        $email = mb_strtolower($email);
        $idColumn = $definition['id_column'];

        $user = User::query()->where($idColumn, $id)->first();

        if ($user === null) {
            $user = User::query()->where('email', $email)->first();

            if ($user === null) {
                if (! ApplicationSetting::current()->allow_registration) {
                    return $this->redirectToLoginWithError('Les inscriptions sont actuellement fermées.');
                }

                $user = User::create([
                    'name' => $this->nameFor($socialUser->getName(), $email),
                    'username' => $usernames->forEmail($email),
                    'email' => $email,
                    'email_verified_at' => now(),
                    $idColumn => $id,
                    'password' => Str::random(64),
                ]);

                $notifications->welcomeIfNeeded($user);
            } else {
                // A provider-verified address proves that the person signing in controls this
                // account. Rotate an unverified local password to prevent account pre-registration.
                $user->update([
                    $idColumn => $id,
                    'email_verified_at' => now(),
                    'password' => $user->hasVerifiedEmail() ? $user->password : Str::random(64),
                ]);
            }
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function hasVerifiedEmail(string $provider, array $profile): bool
    {
        if ($provider === 'google') {
            return filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOL);
        }

        // The Socialite GitHub provider only returns a primary, verified email when `user:email`
        // is requested; it returns null otherwise.
        return true;
    }

    /**
     * @return array{label: string, id_column: string}
     */
    private function definitionFor(string $provider): array
    {
        abort_unless(array_key_exists($provider, self::PROVIDERS), 404);

        return self::PROVIDERS[$provider];
    }

    private function nameFor(?string $name, string $email): string
    {
        $name = mb_trim($name ?? '');

        if ($name !== '') {
            return Str::limit($name, 255, '');
        }

        return Str::of($email)
            ->before('@')
            ->replace(['.', '_', '-'], ' ')
            ->title()
            ->value();
    }

    private function redirectToLoginWithError(string $message): RedirectResponse
    {
        return to_route('login')->withErrors(['email' => $message]);
    }
}
