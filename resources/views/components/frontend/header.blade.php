@props(['whiteHeader' => false])

@php
    $bgNav = $whiteHeader
        ? 'bg-bg/10 backdrop-blur-sm'
        : 'bg-bg/10 border border-border-light/30 shadow shadow-bg-light/40 ';
    $navClass = "mx-auto w-full max-w-7xl flex items-center justify-between gap-10 px-5 py-1.5 rounded-lg {$bgNav}";

    $navItemClass = $whiteHeader ? 'text-fg lg:text-white hover:text-primary-700 lg:hover:text-primary-500' : 'text-fg';
    $navItemActiveClass = $whiteHeader ? 'text-primary-700 lg:text-primary-500' : 'text-primary-700';
@endphp

<span data-nav-overlay data-navbar-id="app-main" aria-hidden="true"
      class="flex invisible opacity-0 bg-gray-800/50 backdrop-blur-xl fx-open:visible fx-open:opacity-100 fixed inset-0 z-40 lg:invisible lg:hidden"></span>

<header class="flex items-center absolute top-0 w-full z-50 pt-5 px-0.5 sm:px-5 xl:pt-7">
    <nav class="{{ $navClass }}">
        <div class="lg:min-w-max flex">
            @if ($whiteHeader)
                <a href="{{ route('home-page') }}" wire:navigate aria-label="Page Accueil du Irma RDC"
                   class="flex w-max gap-1">
                    <img src="{{ asset('assets/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                         class="h-14 w-auto">
                    <img src="{{ asset('assets/irma-text.svg') }}" alt="Irma Text" width="131" height="51.53"
                         class="h-12 w-auto max-[500px]:hidden">
                </a>
            @else
                <a href="{{ route('home-page') }}" wire:navigate aria-label="Page Accueil du Site Betterlife"
                   class="flex items-center w-max gap-1">
                    <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                         class="h-12 w-auto">
                    <img src="{{ asset('images/irma-text-primary.svg') }}" alt="Irma Text" width="131" height="51.53"
                         class="h-12 w-auto max-[500px]:hidden">
                </a>
            @endif
        </div>
        <div data-main-navbar id="app-main"
             class="lg:flex-1 lg:justify-start flex items-center gap-3 rounded-xl px-5 py-14 lg:py-0 lg:px-0 z-[80] lg:z-auto navbar-before navbar-base navbar-visibility navbar-opened lg:rounded-none navbar-content-scrollable">
            <ul class="flex items-center flex-col lg:flex-row gap-3 lg:gap-5 text-fg *:flex w-full h-max">
                <li class="relative flex w-full lg:w-max group">
                    <a href="{{ route('certifications') }}" wire:navigate aria-label="Lien vers la page : Accueil"
                       class="py-2 ease-linear duration-100 inline-flex {{ Request::routeIs('certifications') ? $navItemActiveClass : $navItemClass }}">
                        Formation Continue
                    </a>
                </li>
                <li class="relative flex w-full lg:w-max group">
                    <a href="{{ route('pages.pricings') }}" wire:navigate aria-label="Lien vers la page : Tarifs"
                       class="py-2 ease-linear duration-100 inline-flex {{ Request::routeIs('pages.pricings') ? $navItemActiveClass : $navItemClass }}">
                        Nos tarifs
                    </a>
                </li>
                <li class="relative flex w-full lg:w-max group">
                    <a target="_blank" href="#" aria-label="Lien vers la page : Accueil"
                       class="py-2 ease-linear duration-100 inline-flex {{ $navItemClass }}">
                        iRMA Association
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             class="size-3 ml-0.5 mb-2">
                            <path fill-rule="evenodd"
                                  d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z"
                                  clip-rule="evenodd"/>
                            <path fill-rule="evenodd"
                                  d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
        <div class="lg:min-w-max flex justify-end items-center gap-x-2">
            @guest
                <a href="{{ route('login') }}" wire:navigate
                   class="hidden sm:flex btn btn-sm sm:btn-md btn-ghost text-fg-subtitle hover:bg-primary-50 group">
                    <span class="relative z-10">
                        Se connecter
                    </span>
                </a>
                <a href="{{ route('register') }}" wire:navigate
                   class="btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group">
                    <span class="relative z-10">
                        S'inscrire
                    </span>
                    <span data-btn-layer class="before:bg-primary-800"></span>
                </a>
            @endguest
            @auth
                <div class="flex items-center gap-2">
                    {{-- Indicateur visuel utilisateur connecté --}}
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-700">{{ auth()->user()->name }}</span>
                    </div>

                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group">
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Mon Espace
                        </span>
                        <span data-btn-layer class="before:bg-primary-800"></span>
                    </a>
                </div>
            @endauth
            <div class="flex lg:hidden pr-0.5 py-1 border-l border-gray-200/80 -mr-2.5">
                <button data-nav-trigger data-toggle-nav="app-main" data-expanded="false"
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
