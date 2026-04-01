<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'LifeCare') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans antialiased">
        <div class="flex h-screen bg-gray-50">
            <!-- Sidebar Fixed -->
            <aside class="w-64 bg-white border-r border-gray-200 fixed h-screen overflow-y-auto">
                <!-- Logo & Brand -->
                <div class="p-6 border-b border-gray-200">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-itera.png') }}" alt="ITERA Logo" class="h-10 w-10 object-contain">
                        <div>
                            <div class="font-bold text-gray-900">Poliklinik ITERA</div>
                            <div class="text-xs text-gray-500">Admin</div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Menu -->
                <nav class="px-3 py-6 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l-7-4m0 0H3m16 4l7-4m0 0V9"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Pengguna -->
                    <a href="{{ route('admin.pengguna.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.pengguna.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"></path>
                        </svg>
                        <span>Pengguna</span>
                    </a>

                    <!-- Riwayat Pengingat -->
                    <a href="{{ route('admin.riwayat.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.riwayat.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Riwayat Pengingat</span>
                    </a>

                    <!-- Manajemen Pasien -->
                    <a href="{{ route('admin.clinic-patients.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.clinic-patients.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Manajemen Pasien</span>
                    </a>

                    <!-- Obat -->
                    <a href="{{ route('admin.obat.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.obat.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Obat</span>
                    </a>

                    <!-- Rekam Medis -->
                    <a href="{{ route('admin.rekam-medis.index') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.rekam-medis.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Rekam Medis</span>
                    </a>

                    <!-- Tambah Jadwal -->
                    <a href="{{ route('admin.schedules.create') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors
                              {{ request()->routeIs('admin.schedules.create') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tambah Jadwal</span>
                    </a>
                </nav>

                <!-- Divider -->
                <div class="border-t border-gray-200 mx-3 my-4"></div>

                <!-- Logout -->
                <div class="px-3 py-4">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 ml-64 flex flex-col">
                <!-- Top Navigation Bar -->
                <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
                    <div class="flex items-center justify-between px-8 py-4">
                        <!-- Left: Page Title -->
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page_title', 'Dashboard')</h1>

                        <!-- Right: User Profile -->
                        <div class="flex items-center gap-6">
                            <!-- User Profile Dropdown -->
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ substr(auth('admin')->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <!-- Info -->
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ auth('admin')->user()->name ?? 'Admin' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Koordinator</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-auto p-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
