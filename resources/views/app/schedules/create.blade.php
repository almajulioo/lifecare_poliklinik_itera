@extends('layouts.app_mobile')

@section('title', 'Buat Jadwal Obat Baru')
@section('header', 'Jadwal Baru')

@section('content')
<div class="space-y-4 pb-6">

    <!-- Session Error Message -->
    @if(session('error'))
        <div class="p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
            <div class="flex items-start gap-2">
                <div class="text-red-600 text-lg">✕</div>
                <div>
                    <p class="font-semibold text-red-800 text-sm">Terjadi Kesalahan</p>
                    <p class="text-red-700 text-sm mt-1">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Error Summary -->
    @if($errors->any())
        <div class="p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
            <div class="flex items-start gap-2">
                <div class="text-red-600 text-lg">⚠</div>
                <div>
                    <p class="font-semibold text-red-800 text-sm">Ada {{ $errors->count() }} kesalahan yang perlu diperbaiki:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-red-700 text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if($medicines->isEmpty())
        <div class="p-4 bg-blue-50 border-l-4 border-blue-600 rounded-lg">
            <div class="flex items-start gap-2">
                <div class="text-blue-600 text-lg">ℹ</div>
                <div>
                    <p class="font-semibold text-blue-800 text-sm">Belum Ada Obat</p>
                    <p class="text-blue-700 text-sm mt-1">Anda perlu menambahkan obat terlebih dahulu sebelum membuat jadwal minum obat.</p>
                    <a href="{{ route('app.medicines.create') }}" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        + Tambah Obat
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Form -->
        <form method="POST" action="{{ route('app.schedules.store') }}" class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
            @csrf
            <input type="hidden" name="source" value="mandiri">

            <!-- Obat -->
            <div>
                <label for="medicine_id" class="block text-sm font-semibold text-gray-900 mb-2">
                    Pilih Obat <span class="text-red-600">*</span>
                </label>
                <select
                    id="medicine_id"
                    name="medicine_id"
                    required
                    class="w-full px-4 py-2 border {{ $errors->has('medicine_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                >
                    <option value="">-- Pilih Obat --</option>
                    @foreach($medicines as $medicine)
                        <option value="{{ $medicine->id }}" {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
                            {{ $medicine->name }} ({{ $medicine->dose }} {{ $medicine->unit }})
                        </option>
                    @endforeach
                </select>
                @error('medicine_id')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Row: Tanggal Mulai & Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-gray-900 mb-2">
                        Tanggal Mulai <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date') ?? now()->toDateString() }}"
                        required
                        onchange="calculateDuration()"
                        class="w-full px-4 py-2 border {{ $errors->has('start_date') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                    />
                    @error('start_date')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
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
                        class="w-full px-4 py-2 border {{ $errors->has('end_date') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                    />
                    @error('end_date')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row: Jam Minum & Frekuensi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                                value="{{ old('times.0') }}"
                                required
                                class="w-full px-4 py-2 border {{ $errors->has('times') || $errors->has('times.0') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                            />
                        </div>
                    </div>
                    @error('times')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="frequency" class="block text-sm font-semibold text-gray-900 mb-2">
                        Frekuensi (Opsional)
                    </label>
                    <select
                        id="frequency"
                        name="frequency"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
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
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Durasi -->
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
                    class="w-full px-4 py-2 border {{ $errors->has('duration_days') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                />
                @error('duration_days')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Checkbox -->
            <div class="flex items-center gap-3 py-2">
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
            <div class="flex gap-3 pt-3 border-t border-gray-200">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
                >
                    Simpan Jadwal
                </button>
                <a
                    href="{{ route('app.schedules.index') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition text-center"
                >
                    Batal
                </a>
            </div>
        </form>
    @endif

</div>

<script>
function updateTimeInputs() {
    try {
        const frequency = document.getElementById('frequency').value;
        const container = document.getElementById('time-inputs-container');
        
        if (!container) {
            console.error('Time inputs container not found');
            return;
        }
        
        // Store old time values before clearing
        const oldTimes = Array.from(
            container.querySelectorAll('input[type="time"]')
        ).map(input => input.value).filter(val => val);
        
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
            input.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500 transition';
            input.required = true;
            
            // Restore old values if they exist
            if (oldTimes && oldTimes.length > 0 && oldTimes[i - 1]) {
                input.value = oldTimes[i - 1];
            }
            
            wrapper.appendChild(label);
            wrapper.appendChild(input);
            container.appendChild(wrapper);
        }
    } catch (error) {
        console.error('Error in updateTimeInputs:', error);
    }
}

function calculateDuration() {
    try {
        const startField = document.getElementById('start_date');
        const endField = document.getElementById('end_date');
        const durationField = document.getElementById('duration_days');
        
        const startValue = startField.value;
        const endValue = endField.value;
        
        if (startValue && endValue) {
            // Convert date strings to Date objects
            const startDate = new Date(startValue);
            const endDate = new Date(endValue);
            
            // Calculate difference in milliseconds
            const timeDiff = endDate - startDate;
            
            // Convert to days (1 day = 24 * 60 * 60 * 1000 milliseconds)
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 to include both start and end day
            
            // Only set if positive
            if (daysDiff > 0) {
                durationField.value = daysDiff;
            }
        } else if (startValue && !endValue) {
            // Clear duration if only start date is selected
            durationField.value = '';
        }
    } catch (error) {
        console.error('Error calculating duration:', error);
    }
}
</script>
@endsection
