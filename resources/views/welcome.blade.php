@props(['title'])
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') ?? $title }}</title>

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset("images/irma-logo-base.svg") }}">
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-hidden overflow-y-auto bg-bg min-h-screen">
<x-frontend.header/>
<div class="absolute top-0 left-0 inset-x-0 h-40 flex">
    <span class="flex w-60 h-36 bg-gradient-to-tr from-primary rounded-full blur-2xl opacity-65"></span>
</div>
<main>
    <x-notification-popup/>
    {{ $slot }}
</main>
<x-frontend.footer/>
</body>
</html>
