@props(['title'])
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') ?? $title }}</title>

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset("images/irma-logo-base.svg") }}">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>
<body class="overflow-hidden overflow-y-auto bg-bg">
<div class="min-h-screen">
    <livewire:layout.navigation/>
    <main class="py-8 relative">
        <x-notification-popup/>
        {{ $slot }}
    </main>
</div>
@filamentScripts
@vite('resources/js/app.js')
</body>
</html>
