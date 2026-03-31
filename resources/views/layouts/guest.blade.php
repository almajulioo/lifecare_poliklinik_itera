<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --primary-color: #16bac5;
                --primary-dark: #0a9ca3;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen flex flex-col items-center justify-start p-4 sm:p-0 sm:justify-center bg-white">
            <!-- Mobile Container -->
            <div class="w-full max-w-md">
                <!-- Header with Logo -->
                <div class="pt-6 pb-8 text-center">
                    <a href="/" class="inline-block">
                        <div class="text-4xl font-bold tracking-tight">
                            <span style="color: var(--primary-color);">LIFE</span><span style="color: #1a1a1a;">CARE</span><span style="color: var(--primary-color);">+</span>
                        </div>
                    </a>
                </div>

                <!-- Content -->
                <div class="bg-white">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
