<x-guest-layout>
    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6 p-6">
        @csrf

        <!-- Email Field -->
        <div>
            <x-input-label for="email" value="Email Admin" />
            <x-text-input 
                id="email" 
                class="block mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                placeholder="Masukkan email admin"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password Field -->
        <div>
            <x-input-label for="password" value="Password" />
            <div class="relative mt-2">
                <x-text-input 
                    id="password" 
                    class="block w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition" 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="Masukkan password"
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
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Login Button -->
        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-2.5 font-semibold">
                Login Admin
            </x-primary-button>
        </div>

        <!-- Forgot Password Link -->
        <div class="text-center">
            <a href="{{ route('admin.password.request') }}" class="text-sm text-black-400 hover:text-black-500 font-medium">
                Lupa Password?
            </a>
        </div>
    </form>
</x-guest-layout>