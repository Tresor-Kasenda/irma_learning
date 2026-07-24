<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\ApplicationSetting;
use App\Models\User;
use App\Services\LearnerNotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        abort_unless(ApplicationSetting::current()->allow_registration, 404);

        return Inertia::render('Auth/Register', [
            'socialAuthentication' => [
                'google' => SocialAuthenticationController::isConfigured('google'),
                'github' => SocialAuthenticationController::isConfigured('github'),
            ],
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterUserRequest $request, LearnerNotificationService $notifications): RedirectResponse
    {
        abort_unless(ApplicationSetting::current()->allow_registration, 404);

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $notifications->welcomeIfNeeded($user);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
