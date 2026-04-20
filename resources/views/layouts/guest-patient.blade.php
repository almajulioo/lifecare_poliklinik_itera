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
            
            /* Input focus styling */
            input:focus, select:focus, textarea:focus {
                border-color: var(--primary-color) !important;
                box-shadow: 0 0 0 3px rgba(22, 186, 197, 0.1) !important;
            }
            
            /* Smooth transitions */
            input, select, textarea, button {
                transition: all 0.2s ease;
            }
            
            /* Alt text color for links */
            a {
                text-decoration: none;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- Background Image with overlay for transparency (Patient Login) -->
        <div class="fixed inset-0 -z-10" style="
            background-image: url('{{ asset('images/Drone-Itera.jpeg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        "></div>
        
        <!-- Semi-transparent overlay to control image brightness -->
        <div class="fixed inset-0 -z-10" style="
            background-color: rgba(0, 0, 0, 0.4);
        "></div>

        <div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-0 relative z-10">
            <!-- Mobile Container -->
            <div class="w-full max-w-md">
                <!-- Header with Logo -->
                <div class="pb-8 text-center">
                    <a href="/" class="inline-block">
                        <div class="flex flex-col items-center gap-4">
                            <!-- ITERA Logo -->
                            <img src="{{ asset('images/logo-itera.png') }}" alt="ITERA Logo" class="h-24 w-auto">
                            <!-- Poliklinik Text -->
                            <div>
                                <h1 class="text-4xl font-bold text-gray-800">POLIKLINIK ITERA</h1>
                                <p class="text-base text-gray-600 mt-2">Institut Teknologi Sumatera</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Content with semi-transparent white background -->
                <div class="bg-white bg-opacity-95 rounded-xl shadow-2xl overflow-hidden">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
