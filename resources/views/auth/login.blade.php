<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Title and Subtitle -->
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome!</h1>
        <p class="text-sm text-gray-600">Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary-color);" class="font-semibold hover:opacity-80">Sign up</a></p>
    </div>

    <!-- Tab Navigation -->
    <div class="flex justify-center gap-4 mb-8">
        <button type="button" disabled class="px-6 py-2 text-white rounded-full font-semibold" style="background-color: var(--primary-color);">
            Login
        </button>
        <a href="{{ route('register') }}" class="px-6 py-2 text-gray-900 rounded-full font-semibold hover:bg-gray-100">
            Register
        </a>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
                class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
            />
            <p class="text-xs text-gray-500 mt-2">Supportive text</p>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Password</label>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
                />
            </div>
            <div class="flex justify-between items-center mt-2">
                <p class="text-xs text-gray-500">Supportive text</p>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color: var(--primary-color);" class="text-xs font-semibold hover:opacity-80">Forgot Password</a>
                @endif
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
        </div>

        <!-- Login Button -->
        <button
            type="submit"
            class="w-full py-3 rounded-full font-bold text-white text-sm mt-8 transition hover:opacity-90"
            style="background-color: var(--primary-color);"
        >
            Login
        </button>
    </form>
</x-guest-layout>
