@props(['whiteHeader' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/irma-logo-base.svg') }}">

    @vite('resources/css/app.css')
</head>
<body class="font-sans antialiased">
    <header class="{{ $whiteHeader ? 'bg-white shadow-sm' : 'bg-transparent' }} sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-5 sm:px-10 py-4 flex items-center justify-between">
            <a href="{{ route('home-page') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="{{ config('app.name') }}" class="h-8 w-auto" onerror="this.style.display='none'">
                <span class="font-semibold text-lg">{{ config('app.name') }}</span>
            </a>
            @include('livewire.welcome.navigation')
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>
</body>
</html>
