<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full flex justify-center">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login"
        class="border border-border/60 w-full max-w-lg p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
        <div class="p-5 sm:p-8">
            <a href="{{ route('home-page') }}" wire:navigate>
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                    class="h-16 w-auto mb-5 mx-auto">
            </a>
            <div class="text-center">
                <h1 class="text-fg-title mb-1 text-xl font-semibold">Aide et support</h1>
                <p class="text-sm">Obtenez de l'aide en rapport avec votre compte</p>
            </div>
            <hr class="my-8 border-border-high/60" />
            <div class="space-y-6">
                <div class="flex flex-col gap-2">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email"
                        name="email" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="problem" :value="__('Probleme rencontre')" />
                    <select name="problem" id="problem" class="ui-form-input form-input-md rounded-md peer w-full">
                        <option value="">{{ __('Selectionner un probleme') }}</option>
                        <option value="1">{{ __('Probleme 1') }}</option>
                        <option value="2">{{ __('Probleme 2') }}</option>
                        <option value="3">{{ __('Probleme 3') }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="message" :value="__('Message')" />
                    <x-text-area id="message"/>
                </div>
                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary">
                    {{ __('Soumettre') }}
                </button>
            </div>
        </div>
        <div class="bg-bg-light rounded px-5 sm:px-6 py-4">
            <p class="text-center text-sm">
                Tout va bien!
                <a href="{{ route('login') }}" wire:navigate
                    class="inline text-primary hover:text-primary-700 font-medium">Me connecter</a>
            </p>
        </div>
    </form>
</div>
