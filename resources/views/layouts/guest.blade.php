<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>PawMart Pet Shop</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-pm-cream text-pm-charcoal dark:bg-gray-950 dark:text-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 px-4">
            <div>
                <a href="/" class="transition-transform duration-200 hover:scale-105 inline-block">
                    <x-application-logo class="w-auto h-12" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white dark:bg-gray-900 border border-pm-border shadow-card overflow-hidden sm:rounded-[2rem]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
