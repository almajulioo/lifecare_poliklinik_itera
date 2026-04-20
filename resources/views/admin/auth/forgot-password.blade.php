<x-guest-layout>
    <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-6 p-6">
        @csrf

        <!-- Status Message -->
        @if (session('status'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-800 text-sm">{{ session('status') }}</p>
            </div>
        @endif

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

        <!-- Submit Button -->
        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="w-full justify-center py-2.5 font-semibold">
                Kirim Link Reset Password
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
