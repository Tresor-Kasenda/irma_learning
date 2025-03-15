<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<header class="sticky top-0 z-10 bg-bg-lighter text-fg h-16 border-b border-border-light flex items-center w-full">

    <span data-nav-overlay data-navbar-id="dashnav" aria-hidden="true"
          class="flex invisible opacity-0 bg-gray-800/50 backdrop-blur-xl fx-open:visible fx-open:opacity-100 fixed inset-0 z-40 lg:invisible lg:hidden"></span>
    <nav
        class="mx-auto w-full px-4 sm:px-10 lg:px-5 xl:px-8 xl:max-w-[88rem] flex items-center justify-between gap-8 h-full">
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate aria-label="Page Accueil du Site Betterlife"
               class="flex items-center w-max gap-1">
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                     class="h-12 w-auto">
                <img src="{{ asset('images/irma-text-primary.svg') }}" alt="Irma Text" width="131" height="51.53"
                     class="h-12 w-auto max-[500px]:hidden">
            </a>
        </div>
        <div data-dash-nav id="dashnav" class="flex-1 flex sm:justify-start sm:items-center sm:h-full dashnav">
            <ul class="flex flex-col sm:flex-row gap-4 sm:gap-2 sm:h-full text-fg w-full">
                <li data-state="{{ request()->routeIs('dashboard') ? 'active' : 'inactive' }}"
                    class="md:h-full flex items-center group fx-active:border-primary border-b-2 border-transparent fx-active:text-fg-title">
                    <a href="{{ route('dashboard') }}"
                       wire:navigate
                       class="w-full sm:w-max flex px-3 py-1.5 rounded-md hover:bg-bg-light group-fx-active:bg-bg border border-transparent group-fx-active:border-border/40">
                        Dashboard
                    </a>
                </li>
                <li data-state="{{ request()->routeIs('resultats') ? 'active' : 'inactive' }}"
                    class="md:h-full flex items-center group fx-active:border-primary border-b-2 border-transparent fx-active:text-fg-title">
                    <a href="{{ route('resultats') }}"
                       wire:navigate
                       class="w-full sm:w-max flex px-3 py-1.5 rounded-md hover:bg-bg-light group-fx-active:bg-bg border border-transparent group-fx-active:border-border/40">
                        Resultats
                    </a>
                </li>
            </ul>
        </div>

        <div class="flex items-center gap-3">
            <button aria-label="Afficher dropdown profile" data-dropdown-trigger data-dropdown-id="user-dropdown"
                    class="border-4 border-border-high size-10 min-w-10 rounded-full overflow-hidden">
                <img src="{{ asset('images/avatar.webp') }}" width="200" height="200" alt="User avatar"
                     class="size-full object-cover">
            </button>
            <div role="menu" data-ui-dropdown id="user-dropdown" aria-labelledby="pm-dropdown"
                 class="ui-popper z-10 w-60 bg-bg origin-top-right p-2 border border-border backdrop-blur-xl rounded-lg invisible fx-open:visible opacity-0 fx-open:opacity-100 translate-y-5 fx-open:translate-y-0 ease-linear duration-100 transition-[visibility_opacity_transform]">
                <ul class="flex flex-col space-y-3" role="menu" aria-orientation="vertical"
                    aria-labelledby="dropdown-avatar">
                    <li class="flex items-center gap-3 px-2 border-b pb-2 border-b-border">
                        <div class="w-max">
                            <div class="w-10 rounded-full overflow-hidden">
                                <img src="{{ asset('images/avatar.webp') }}" alt="User Avatar"
                                     class="size-10 rounded-full object-cover"/>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h6 class="text-fg-subtitle text-base font-semibold truncate">{{ auth()->user()?->name }}
                            </h6>
                            <small
                                class="text-fg-subtext text-sm font-normal truncate flex">{{ auth()->user()?->email }}</small>
                        </div>
                    </li>
                    <li>
                        <a class="ui-dropdown-item gap-x-2" href="{{ route('profile') }}" wire:navigate>
                            <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24" stroke-width="1.5" width="24" height="24"
                                 color="currentColor" class="size-5 *:stroke-current *:fill-none">
                                <circle cx="12" cy="7.25" r="5.73"></circle>
                                <path
                                    d="M1.5,23.48l.37-2.05A10.3,10.3,0,0,1,12,13h0a10.3,10.3,0,0,1,10.13,8.45l.37,2.05">
                                </path>
                            </svg>
                            <span>
                                Profile
                            </span>
                        </a>
                    </li>
                    <li>
                        <button wire:click="logout"
                                class="ui-dropdown-item text-red-600 hover:bg-red-100/60 w-full focus:outline-red-600 focus:bg-red-50 gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                 class="size-5">
                                <path fill-rule="evenodd"
                                      d="M17 4.25A2.25 2.25 0 0 0 14.75 2h-5.5A2.25 2.25 0 0 0 7 4.25v2a.75.75 0 0 0 1.5 0v-2a.75.75 0 0 1 .75-.75h5.5a.75.75 0 0 1 .75.75v11.5a.75.75 0 0 1-.75.75h-5.5a.75.75 0 0 1-.75-.75v-2a.75.75 0 0 0-1.5 0v2A2.25 2.25 0 0 0 9.25 18h5.5A2.25 2.25 0 0 0 17 15.75V4.25Z"
                                      clip-rule="evenodd"/>
                                <path fill-rule="evenodd"
                                      d="M14 10a.75.75 0 0 0-.75-.75H3.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 14 10Z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <span>
                                Se deconnerter
                            </span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="w-full flex items-center sm:hidden">
                <button data-nav-trigger data-toggle-nav="dashnav" data-expanded="false"
                        class="px-2.5 relative z-[90] space-y-2 group" aria-label="toggle navbar">
                    <span class="h-0.5 flex w-6 rounded bg-fg transition duration-300 group-aria-expanded:rotate-45"
                          id="line-1" aria-hidden="true"></span>
                    <span class="h-0.5 flex w-6 rounded bg-fg transition duration-300 group-aria-expanded:rotate-45"
                          id="line-2" aria-hidden="true"></span>
                </button>
            </div>
        </div>

    </nav>
</header>
