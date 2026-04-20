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
            <x-text-input 
                id="password" 
                class="block mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition" 
                type="password" 
                name="password" 
                required 
                placeholder="Masukkan password"
            />
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