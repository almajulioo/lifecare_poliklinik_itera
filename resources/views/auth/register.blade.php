<x-guest-layout>
    <!-- Title and Subtitle -->
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Create an account</h1>
        <p class="text-sm text-gray-600">Already have an account? <a href="{{ route('login') }}" style="color: var(--primary-color);" class="font-semibold hover:opacity-80">Log in</a></p>
    </div>

    <!-- Tab Navigation -->
    <div class="flex justify-center gap-4 mb-8">
        <a href="{{ route('login') }}" class="px-6 py-2 text-gray-900 rounded-full font-semibold hover:bg-gray-100">
            Login
        </a>
        <button type="button" disabled class="px-6 py-2 text-white rounded-full font-semibold" style="background-color: var(--primary-color);">
            Register
        </button>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Role -->
        <div>
            <label for="role_user" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Role</label>
            <select
                id="role_user"
                name="role_user"
                required
                class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm focus:outline-none focus:border-gray-400 transition bg-white"
            >
                <option value="">-- Pilih Role --</option>
                <option value="mahasiswa" {{ old('role_user') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="pegawai" {{ old('role_user') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">Supportive text</p>
            <x-input-error :messages="$errors->get('role_user')" class="mt-2 text-xs" />
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama anda"
                class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
            />
            <p class="text-xs text-gray-500 mt-2">Supportive text</p>
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-xs" />
        </div>

        <!-- NIM + Prodi (khusus Mahasiswa) -->
        <div id="mahasiswaFields" style="display:none;" class="space-y-5">
            <div>
                <label for="nim" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">NIM</label>
                <input
                    id="nim"
                    type="text"
                    name="nim"
                    value="{{ old('nim') }}"
                    placeholder="Nomor induk mahasiswa"
                    class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
                />
                <p class="text-xs text-gray-500 mt-2">Supportive text</p>
                <x-input-error :messages="$errors->get('nim')" class="mt-2 text-xs" />
            </div>

            <div>
                <label for="prodi" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Program Studi</label>
                <input
                    id="prodi"
                    type="text"
                    name="prodi"
                    value="{{ old('prodi') }}"
                    placeholder="Program studi anda"
                    class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
                />
                <p class="text-xs text-gray-500 mt-2">Supportive text</p>
                <x-input-error :messages="$errors->get('prodi')" class="mt-2 text-xs" />
            </div>
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Alamat Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="you@example.com"
                class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
            />
            <p class="text-xs text-gray-500 mt-2">Supportive text</p>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Kata Sandi</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
            />
            <p class="text-xs text-gray-500 mt-2">Supportive text</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wider">Konfirmasi Kata Sandi</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-2xl border-2 border-gray-300 text-sm placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
            />
            <p class="text-xs text-gray-500 mt-2">Supportive text</p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs" />
        </div>

        <!-- Register Button -->
        <button
            type="submit"
            class="w-full py-3 rounded-full font-bold text-white text-sm mt-8 transition hover:opacity-90"
            style="background-color: var(--primary-color);"
        >
            Sign Up
        </button>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleSelect = document.getElementById('role_user');
            const mahasiswaFields = document.getElementById('mahasiswaFields');

            function toggleFields() {
                mahasiswaFields.style.display = (roleSelect.value === 'mahasiswa') ? 'block' : 'none';
            }

            roleSelect.addEventListener('change', toggleFields);
            toggleFields(); // untuk old value
        });
        </script>
    </form>
</x-guest-layout>
