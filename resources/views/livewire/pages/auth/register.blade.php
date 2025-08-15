<?php

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')]
class extends Component {
    public string $name = '';
    public string $username = '';
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
            'username' => ['required', 'string', 'lowercase', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => [
                'required',
                'string',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = UserRoleEnum::STUDENT;
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
                     class="h-16 w-auto mx-auto">
            </a>
            <div class="text-center mt-2">
                <h1 class="text-fg-title mb-1 text-xl font-semibold">Bienvenue sur Irma</h1>
                <p class="text-sm">Identifiez-vous pour accéder à votre compte</p>
            </div>
            <hr class="my-4 border-border-high/60"/>
            <div class="space-y-6">
                <div class="flex flex-col gap-1">
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
                <div class="flex flex-col gap-1">
                    <x-input-label for="username" :value="__('Username')"/>
                    <x-text-input
                        wire:model="username"
                        id="username"
                        class="block mt-1 w-full"
                        type="text"
                        name="username"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Your username"
                    />
                    <x-input-error :messages="$errors->get('username')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-1">
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
                <div class="flex flex-col gap-1">
                    <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-gray-700"/>
                    <div
                        class="relative"
                        x-data="{
                            showPassword: false,
                            strength: 0,
                            password: '',

                            checkStrength() {
                                let strength = 0;
                                const password = this.password;

                                if (password.length === 0) {
                                    this.strength = 0;
                                    return;
                                }

                                if (password.length >= 8) strength += 1;
                                if (password.length >= 12) strength += 1;

                                if (/[0-9]/.test(password)) strength += 1;
                                if (/[a-z]/.test(password)) strength += 1;
                                if (/[A-Z]/.test(password)) strength += 1;
                                if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

                                this.strength = Math.min(5, strength);
                            }
                        }"
                        x-init="$watch('password', () => checkStrength())"
                    >
                        <div class="relative">
                            <x-text-input
                                wire:model.live="password"
                                x-model="password"
                                id="password"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm pr-10"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                placeholder="Enter your password"
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                                tabindex="-1">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-1">
                            <div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-1 transition-all duration-300"
                                    :class="{
                                        'w-0': strength === 0,
                                        'w-1/5 bg-red-500': strength === 1,
                                        'w-2/5 bg-red-500': strength === 2,
                                        'w-3/5 bg-yellow-500': strength === 3,
                                        'w-4/5 bg-yellow-500': strength === 4,
                                        'w-full bg-green-500': strength === 5
                                    }"
                                ></div>
                            </div>
                            <p
                                class="text-xs mt-1"
                                :class="{
                                   'text-gray-400': strength === 0,
                                   'text-red-500': strength <= 2,
                                   'text-yellow-500': strength > 2 && strength < 5,
                                   'text-green-500': strength === 5
                                }"
                            >
                                <span x-show="strength === 0">Password strength</span>
                                <span x-show="strength === 1">Very weak</span>
                                <span x-show="strength === 2">Weak</span>
                                <span x-show="strength === 3">Medium</span>
                                <span x-show="strength === 4">Strong</span>
                                <span x-show="strength === 5">Very strong</span>
                            </p>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600"/>
                </div>

                <div class="flex flex-col gap-1">
                    <x-input-label
                        for="password_confirmation"
                        :value="__('Confirm Password')"
                        class="text-sm font-medium text-gray-700"
                    />
                    <div class="relative" x-data="{ showPassword: false }">
                        <x-text-input
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm pr-10"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600"/>
                </div>

                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary"
                        wire:loading.attr="disabled">
                    {{ __('Register') }}
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
