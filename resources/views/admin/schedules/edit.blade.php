@extends('admin.layouts.app')

@section('title', 'Edit Jadwal Pengingat Obat')

@section('content')
<div class="p-8">
    <div class="max-w-3xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.schedules.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">← Kembali</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Edit Jadwal Pengingat Obat</h1>
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
        <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="bg-white rounded-lg shadow p-8 space-y-6">
            @csrf
            @method('PUT')

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
                            <option value="{{ $user->id }}" {{ old('user_id', $schedule->user_id) == $user->id ? 'selected' : '' }}>
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
                            <option value="{{ $medicine->id }}" {{ old('medicine_id', $schedule->medicine_id) == $medicine->id ? 'selected' : '' }}>
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
                        value="{{ old('start_date', optional($schedule->start_date)->format('Y-m-d')) }}"
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
                        value="{{ old('end_date', optional($schedule->end_date)->format('Y-m-d')) }}"
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
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">
                        Jam Minum <span class="text-red-600">*</span>
                    </label>
                    <div id="time-inputs-container" class="space-y-2">
                        <div class="time-input-wrapper">
                            <label for="time" class="block text-xs text-gray-600 mb-1">Jam</label>
                            <input
                                type="time"
                                id="time"
                                name="time"
                                value="{{ old('time', substr($schedule->time,0,5)) }}"
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
                    >
                        <option value="">-- Pilih Frekuensi --</option>
                        @foreach(['1x sehari','2x sehari','3x sehari','4x sehari','setiap 12 jam','setiap 8 jam','setiap 6 jam','setiap 4 jam','saat diperlukan'] as $f)
                            <option value="{{ $f }}" {{ old('frequency', $schedule->frequency) == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
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
                        value="{{ old('duration_days', $schedule->duration_days) }}"
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
                        <option value="resep" {{ old('source', $schedule->source) == 'resep' ? 'selected' : '' }}>Resep Dokter</option>
                        <option value="mandiri" {{ old('source', $schedule->source) == 'mandiri' ? 'selected' : '' }}>Mandiri</option>
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
                    {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}
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
                    Simpan Perubahan
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
document.addEventListener('DOMContentLoaded', function() {
    try {
        calculateDuration();
    } catch(e) {
        console.error(e);
    }
});

function calculateDuration() {
    try {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const durationField = document.getElementById('duration_days');
        if (!startDateInput || !endDateInput || !durationField) return;
        const startValue = startDateInput.value;
        const endValue = endDateInput.value;
        if (startValue && endValue) {
            const startDate = new Date(startValue);
            const endDate = new Date(endValue);
            const timeDiff = endDate - startDate;
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
            if (daysDiff > 0) durationField.value = daysDiff;
        }
    } catch(e) {
        console.error(e);
    }
}
</script>

@endsection
