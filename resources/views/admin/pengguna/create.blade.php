@extends('admin.layouts.app')

@section('title', 'Tambah Pengguna')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.pengguna.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <x-admin.card>
        <form action="{{ route('admin.pengguna.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">
                    Nama Lengkap
                </label>
                <input 
                    type="text" 
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Masukkan nama lengkap pengguna"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                />
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                    Email
                </label>
                <input 
                    type="email" 
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    placeholder="contoh@email.com"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                />
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIM Field -->
            <div id="nim-field">
                <label for="nim" class="block text-sm font-semibold text-gray-900 mb-2">
                    ID/NIM
                </label>
                <input 
                    type="text" 
                    id="nim"
                    name="nim"
                    value="{{ old('nim') }}"
                    placeholder="Masukkan ID atau NIM pengguna"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nim') border-red-500 @enderror"
                />
                @error('nim')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Field -->
            <div>
                <label for="role_user" class="block text-sm font-semibold text-gray-900 mb-2">
                    Role
                </label>
                <select 
                    id="role_user"
                    name="role_user"
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role_user') border-red-500 @enderror"
                >
                    <option value="">-- Pilih Role --</option>
                    <option value="mahasiswa" {{ old('role_user') == 'mahasiswa' ? 'selected' : '' }}>
                        Mahasiswa
                    </option>
                    <option value="pegawai" {{ old('role_user') == 'pegawai' ? 'selected' : '' }}>
                        Pegawai
                    </option>
                </select>
                @error('role_user')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prodi Field -->
            <div id="prodi-field">
                <label for="prodi" class="block text-sm font-semibold text-gray-900 mb-2">
                    Program Studi
                </label>
                <select 
                    id="prodi"
                    name="prodi"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('prodi') border-red-500 @enderror"
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
                    <option value="TEKNIK GEOLOGI" {{ old('prodi') == 'TEKNIK GEOLOGI' ? 'selected' : '' }}>TEKNIK GEOLOGI</option>
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
                @error('prodi')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
                    Password
                </label>
                <input 
                    type="password" 
                    id="password"
                    name="password"
                    required
                    placeholder="Minimal 8 karakter"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                />
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
                    Konfirmasi Password
                </label>
                <input 
                    type="password" 
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    placeholder="Ketikkan ulang password Anda"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password_confirmation') border-red-500 @enderror"
                />
                @error('password_confirmation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <a 
                    href="{{ route('admin.pengguna.index') }}" 
                    class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium text-center"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </x-admin.card>
</div>

<script>
    // Handle role change to show/hide NIM and Prodi fields
    const roleSelect = document.getElementById('role_user');
    const nimField = document.getElementById('nim-field');
    const prodiField = document.getElementById('prodi-field');
    const nimInput = document.getElementById('nim');

    function updateFieldVisibility() {
        const selectedRole = roleSelect.value;
        
        if (selectedRole === 'pegawai') {
            // Hide NIM and Prodi fields for Pegawai
            nimField.style.display = 'none';
            prodiField.style.display = 'none';
            // Remove required attribute and clear value
            nimInput.removeAttribute('required');
            nimInput.value = '';
        } else if (selectedRole === 'mahasiswa') {
            // Show NIM and Prodi fields for Mahasiswa
            nimField.style.display = 'block';
            prodiField.style.display = 'block';
            // Add required attribute back
            nimInput.setAttribute('required', 'required');
        }
    }

    // Run on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        updateFieldVisibility();
    });

    // Run when role changes
    roleSelect.addEventListener('change', updateFieldVisibility);
</script>
@endsection
