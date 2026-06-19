<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body
        class="h-screen bg-cover bg-center font-sans text-text-pri antialiased"
        style="background-image: url('{{ asset('river-landscape-illustration-pixel-art-style.jpg') }}')"
    >
        {{ $slot }}

        <x-toast-container />


        @livewireScripts
    </body>
</html>
