<?php

use App\Notifications\PasswordChangeNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Validation\Rules;

new #[Layout('layouts.guest')] class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->dispatch(
                'notify',
                message: "Le mot de passe actuel est incorrect.",
                type: 'error'
            );
            return;
        }

        $user->update([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        Auth::logout();

        $this->dispatch(
            'notify',
            message: "Mot de passe changé avec succès. Connectez-vous avec votre nouveau mot de passe.",
            type: 'success'
        );

        \Illuminate\Support\defer(function () use ($user) {
            Notification::sendNow($use->email, new PasswordChangeNotification($user));
        });

        $this->redirectIntended(default: route('login', absolute: false), navigate: true);

    }
}; ?>
<div class="w-full flex justify-center">
    <form
        wire:submit="changePassword"
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
                    <x-input-label for="current_password" :value="__('Mot de passe actuelle')"/>
                    <x-text-input
                        wire:model="current_password"
                        id="current_password"
                        class="block mt-1 w-full"
                        type="password"
                        name="current_password"
                        required
                        autofocus
                        autocomplete="current_password"
                    />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-2"/>
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
                            autocomplete="password"
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
                            required autocomplete="password_confirmation"
                        />

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
                    </div>
                </div>
                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary">
                    {{ __('Update Password') }}
                </button>
            </div>
        </div>
        <div class="bg-bg-light rounded px-5 sm:px-6 py-4">
            <p class="text-center text-sm">
                Soucis pour vous connecter ?
                <a href="#" wire:navigate
                   class="inline text-primary hover:text-primary-700 font-medium">Contactez-nous</a>
            </p>
        </div>
    </form>
</div>
