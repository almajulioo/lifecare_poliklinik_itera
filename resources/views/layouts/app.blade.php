<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - LifeCare+')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <header class="bg-white border-b p-4 flex items-center justify-between">
            <div class="font-semibold">Admin LifeCare+</div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.schedules.index') }}" class="text-sm underline">
                    Jadwal
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="text-sm text-red-600">Logout</button>
                </form>
            </div>
        </header>

        <main class="p-4">
            @yield('content')
        </main>
    </div>
</body>
</html>