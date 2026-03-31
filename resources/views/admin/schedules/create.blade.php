@extends('admin.layouts.app')

@section('title', 'Buat Jadwal Obat')

@section('content')
<div class="p-8">
    <div class="max-w-3xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.schedules.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">← Kembali</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Buat Jadwal Obat</h1>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                <p class="font-semibold mb-2">Terjadi kesalahan:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('admin.schedules.store') }}" class="bg-white rounded-lg shadow p-8 space-y-6">
            @csrf

            <!-- Row 1: Pasien & Obat -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-semibold text-gray-900 mb-2">
                        Pilih Pasien <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="user_id"
                        name="user_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    >
                        <option value="">-- Pilih Pasien --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="medicine_id" class="block text-sm font-semibold text-gray-900 mb-2">
                        Pilih Obat <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="medicine_id"
                        name="medicine_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    >
                        <option value="">-- Pilih Obat --</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->name }} ({{ $medicine->dose }} {{ $medicine->unit }})
                            </option>
                        @endforeach
                    </select>
                    @error('medicine_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 2: Tanggal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-gray-900 mb-2">
                        Tanggal Mulai <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date') }}"
                        required
                        onchange="calculateDuration()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    />
                    @error('start_date')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-semibold text-gray-900 mb-2">
                        Tanggal Selesai (Opsional)
                    </label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ old('end_date') }}"
                        onchange="calculateDuration()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    />
                    @error('end_date')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 3: Jam & Frekuensi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Jam Minum Container -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Jam Minum <span class="text-red-600">*</span>
                    </label>
                    <div id="time-inputs-container" class="space-y-2">
                        <div class="time-input-wrapper">
                            <label for="time_1" class="block text-xs text-gray-600 mb-1">Jam ke 1</label>
                            <input
                                type="time"
                                id="time_1"
                                name="times[]"
                                value="{{ old('times.0') ?? old('time') }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label for="frequency" class="block text-sm font-semibold text-gray-900 mb-2">
                        Frekuensi (Opsional)
                    </label>
                    <select
                        id="frequency"
                        name="frequency"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        onchange="updateTimeInputs()"
                    >
                        <option value="">-- Pilih Frekuensi --</option>
                        <option value="1x sehari" {{ old('frequency') == '1x sehari' ? 'selected' : '' }}>1x sehari</option>
                        <option value="2x sehari" {{ old('frequency') == '2x sehari' ? 'selected' : '' }}>2x sehari</option>
                        <option value="3x sehari" {{ old('frequency') == '3x sehari' ? 'selected' : '' }}>3x sehari</option>
                        <option value="4x sehari" {{ old('frequency') == '4x sehari' ? 'selected' : '' }}>4x sehari</option>
                        <option value="setiap 12 jam" {{ old('frequency') == 'setiap 12 jam' ? 'selected' : '' }}>Setiap 12 jam</option>
                        <option value="setiap 8 jam" {{ old('frequency') == 'setiap 8 jam' ? 'selected' : '' }}>Setiap 8 jam</option>
                        <option value="setiap 6 jam" {{ old('frequency') == 'setiap 6 jam' ? 'selected' : '' }}>Setiap 6 jam</option>
                        <option value="setiap 4 jam" {{ old('frequency') == 'setiap 4 jam' ? 'selected' : '' }}>Setiap 4 jam</option>
                        <option value="saat diperlukan" {{ old('frequency') == 'saat diperlukan' ? 'selected' : '' }}>Saat diperlukan</option>
                    </select>
                    @error('frequency')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 4: Durasi & Sumber -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="duration_days" class="block text-sm font-semibold text-gray-900 mb-2">
                        Durasi (Hari, Opsional)
                    </label>
                    <input
                        type="number"
                        id="duration_days"
                        name="duration_days"
                        value="{{ old('duration_days') }}"
                        min="1"
                        max="365"
                        placeholder="Jumlah hari"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    />
                    @error('duration_days')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="source" class="block text-sm font-semibold text-gray-900 mb-2">
                        Sumber <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="source"
                        name="source"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    >
                        <option value="">-- Pilih Sumber --</option>
                        <option value="resep" {{ old('source') == 'resep' ? 'selected' : '' }}>Resep Dokter</option>
                        <option value="mandiri" {{ old('source') == 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                    </select>
                    @error('source')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Checkbox -->
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 rounded"
                />
                <label for="is_active" class="text-sm font-medium text-gray-900">
                    Aktifkan jadwal ini
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition"
                >
                    Simpan Jadwal
                </button>
                <a
                    href="{{ route('admin.schedules.index') }}"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold transition"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function updateTimeInputs() {
    const frequency = document.getElementById('frequency').value;
    const container = document.getElementById('time-inputs-container');
    const oldTimes = Array.from(document.querySelectorAll('#time-inputs-container input[type="time"]')).map(input => input.value);
    
    // Determine number of inputs based on frequency
    let numInputs = 1;
    let labels = {};
    
    switch(frequency) {
        case '1x sehari':
        case 'saat diperlukan':
            numInputs = 1;
            labels = { 1: 'Jam ke 1' };
            break;
        case '2x sehari':
        case 'setiap 12 jam':
            numInputs = 2;
            labels = { 1: 'Jam ke 1 (Pagi)', 2: 'Jam ke 2 (Sore)' };
            break;
        case '3x sehari':
        case 'setiap 8 jam':
            numInputs = 3;
            labels = { 1: 'Jam ke 1 (Pagi)', 2: 'Jam ke 2 (Siang)', 3: 'Jam ke 3 (Malam)' };
            break;
        case '4x sehari':
        case 'setiap 6 jam':
            numInputs = 4;
            labels = { 1: 'Jam ke 1 (Pagi)', 2: 'Jam ke 2 (Siang)', 3: 'Jam ke 3 (Sore)', 4: 'Jam ke 4 (Malam)' };
            break;
        case 'setiap 4 jam':
            numInputs = 6;
            labels = { 1: 'Jam ke 1', 2: 'Jam ke 2', 3: 'Jam ke 3', 4: 'Jam ke 4', 5: 'Jam ke 5', 6: 'Jam ke 6' };
            break;
        default:
            numInputs = 1;
            labels = { 1: 'Jam ke 1' };
    }
    
    // Clear container
    container.innerHTML = '';
    
    // Create new inputs
    for (let i = 1; i <= numInputs; i++) {
        const wrapper = document.createElement('div');
        wrapper.className = 'time-input-wrapper';
        
        const label = document.createElement('label');
        label.htmlFor = `time_${i}`;
        label.className = 'block text-xs text-gray-600 mb-1';
        label.textContent = labels[i] || `Jam ke ${i}`;
        
        const input = document.createElement('input');
        input.type = 'time';
        input.id = `time_${i}`;
        input.name = 'times[]';
        input.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500';
        input.required = true;
        
        // Restore old values if they exist
        if (oldTimes[i - 1]) {
            input.value = oldTimes[i - 1];
        }
        
        wrapper.appendChild(label);
        wrapper.appendChild(input);
        container.appendChild(wrapper);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const frequency = document.getElementById('frequency').value;
    if (frequency) {
        updateTimeInputs();
    }
    
    // Calculate duration on page load if dates are already filled
    calculateDuration();
});

function calculateDuration() {
    const startDateInput = document.getElementById('start_date').value;
    const endDateInput = document.getElementById('end_date').value;
    const durationField = document.getElementById('duration_days');
    
    if (startDateInput && endDateInput) {
        // Convert date strings to Date objects
        const startDate = new Date(startDateInput);
        const endDate = new Date(endDateInput);
        
        // Calculate difference in milliseconds
        const timeDiff = endDate - startDate;
        
        // Convert to days (1 day = 24 * 60 * 60 * 1000 milliseconds)
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 to include both start and end day
        
        // Only set if positive
        if (daysDiff > 0) {
            durationField.value = daysDiff;
        }
    } else if (startDateInput && !endDateInput) {
        // Clear duration if only start date is selected
        durationField.value = '';
    }
}
</script>

@endsection