<x-guest-layout>
    <!-- Title Section -->
    <div class="text-center mb-8 px-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Create Your Account</h1>
        <p class="text-sm text-gray-600 mb-3">Join us to manage your clinic appointments and healthcare efficiently</p>
        <p class="text-sm text-gray-600">Already have an account? <a href="{{ route('login') }}" style="color: var(--primary-color);" class="font-semibold hover:underline">Sign in</a></p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-7 p-6">
        @csrf

        <!-- Role -->
        <div>
            <label for="role_user" class="block font-semibold text-sm text-gray-800 mb-2.5">Select Your Role</label>
            <select
                id="role_user"
                name="role_user"
                required
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition bg-white" 
                style="focus:ring-color: var(--primary-color);"
            >
                <option value="">-- Pilih Role --</option>
                <option value="mahasiswa" {{ old('role_user') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="pegawai" {{ old('role_user') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
            </select>
            <x-input-error :messages="$errors->get('role_user')" class="mt-2 text-sm" />
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block font-semibold text-sm text-gray-800 mb-2.5">Full Name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Enter your full name"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm" />
        </div>

        <!-- NIM + Prodi (khusus Mahasiswa) -->
        <div id="mahasiswaFields" style="display:none;" class="space-y-7 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div>
                <label for="nim" class="block font-semibold text-sm text-gray-800 mb-2.5">Student ID (NIM)</label>
                <input
                    id="nim"
                    type="text"
                    name="nim"
                    value="{{ old('nim') }}"
                    placeholder="Enter your student ID"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                    style="focus:ring-color: var(--primary-color);"
                />
                <x-input-error :messages="$errors->get('nim')" class="mt-2 text-sm" />
            </div>

            <div>
                <label for="prodi" class="block font-semibold text-sm text-gray-800 mb-2.5">Program of Study</label>
                <input
                    id="prodi"
                    type="text"
                    name="prodi"
                    value="{{ old('prodi') }}"
                    placeholder="Enter your program of study"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                    style="focus:ring-color: var(--primary-color);"
                />
                <x-input-error :messages="$errors->get('prodi')" class="mt-2 text-sm" />
            </div>
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-semibold text-sm text-gray-800 mb-2.5">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="you@example.com"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block font-semibold text-sm text-gray-800 mb-2.5">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Create a strong password"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block font-semibold text-sm text-gray-800 mb-2.5">Confirm Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm your password"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm" />
        </div>

        <!-- Register Button -->
        <div class="pt-4">
            <button
                type="submit"
                class="w-full py-3.5 rounded-lg font-bold text-white text-base transition hover:opacity-90 shadow-sm"
                style="background-color: var(--primary-color);"
            >
                Create Account
            </button>
        </div>

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
