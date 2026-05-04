<x-patient-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <!-- Title Section -->
    <div class="text-center px-6 mt-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Selamat Datang!</h1>
        <p class="text-sm text-gray-600 mb-3">Masuk untuk menggunakan aplikasi</p>
        <p class="text-sm text-gray-600">Belum punya akun? <a href="{{ route('register') }}" style="color: var(--black-color);" class="font-semibold hover:underline underline">Daftar</a></p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-2 p-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-semibold text-sm text-gray-800 mb-2.5">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
                oninvalid="this.setCustomValidity('Silakan masukkan email yang valid.')"
                oninput="this.setCustomValidity('')"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2.5">
                <label for="password" class="block font-semibold text-sm text-gray-800">Password</label>
            </div>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="block w-full px-4 py-3 pr-11 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                    style="focus:ring-color: var(--primary-color);"
                />
                <button
                    type="button"
                    onclick="togglePasswordVisibility('password')"
                    class="absolute right-0 top-0 h-full px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none transition rounded-r-lg"
                    tabindex="-1"
                >
                    <svg id="password-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color: var(--black-color);" class="text-sm font-semibold hover:underline">Lupa password?</a>
                @endif
        </div>

        <!-- Login Button -->
        <div class="pt-4">
            <button
                type="submit"
                class="w-full py-3.5 rounded-lg font-bold text-white text-base transition hover:opacity-90 shadow-sm"
                style="background-color: var(--primary-color);"
            >
                Masuk
            </button>
        </div>
    </form>
</x-patient-guest-layout>
