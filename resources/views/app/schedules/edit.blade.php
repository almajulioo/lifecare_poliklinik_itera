@extends('layouts.app_mobile')

@section('title', 'Edit Jadwal Obat')
@section('header', 'Edit Jadwal')

@section('content')
<div class="space-y-4">

    <!-- Error Messages -->
    @if($errors->any())
        <div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('warning'))
        <div class="p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-sm">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Info: Related Schedules Checklist -->
    @if($relatedSchedules->count() > 0)
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="text-blue-600 text-lg mt-1">ℹ️</div>
                <div class="flex-1">
                    <h3 class="font-semibold text-blue-900 mb-2">
                        Jadwal Lain dari Obat "{{ $schedule->medicine->name }}"
                    </h3>
                    <p class="text-xs text-blue-700 mb-3">
                        Obat ini memiliki {{ $relatedSchedules->count() + 1 }} jadwal total. Perubahan hanya akan diterapkan pada jadwal ini (ID: {{ $schedule->id }}), bukan pada jadwal lain berikut:
                    </p>
                    <div class="space-y-2">
                        @foreach($relatedSchedules as $related)
                            <div class="flex items-center gap-2 text-xs text-blue-800 bg-white bg-opacity-50 p-2 rounded">
                                <span class="inline-block w-4 h-4 bg-blue-200 rounded flex items-center justify-center">→</span>
                                <span>
                                    <strong>ID {{ $related->id }}</strong>
                                    — {{ $related->time }} 
                                    @if($related->frequency)
                                        ({{ $related->frequency }})
                                    @endif
                                    @if($related->start_date && $related->end_date)
                                        <span class="text-blue-600">{{ $related->start_date }} s/d {{ $related->end_date }}</span>
                                    @elseif($related->start_date)
                                        <span class="text-blue-600">mulai {{ $related->start_date }}</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="text-green-600 text-lg">✓</div>
                <div>
                    <h3 class="font-semibold text-green-900">
                        Jadwal Unik
                    </h3>
                    <p class="text-xs text-green-700">
                        Ini adalah satu-satunya jadwal untuk obat "{{ $schedule->medicine->name }}". Perubahan hanya akan diterapkan di sini.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('app.schedules.update', $schedule) }}" class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
        @csrf
        @method('PUT')

        <!-- Obat -->
        <div>
            <label for="medicine_id" class="block text-sm font-semibold text-gray-900 mb-1">
                Obat <span class="text-red-600">*</span>
            </label>
            <select
                id="medicine_id"
                name="medicine_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
            >
                <option value="">-- Pilih --</option>
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

        <!-- Tanggal Mulai -->
        <div>
            <label for="start_date" class="block text-sm font-semibold text-gray-900 mb-1">
                Mulai <span class="text-red-600">*</span>
            </label>
            <input
                type="date"
                id="start_date"
                name="start_date"
                value="{{ old('start_date', optional($schedule->start_date)->format('Y-m-d')) }}"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
            />
            @error('start_date')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Selesai -->
        <div>
            <label for="end_date" class="block text-sm font-semibold text-gray-900 mb-1">
                Selesai (Opsional)
            </label>
            <input
                type="date"
                id="end_date"
                name="end_date"
                value="{{ old('end_date', $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '') }}"
                min="{{ now()->toDateString() }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
            />
            @error('end_date')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jam Minum -->
        <div>
            <label for="time" class="block text-sm font-semibold text-gray-900 mb-1">
                Jam Minum <span class="text-red-600">*</span>
            </label>
            <input
                type="time"
                id="time"
                name="time"
                value="{{ old('time') ?? $schedule->time }}"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
            />
            @error('time')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Frekuensi -->
        <div>
            <label for="frequency" class="block text-sm font-semibold text-gray-900 mb-1">
                Frekuensi
            </label>
            <select
                id="frequency"
                name="frequency"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
            >
                <option value="">-- Tidak ada --</option>
                <option value="1x sehari" {{ (old('frequency') ?? $schedule->frequency) == '1x sehari' ? 'selected' : '' }}>1x sehari</option>
                <option value="2x sehari" {{ (old('frequency') ?? $schedule->frequency) == '2x sehari' ? 'selected' : '' }}>2x sehari</option>
                <option value="3x sehari" {{ (old('frequency') ?? $schedule->frequency) == '3x sehari' ? 'selected' : '' }}>3x sehari</option>
                <option value="4x sehari" {{ (old('frequency') ?? $schedule->frequency) == '4x sehari' ? 'selected' : '' }}>4x sehari</option>
            </select>
            @error('frequency')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Durasi -->
        <div>
            <label for="duration_days" class="block text-sm font-semibold text-gray-900 mb-1">
                Durasi (Hari)
            </label>
            <input
                type="number"
                id="duration_days"
                name="duration_days"
                value="{{ old('duration_days') ?? $schedule->duration_days }}"
                min="1"
                max="365"
                placeholder="Contoh: 7"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
            />
            @error('duration_days')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Status Aktif -->
        <div>
            <label for="is_active" class="flex items-center gap-2 cursor-pointer">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ (old('is_active') ?? $schedule->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 border border-gray-300 rounded text-blue-600 focus:outline-none"
                />
                <span class="text-sm font-medium text-gray-900">Aktifkan</span>
            </label>
        </div>

        <!-- Changes Summary -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mt-4">
            <h4 class="font-semibold text-purple-900 mb-3 text-sm">📋 Ringkasan Perubahan:</h4>
            <div class="space-y-2 text-xs text-purple-800">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 bg-purple-300 rounded-full flex items-center justify-center text-xs text-white">✓</span>
                    <span><strong>Obat:</strong> {{ $schedule->medicine->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 bg-purple-300 rounded-full flex items-center justify-center text-xs text-white">✓</span>
                    <span><strong>Jadwal ID:</strong> {{ $schedule->id }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 bg-purple-300 rounded-full flex items-center justify-center text-xs text-white">✓</span>
                    <span><strong>Jam Minum:</strong> <span id="preview-time">{{ $schedule->time }}</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 bg-purple-300 rounded-full flex items-center justify-center text-xs text-white">✓</span>
                    <span><strong>Mulai:</strong> <span id="preview-start">{{ optional($schedule->start_date)->format('d/m/Y') }}</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 bg-purple-300 rounded-full flex items-center justify-center text-xs text-white">✓</span>
                    <span><strong>Selesai:</strong> <span id="preview-end">{{ $schedule->end_date ? $schedule->end_date->format('d/m/Y') : '(tidak terbatas)' }}</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-4 h-4 bg-purple-300 rounded-full flex items-center justify-center text-xs text-white">✓</span>
                    <span><strong>Status:</strong> <span id="preview-status">{{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}</span></span>
                </div>
            </div>
            <p class="text-xs text-purple-600 mt-3 italic">
                💡 Perubahan hanya akan diterapkan pada jadwal ini. Jadwal lain dari obat yang sama tidak akan berubah.
            </p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2 pt-2 border-t">
            <button
                type="submit"
                class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
            >
                Perbarui
            </button>
            <a
                href="{{ route('app.schedules.upcoming') }}"
                class="flex-1 px-4 py-3 bg-gray-300 text-gray-800 rounded-lg text-sm font-semibold hover:bg-gray-400 transition text-center"
            >
                Batal
            </a>
        </div>
    </form>

    <!-- Hapus Form (Separate dari Update Form) -->
    <form method="POST" action="{{ route('app.schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal?');">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="w-full px-4 py-3 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition"
        >
            Hapus
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update preview saat jam minum berubah
    const timeInput = document.getElementById('time');
    if (timeInput) {
        timeInput.addEventListener('change', function() {
            document.getElementById('preview-time').textContent = this.value || '{{ $schedule->time }}';
        });
    }

    // Update preview saat tanggal mulai berubah
    const startDateInput = document.getElementById('start_date');
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            document.getElementById('preview-start').textContent = this.value || '{{ $schedule->start_date }}';
        });
    }

    // Update preview saat tanggal selesai berubah
    const endDateInput = document.getElementById('end_date');
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            document.getElementById('preview-end').textContent = this.value || '(tidak terbatas)';
        });
    }

    // Update preview saat status aktif berubah
    const isActiveCheckbox = document.getElementById('is_active');
    if (isActiveCheckbox) {
        isActiveCheckbox.addEventListener('change', function() {
            document.getElementById('preview-status').textContent = this.checked ? 'Aktif' : 'Nonaktif';
        });
    }
});
</script>
@endsection
