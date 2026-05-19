<x-patient-guest-layout>
    <x-auth-session-status class="mb-6 text-center" :status="session('status')" />

    <!-- Title Section -->
    <div class="text-center px-6 mt-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Buat Akun Baru</h1>
        <p class="text-sm text-gray-600">Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--black-color);" class="font-semibold hover:underline">Masuk</a></p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-2 p-6">
        @csrf

        <!-- Role -->
        <div>
            <label for="role_user" class="block font-semibold text-sm text-gray-800 mb-2.5">Pilih Status</label>
            <select
                id="role_user"
                name="role_user"
                required
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition bg-white" 
                style="focus:ring-color: var(--primary-color);"
            >
                <option value="">-- Pilih Status --</option>
                <option value="mahasiswa" {{ old('role_user') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="pegawai" {{ old('role_user') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
            </select>
            <x-input-error :messages="$errors->get('role_user')" class="mt-2 text-sm" />
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block font-semibold text-sm text-gray-800 mb-2.5">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap Anda"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm" />
        </div>

        <!-- NIM + Prodi (khusus Mahasiswa) -->
        <div id="mahasiswaFields" style="display:none;" class="space-y-2 p-4 bg-blue-50 rounded-lg border border-blue-200 mt-2">
            <div>
                <label for="nim" class="block font-semibold text-sm text-gray-800 mb-2.5">NIM</label>
                <input
                    id="nim"
                    type="text"
                    name="nim"
                    value="{{ old('nim') }}"
                    placeholder="Masukkan NIM Anda"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                    style="focus:ring-color: var(--primary-color);"
                />
                <x-input-error :messages="$errors->get('nim')" class="mt-2 text-sm" />
            </div>

            <div>
                <label for="prodi" class="block font-semibold text-sm text-gray-800 mb-2.5">Program of Study</label>
                <select
                    id="prodi"
                    name="prodi"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition bg-white" 
                    style="focus:ring-color: var(--primary-color);"
                >
                    <option value="">-- Pilih Program Studi --</option>
                    <option value="Arsitektur" {{ old('prodi') == 'Arsitektur' ? 'selected' : '' }}>Arsitektur</option>
                    <option value="Arsitektur Lanskap" {{ old('prodi') == 'Arsitektur Lanskap' ? 'selected' : '' }}>Arsitektur Lanskap</option>
                    <option value="Biologi" {{ old('prodi') == 'Biologi' ? 'selected' : '' }}>Biologi</option>
                    <option value="Desain Komunikasi Visual" {{ old('prodi') == 'Desain Komunikasi Visual' ? 'selected' : '' }}>Desain Komunikasi Visual</option>
                    <option value="Farmasi" {{ old('prodi') == 'Farmasi' ? 'selected' : '' }}>Farmasi</option>
                    <option value="Fisika" {{ old('prodi') == 'Fisika' ? 'selected' : '' }}>Fisika</option>
                    <option value="Kimia" {{ old('prodi') == 'Kimia' ? 'selected' : '' }}>Kimia</option>
                    <option value="Matematika" {{ old('prodi') == 'Matematika' ? 'selected' : '' }}>Matematika</option>
                    <option value="Pariwisata" {{ old('prodi') == 'Pariwisata' ? 'selected' : '' }}>Pariwisata</option>
                    <option value="Perencanaan Wilayah dan Kota" {{ old('prodi') == 'Perencanaan Wilayah dan Kota' ? 'selected' : '' }}>Perencanaan Wilayah dan Kota</option>
                    <option value="Rekayasa Instrumentasi dan Automasi" {{ old('prodi') == 'Rekayasa Instrumentasi dan Automasi' ? 'selected' : '' }}>Rekayasa Instrumentasi dan Automasi</option>
                    <option value="Rekayasa Kebutanan" {{ old('prodi') == 'Rekayasa Kebutanan' ? 'selected' : '' }}>Rekayasa Kebutanan</option>
                    <option value="Rekayasa Keolahragan" {{ old('prodi') == 'Rekayasa Keolahragan' ? 'selected' : '' }}>Rekayasa Keolahragan</option>
                    <option value="Rekayasa Kosmetik" {{ old('prodi') == 'Rekayasa Kosmetik' ? 'selected' : '' }}>Rekayasa Kosmetik</option>
                    <option value="Rekayasa Minyak dan Gas" {{ old('prodi') == 'Rekayasa Minyak dan Gas' ? 'selected' : '' }}>Rekayasa Minyak dan Gas</option>
                    <option value="Rekayasa Tata Kelola Air Terpadu" {{ old('prodi') == 'Rekayasa Tata Kelola Air Terpadu' ? 'selected' : '' }}>Rekayasa Tata Kelola Air Terpadu</option>
                    <option value="Sains Aktuaria" {{ old('prodi') == 'Sains Aktuaria' ? 'selected' : '' }}>Sains Aktuaria</option>
                    <option value="Sains Atmosfir dan Keplanetan" {{ old('prodi') == 'Sains Atmosfir dan Keplanetan' ? 'selected' : '' }}>Sains Atmosfir dan Keplanetan</option>
                    <option value="Sains Data" {{ old('prodi') == 'Sains Data' ? 'selected' : '' }}>Sains Data</option>
                    <option value="Sains Lingkungan Kelautan" {{ old('prodi') == 'Sains Lingkungan Kelautan' ? 'selected' : '' }}>Sains Lingkungan Kelautan</option>
                    <option value="Teknik Biomedis" {{ old('prodi') == 'Teknik Biomedis' ? 'selected' : '' }}>Teknik Biomedis</option>
                    <option value="Teknik Biosistem" {{ old('prodi') == 'Teknik Biosistem' ? 'selected' : '' }}>Teknik Biosistem</option>
                    <option value="Teknik Elektro" {{ old('prodi') == 'Teknik Elektro' ? 'selected' : '' }}>Teknik Elektro</option>
                    <option value="Teknik Fisika" {{ old('prodi') == 'Teknik Fisika' ? 'selected' : '' }}>Teknik Fisika</option>
                    <option value="Teknik Geofisika" {{ old('prodi') == 'Teknik Geofisika' ? 'selected' : '' }}>Teknik Geofisika</option>
                    <option value="Teknik Geologi" {{ old('prodi') == 'Teknik Geologi' ? 'selected' : '' }}>Teknik Geologi</option>
                    <option value="Teknik Geomatika" {{ old('prodi') == 'Teknik Geomatika' ? 'selected' : '' }}>Teknik Geomatika</option>
                    <option value="Teknik Industri" {{ old('prodi') == 'Teknik Industri' ? 'selected' : '' }}>Teknik Industri</option>
                    <option value="Teknik Informatika" {{ old('prodi') == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                    <option value="Teknik Kelautan" {{ old('prodi') == 'Teknik Kelautan' ? 'selected' : '' }}>Teknik Kelautan</option>
                    <option value="Teknik Kimia" {{ old('prodi') == 'Teknik Kimia' ? 'selected' : '' }}>Teknik Kimia</option>
                    <option value="Teknik Lingkungan" {{ old('prodi') == 'Teknik Lingkungan' ? 'selected' : '' }}>Teknik Lingkungan</option>
                    <option value="Teknik Material" {{ old('prodi') == 'Teknik Material' ? 'selected' : '' }}>Teknik Material</option>
                    <option value="Teknik Mesin" {{ old('prodi') == 'Teknik Mesin' ? 'selected' : '' }}>Teknik Mesin</option>
                    <option value="Teknik Perkeretaapian" {{ old('prodi') == 'Teknik Perkeretaapian' ? 'selected' : '' }}>Teknik Perkeretaapian</option>
                    <option value="Teknik Pertambangan" {{ old('prodi') == 'Teknik Pertambangan' ? 'selected' : '' }}>Teknik Pertambangan</option>
                    <option value="Teknik Sipil" {{ old('prodi') == 'Teknik Sipil' ? 'selected' : '' }}>Teknik Sipil</option>
                    <option value="Teknik Sistem Energi" {{ old('prodi') == 'Teknik Sistem Energi' ? 'selected' : '' }}>Teknik Sistem Energi</option>
                    <option value="Teknik Telekomunikasi" {{ old('prodi') == 'Teknik Telekomunikasi' ? 'selected' : '' }}>Teknik Telekomunikasi</option>
                    <option value="Teknologi Industri Pertanian" {{ old('prodi') == 'Teknologi Industri Pertanian' ? 'selected' : '' }}>Teknologi Industri Pertanian</option>
                    <option value="Teknologi Pangan" {{ old('prodi') == 'Teknologi Pangan' ? 'selected' : '' }}>Teknologi Pangan</option>
                </select>
                <x-input-error :messages="$errors->get('prodi')" class="mt-2 text-sm" />
            </div>
        </div>

        <!-- Usia dan Jenis Kelamin (untuk semua user) -->
        <div class="space-y-2 p-4 bg-green-50 rounded-lg border border-green-200 mt-2">
            <div class="grid grid-cols-2 gap-3">
                <!-- Usia -->
                <div>
                    <label for="age" class="block font-semibold text-sm text-gray-800 mb-2.5">Usia (Tahun)</label>
                    <input
                        id="age"
                        type="number"
                        name="age"
                        value="{{ old('age') }}"
                        min="1"
                        max="150"
                        placeholder="Contoh: 20"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                        style="focus:ring-color: var(--primary-color);"
                    />
                    <x-input-error :messages="$errors->get('age')" class="mt-2 text-sm" />
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="gender" class="block font-semibold text-sm text-gray-800 mb-2.5">Jenis Kelamin</label>
                    <select
                        id="gender"
                        name="gender"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition bg-white" 
                        style="focus:ring-color: var(--primary-color);"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="laki-laki" {{ old('gender') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('gender') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2 text-sm" />
                </div>
            </div>

            <!-- Nomor Telepon -->
            <div>
                <label for="phone" class="block font-semibold text-sm text-gray-800 mb-2.5">Nomor Telepon</label>
                <input
                    id="phone"
                    type="tel"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="Contoh: +62812345678"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                    style="focus:ring-color: var(--primary-color);"
                />
                <x-input-error :messages="$errors->get('phone')" class="mt-2 text-sm" />
            </div>
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-semibold text-sm text-gray-800 mb-2.5">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="you.123456@itera.ac.id"
                oninvalid="this.setCustomValidity('Silakan masukkan email yang valid.')"
                oninput="this.setCustomValidity('')"
                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                style="focus:ring-color: var(--primary-color);"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block font-semibold text-sm text-gray-800 mb-2.5">Password</label>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Buat password yang kuat"
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
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block font-semibold text-sm text-gray-800 mb-2.5">Konfirmasi Password</label>
            <div class="relative">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Konfirmasi password Anda"
                    class="block w-full px-4 py-3 pr-11 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 transition" 
                    style="focus:ring-color: var(--primary-color);"
                />
                <button
                    type="button"
                    onclick="togglePasswordVisibility('password_confirmation')"
                    class="absolute right-0 top-0 h-full px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none transition rounded-r-lg"
                    tabindex="-1"
                >
                    <svg id="password_confirmation-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm" />
        </div>

        <!-- Register Button -->
        <div class="pt-4">
            <button
                type="submit"
                class="w-full py-3.5 rounded-lg font-bold text-white text-base transition hover:opacity-90 shadow-sm"
                style="background-color: var(--primary-color);"
            >
                Daftar
            </button>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleSelect = document.getElementById('role_user');
            const mahasiswaFields = document.getElementById('mahasiswaFields');
            const prodiSelect = document.getElementById('prodi');

            function toggleFields() {
                const isMahasiswa = roleSelect.value == 'mahasiswa';
                mahasiswaFields.style.display = isMahasiswa ? 'block' : 'none';
                
                // Dinamis add/remove required pada prodi berdasarkan role
                if (isMahasiswa) {
                    prodiSelect.setAttribute('required', 'required');
                } else {
                    prodiSelect.removeAttribute('required');
                    prodiSelect.value = ''; // Reset value saat hidden
                }
            }

            roleSelect.addEventListener('change', toggleFields);
            toggleFields(); // untuk old value
        });
        </script>
    </form>
</x-patient-guest-layout>
