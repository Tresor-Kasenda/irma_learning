<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {

    #[\Livewire\Attributes\Validate('required|string|email')]
    public ?string $email = null;

    #[\Livewire\Attributes\Validate('required|exists:problems,id')]
    public ?int $problem_id = null;

    #[\Livewire\Attributes\Validate('required|string')]
    public ?string $message = null;

    #[\Livewire\Attributes\Computed]
    public function problems(): \Illuminate\Support\Collection
    {
        return \App\Models\Problem::pluck('name', 'id');
    }

    public function submit(): void
    {
        $this->validate();

        $helpRequest = \App\Models\HelpRequest::create([
            'problem_id' => $this->problem_id,
            'email' => $this->email,
            'message' => $this->message,
        ]);

        try {
            $admin = \App\Models\User::query()
                ->where('role', '=', \App\Enums\UserRoleEnum::ROOT)->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\NewHelpRequest($helpRequest));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Échec de l\'envoi de notification: ' . $e->getMessage());
        }

        $this->reset(['email', 'problem_id', 'message']);

        $this->dispatch(
            'notify',
            message: "Votre demande d'aide a été soumise avec",
            type: 'success'
        );
    }

}; ?>

<div class="w-full flex justify-center">
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form wire:submit="submit"
          class="border border-border/60 w-full max-w-lg p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
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
                <h1 class="text-fg-title mb-1 text-xl font-semibold">Aide et support</h1>
                <p class="text-sm">Obtenez de l'aide en rapport avec votre compte</p>
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
                        placeholder=""
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="problem" :value="__('Probleme rencontre')"/>
                    <select name="problem" wire:model="problem_id" id="problem"
                            class="ui-form-input form-input-md rounded-md peer w-full">
                        <option>{{ __('Selectionner un probleme') }}</option>
                        @foreach($this->problems() as $id => $problem)
                            <option value="{{ $id }}">{{ $problem }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>
                <div class="flex flex-col gap-2">
                    <x-input-label for="message" :value="__('Message')"/>
                    <x-text-area wire:model="message" id="message"/>
                </div>
                <button class="btn btn-md rounded-md w-full justify-center text-white bg-primary"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Soumettre') }}</span>
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
                Tout va bien!
                <a href="{{ route('login') }}" wire:navigate
                   class="inline text-primary hover:text-primary-700 font-medium">Me connecter</a>
            </p>
        </div>
    </form>
</div>
