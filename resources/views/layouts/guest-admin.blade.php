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
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-blue-50 to-teal-50">
        <!-- Background gradient without image (Admin Login) -->
        <div class="fixed inset-0 -z-10" style="
            background: linear-gradient(135deg, #f0f9ff 0%, #f0fdfa 100%);
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

        <script>
            function togglePasswordVisibility(fieldId) {
                const input = document.getElementById(fieldId);
                const icon = document.getElementById(fieldId + '-icon');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    // Change icon to eye-off
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM19.5 13.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l.75 1.5m4.5-4.5l.75 1.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    `;
                } else {
                    input.type = 'password';
                    // Change icon back to eye
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    `;
                }
            }
        </script>
    </body>
</html>
