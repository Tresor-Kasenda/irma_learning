<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')]
class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->dispatch('notification-add', [
            'type' => 'success',
            'title' => 'Connexion réussie !',
            'message' => 'Vous revoila sur votre espace de formation',
            'duration' => 5000
        ]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full flex justify-center">
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form wire:submit="login"
          class="border border-border/60 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
        <div class="p-5 sm:p-8">
            <a href="{{ route('home-page') }}" wire:navigate>
                <img
                    src="{{ asset('images/irma-logo-base.svg') }}"
                    alt="logo Irma"
                    width="200"
                    height="100"
                    class="h-16 w-auto mb-5 mx-auto"
                />
            </a>
            <div class="text-center">
                <h1 class="text-fg-title mb-1 text-xl font-semibold">
                    Bienvenue sur Irma
                </h1>
                <p class="text-sm">
                    Identifiez-vous pour accéder à votre compte
                </p>
            </div>
            <hr class="my-8 border-border-high/60"/>
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <x-input-label for="email" :value="__('Email')"/>
                    <x-text-input
                        wire:model="form.email"
                        id="email"
                        class="block mt-1 w-full placeholder:text-gray-400"
                        type="email"
                        name="email"
                        placeholder="Ex: users@example.com"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="password" :value="__('Password')"/>
                    <x-text-input
                        wire:model="form.password"
                        id="password"
                        class="block mt-1 w-full placeholder:text-gray-400"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="********"
                    />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2"/>
                    <div class="flex justify-between w-full pt-3">
                        <div class="flex items-center gap-2">
                            <input
                                wire:model="form.remember"
                                id="remember"
                                type="checkbox"
                                class="ui-form-checkbox rounded text-primary-600"
                                name="remember"
                            />
                            <label for="remember-me" class="text-sm text-fg-text">
                                {{ __('Remember me') }}</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="inline text-primary hover:text-primary-700 text-sm"
                                wire:navigate
                            >
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                </div>
                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Log in') }}</span>
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
                Vous n'avez pas de compte ?
                <a href="{{ route('register') }}" wire:navigate
                   class="inline text-primary hover:text-primary-700 font-medium">
                    S'inscrire
                </a>
            </p>
        </div>
    </form>
</div>
