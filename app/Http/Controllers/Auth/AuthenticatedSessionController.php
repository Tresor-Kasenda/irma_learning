<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\ActiveSessionGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'socialAuthentication' => [
                'google' => SocialAuthenticationController::isConfigured('google'),
                'github' => SocialAuthenticationController::isConfigured('github'),
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, ActiveSessionGuard $sessionGuard): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if ($user instanceof User && $user->isStudent()
            && $sessionGuard->hasActiveSessionElsewhere($user, $request->session()->getId())) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous ailleurs avant de continuer.',
            ]);
        }

        $request->session()->regenerate();

        $destination = $user instanceof User && $user->canAccessAdministration()
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        return redirect()->intended($destination);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
