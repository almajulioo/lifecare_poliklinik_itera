<x-guest-layout>
    <!-- Back Button -->
    <div class="px-6 pt-4 pb-2">
        <a href="/" class="inline-flex items-center p-2 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <!-- Title Section -->
    <div class="text-center mt-2 mb-4 px-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Welcome Back!</h1>
        <p class="text-sm text-gray-600 mb-3">Log in to manage your clinic appointments and healthcare</p>
        <p class="text-sm text-gray-600">Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary-color);" class="font-semibold hover:underline">Sign up</a></p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-7 p-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-semibold text-sm text-gray-800 mb-2.5">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2.5">
                <label for="password" class="block font-semibold text-sm text-gray-800">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color: var(--primary-color);" class="text-sm font-semibold hover:underline">Forgot password?</a>
                @endif
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
        </div>

        <!-- Login Button -->
        <div class="pt-4">
            <button
                type="submit"
                class="w-full py-3.5 rounded-lg font-bold text-white text-base transition hover:opacity-90 shadow-sm"
                style="background-color: var(--primary-color);"
            >
                Sign In
            </button>
        </div>
    </form>
</x-guest-layout>
