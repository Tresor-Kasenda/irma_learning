<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = \App\Enums\UserRoleEnum::STUDENT;
        $validated['must_change_password'] = true;

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->dispatch(
            'notify',
            message: 'Votre compte a ete cree avec success',
            type: 'success'
        );

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full flex justify-center">
    <form wire:submit="register"
          class="border border-border/60 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
        <div class="p-5 sm:p-8">
            <a href="{{ route('home-page') }}" wire:navigate>
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                     class="h-16 w-auto mb-5 mx-auto">
            </a>
            <div class="text-center">
                <h1 class="text-fg-title mb-1 text-xl font-semibold">Bienvenue sur Irma</h1>
                <p class="text-sm">Identifiez-vous pour accéder à votre compte</p>
            </div>
            <hr class="my-8 border-border-high/60"/>
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <x-input-label for="name" :value="__('Name')"/>
                    <x-text-input
                        wire:model="name"
                        id="name"
                        class="block mt-1 w-full"
                        type="text"
                        name="name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Your name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="email" :value="__('Email')"/>
                    <x-text-input
                        wire:model="email"
                        id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        required
                        placeholder="Ex: users@example.com"
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="password" :value="__('Password')"/>

                    <x-text-input
                        wire:model="password"
                        id="password"
                        class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required
                        placeholder="Ex: *******"
                        autocomplete="new-password"
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
                        required
                        autocomplete="new-password"
                        placeholder="Ex: *******"
                    />

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
                </div>
                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary"
                        wire:loading.attr="disabled">
                    {{ __('Register') }}
                    <span wire:loading.remove>{{ __('Register') }}</span>
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
                Vous avez un compte ?
                <a href="{{ route('login') }}" wire:navigate
                   class="inline text-primary hover:text-primary-700 font-medium">Se Connecter</a>
            </p>
        </div>
    </form>
</div>
