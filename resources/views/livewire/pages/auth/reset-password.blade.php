<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>
<div class="w-full flex justify-center">
    <form
        wire:submit="resetPassword"
        class="border border-border/40 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg"
    >
        <div class="p-5 sm:p-8">
            <a href="{{ route('home-page') }}" wire:navigate>
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                     class="h-16 w-auto mb-5 mx-auto">
            </a>
            <div class="text-center">
                <h1 class="text-fg-title mb-1 text-xl font-semibold">Bienvenue sur Irma</h1>
                <p class="text-sm">
                    Mettre le mot de passe a jours
                </p>
            </div>
            <hr class="my-8 border-border-high/60"/>
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <x-input-label for="email" :value="__('Email')"/>
                    <x-text-input
                        wire:model="email"
                        id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex flex-col gap-2">
                        <x-input-label for="password" :value="__('Password')"/>
                        <x-text-input
                            wire:model="password"
                            id="password"
                            class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')"/>

                        <x-text-input
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password"
                        />

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
                    </div>
                    <div class="flex justify-between w-full pt-3">
                        <div class="flex items-center gap-2">
                            <input
                                wire:model="form.remember"
                                id="remember"
                                type="checkbox"
                                class="ui-form-checkbox rounded text-primary-600"
                                name="remember"
                            />
                            <label for="remember-me" class="text-sm text-fg-text">{{ __('Remember me') }}</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="inline text-primary hover:text-primary-700 text-sm" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                </div>
                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Reset Password') }}</span>
                    <span wire:loading>
                        <svg class="animate-spin mr-2 h-5 w-5 text-white inline-block"
                             xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Chargement...
                    </span>
                </button>
            </div>
        </div>
        <div class="bg-bg-light rounded px-5 sm:px-6 py-4">
            <p class="text-center text-sm">
                Soucis pour vous connecter ?
                <a href="{{ route('help') }}" wire:navigate
                   class="inline text-primary hover:text-primary-700 font-medium">Contactez-nous</a>
            </p>
        </div>
    </form>
</div>
