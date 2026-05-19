@extends('admin.layouts.app')

@section('title', 'Tambah Pasien Baru')
@section('page_title', 'Tambah Pasien')

@section('content')
<div class="space-y-8 max-w-4xl">
    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Tambah Pasien Baru</h2>
        <p class="text-sm text-gray-600 mt-1">Daftarkan pasien poliklinik baru ke sistem</p>
    </div>

    <!-- Form Card -->
    <x-admin.card title="Informasi Pasien">
        <form method="POST" action="{{ route('admin.clinic-patients.store') }}" class="space-y-6">
            @csrf

            <!-- Basic Info Section -->
            <div class="space-y-4">
                <h3 class="font-semibold text-gray-900">Data Dasar Pasien</h3>
                
                <!-- Nama Pasien -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Pasien <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama lengkap pasien"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('name') border-red-500 focus:ring-red-500 @enderror"
                        required
                    />
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Identity Number -->
                <div>
                    <label for="identity_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Nomor Identitas (NIM/NIP/No. Identitas)
                    </label>
                    <input 
                        type="text"
                        id="identity_number"
                        name="identity_number"
                        value="{{ old('identity_number') }}"
                        placeholder="Masukkan nomor identitas"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('identity_number') border-red-500 focus:ring-red-500 @enderror"
                    />
                    @error('identity_number')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori Pasien <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="category"
                        name="category"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('category') border-red-500 focus:ring-red-500 @enderror"
                        required
                    >
                        <option value="">-- Pilih Kategori --</option>
                        <option value="mahasiswa" {{ old('category') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="pegawai" {{ old('category') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        <option value="umum" {{ old('category') == 'umum' ? 'selected' : '' }}>Umum</option>
                    </select>
                    @error('category')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Contact Info Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Informasi Kontak</h3>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input 
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Alamat email (opsional)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('email') border-red-500 focus:ring-red-500 @enderror"
                    />
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Jika pasien memiliki akun aplikasi, email harus sesuai dengan akun</p>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Nomor Telepon
                    </label>
                    <input 
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Nomor telepon (opsional)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('phone') border-red-500 focus:ring-red-500 @enderror"
                    />
                    @error('phone')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Personal Info Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Informasi Pribadi</h3>
                
                <!-- Age -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
                            Usia (Tahun)
                        </label>
                        <input 
                            type="number"
                            id="age"
                            name="age"
                            value="{{ old('age') }}"
                            min="1"
                            max="150"
                            placeholder="Contoh: 20"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('age') border-red-500 focus:ring-red-500 @enderror"
                        />
                        @error('age')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Kelamin
                        </label>
                        <select 
                            id="gender"
                            name="gender"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('gender') border-red-500 focus:ring-red-500 @enderror"
                        >
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="laki-laki" {{ old('gender') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="perempuan" {{ old('gender') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Account Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Akun Aplikasi</h3>
                <p class="text-sm text-gray-600">Buat akun aplikasi untuk pasien ini (untuk login dan akses aplikasi)</p>
                
                <!-- Create Account Toggle -->
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="create_user_account"
                            name="create_user_account"
                            value="1"
                            {{ old('create_user_account') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-gray-700">Buat akun aplikasi untuk pasien ini</span>
                    </label>
                </div>

                <!-- Account Credentials Section (shown only if create_user_account is checked) -->
                <div id="account-fields" class="{{ old('create_user_account') ? '' : 'hidden' }} space-y-4">
                    <!-- Program Studi -->
                    <div>
                        <label for="prodi" class="block text-sm font-medium text-gray-700 mb-1">
                            Program Studi
                        </label>
                        <select 
                            id="prodi"
                            name="prodi"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">-- Pilih Program Studi --</option>
                            <option value="Arsitektur" {{ old('prodi') == 'Arsitektur' ? 'selected' : '' }}>Arsitektur</option>
                            <option value="Arsitektur Lanskap" {{ old('prodi') == 'Arsitektur Lanskap' ? 'selected' : '' }}>Arsitektur Lanskap</option>
                            <option value="Biologi" {{ old('prodi') == 'Biologi' ? 'selected' : '' }}>Biologi</option>
                            <option value="Teknik Informatika" {{ old('prodi') == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                            <option value="Teknik Sipil" {{ old('prodi') == 'Teknik Sipil' ? 'selected' : '' }}>Teknik Sipil</option>
                            <option value="Teknik Mesin" {{ old('prodi') == 'Teknik Mesin' ? 'selected' : '' }}>Teknik Mesin</option>
                            <option value="Teknik Elektro" {{ old('prodi') == 'Teknik Elektro' ? 'selected' : '' }}>Teknik Elektro</option>
                            <option value="Matematika" {{ old('prodi') == 'Matematika' ? 'selected' : '' }}>Matematika</option>
                            <option value="Fisika" {{ old('prodi') == 'Fisika' ? 'selected' : '' }}>Fisika</option>
                            <option value="Kimia" {{ old('prodi') == 'Kimia' ? 'selected' : '' }}>Kimia</option>
                            <!-- Add more prodi as needed -->
                        </select>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('password') border-red-500 focus:ring-red-500 @enderror"
                        />
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ketikkan ulang password"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('password_confirmation') border-red-500 focus:ring-red-500 @enderror"
                        />
                        @error('password_confirmation')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-xs text-gray-500">Email akan digunakan sebagai username untuk login. Pastikan email unik dan aktif.</p>
                </div>
            </div>

            <!-- Medical Conditions Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Kondisi Medis</h3>
                <p class="text-sm text-gray-600">Daftar kondisi medis pasien (jika ada)</p>
                
                <!-- Medical Conditions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Kondisi Medis
                    </label>
                    <div id="medicalConditionsContainer" class="space-y-3">
                        @if(old('medical_conditions'))
                            @foreach(old('medical_conditions') as $index => $condition)
                                <div class="flex gap-2 items-start medical-condition-item">
                                    <input 
                                        type="text"
                                        name="medical_conditions[{{ $index }}]"
                                        value="{{ $condition }}"
                                        placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
                                        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <button 
                                        type="button"
                                        onclick="removeMedicalCondition(this)"
                                        class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex gap-2 items-start medical-condition-item">
                                <input 
                                    type="text"
                                    name="medical_conditions[0]"
                                    placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button 
                                    type="button"
                                    onclick="removeMedicalCondition(this)"
                                    class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
                                >
                                    Hapus
                                </button>
                            </div>
                        @endif
                    </div>
                    <button 
                        type="button"
                        onclick="addMedicalCondition()"
                        class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm"
                    >
                        + Tambah Kondisi Medis
                    </button>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Catatan Medis</h3>
                
                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan Tambahan
                    </label>
                    <textarea 
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Masukkan catatan medis tambahan (opsional)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('notes') border-red-500 focus:ring-red-500 @enderror"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Status Pasien</h3>
                
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="status"
                        name="status"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('status') border-red-500 focus:ring-red-500 @enderror"
                        required
                    >
                        <option value="">-- Pilih Status --</option>
                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Simpan Pasien
                </button>
                <a href="{{ route('admin.clinic-patients.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Batal
                </a>
            </div>
        </form>
    </x-admin.card>
</div>

<script>
// Tambahkan event listener ke checkbox untuk toggle account fields
document.addEventListener('DOMContentLoaded', function() {
    const createUserCheckbox = document.getElementById('create_user_account');
    const accountFields = document.getElementById('account-fields');
    
    if (createUserCheckbox) {
        createUserCheckbox.addEventListener('change', function() {
            if (this.checked) {
                accountFields.classList.remove('hidden');
                // Make password required
                document.getElementById('password').setAttribute('required', 'required');
                document.getElementById('password_confirmation').setAttribute('required', 'required');
            } else {
                accountFields.classList.add('hidden');
                // Remove password required
                document.getElementById('password').removeAttribute('required');
                document.getElementById('password_confirmation').removeAttribute('required');
            }
        });
    }
});

function addMedicalCondition() {
    const container = document.getElementById('medicalConditionsContainer');
    const index = container.querySelectorAll('.medical-condition-item').length;
    
    const newItem = document.createElement('div');
    newItem.className = 'flex gap-2 items-start medical-condition-item';
    newItem.innerHTML = `
        <input 
            type="text"
            name="medical_conditions[${index}]"
            placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <button 
            type="button"
            onclick="removeMedicalCondition(this)"
            class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
        >
            Hapus
        </button>
    `;
    
    container.appendChild(newItem);
}

function removeMedicalCondition(button) {
    const container = document.getElementById('medicalConditionsContainer');
    const items = container.querySelectorAll('.medical-condition-item');
    
    if (items.length > 1) {
        button.closest('.medical-condition-item').remove();
    } else {
        button.closest('.medical-condition-item').querySelector('input').value = '';
    }
}
</script>
@endsection

