<x-guest-layout>
    <div class="mb-4 px-5 py-2 mt-4 text-sm text-gray-600">
        {{ __('Lupa password? Tidak Masalah. Beritahu kami alamat email Anda dan kami akan mengirimkan tautan pengaturan ulang password yang memungkinkan Anda memilih password baru.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="px-5">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4 px-5 mb-4">
            <x-primary-button>
                {{ __('Kirim Tautan Pengaturan Ulang Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
