<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full flex justify-center">

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <form
        wire:submit="confirmPassword"
        class="border border-border/40 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg"
    >
        <div class="p-5 sm:p-8">
            <a href="{{ route('home-page') }}" wire:navigate>
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                     class="h-16 w-auto mb-5 mx-auto">
            </a>
            <div class="text-center">
                <h1 class="text-fg-title mb-1 text-xl font-semibold">Vous avez oublier le mot de passe</h1>
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </div>
            </div>
            <hr class="my-8 border-border-high/60"/>
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <x-input-label for="password" :value="__('Password')"/>
                    <x-text-input
                        wire:model="password"
                        id="password"
                        class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="current-password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2"/>
                </div>

                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Confirm') }}</span>
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
