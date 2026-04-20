<x-guest-layout>
    <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-6 p-6">
        @csrf

        <!-- Hidden Fields -->
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- Email Field (Display Only) -->
        <div>
            <x-input-label for="email_display" value="Email Admin" />
            <input 
                id="email_display" 
                class="block mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed" 
                type="email" 
                value="{{ $email }}"
                disabled
            />
        </div>

        <!-- Password Field -->
        <div>
            <x-input-label for="password" value="Password Baru" />
            <x-text-input 
                id="password" 
                class="block mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition" 
                type="password" 
                name="password" 
                required 
                placeholder="Masukkan password baru (minimal 8 karakter)"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Password Confirmation Field -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input 
                id="password_confirmation" 
                class="block mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition" 
                type="password" 
                name="password_confirmation" 
                required 
                placeholder="Konfirmasi password baru"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-2.5 font-semibold">
                Reset Password
            </x-primary-button>
        </div>

        <!-- Back to Login -->
        <div class="text-center">
            <a href="{{ route('admin.login') }}" class="text-sm text-teal-600 hover:text-teal-700 font-medium">
                Kembali ke Login
            </a>
        </div>
    </form>
</x-guest-layout>
