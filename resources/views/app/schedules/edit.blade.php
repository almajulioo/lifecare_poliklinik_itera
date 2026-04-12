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
                    <option value="{{ $medicine->id }}" {{ ($old('medicine_id') ?? $schedule->medicine_id) == $medicine->id ? 'selected' : '' }}>
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
                value="{{ old('start_date') ?? $schedule->start_date }}"
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
                value="{{ old('end_date') ?? $schedule->end_date }}"
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

        <!-- Buttons -->
        <div class="flex gap-2 pt-2 border-t">
            <button
                type="submit"
                class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
            >
                Perbarui
            </button>
            <a
                href="{{ route('app.schedules.index') }}"
                class="flex-1 px-4 py-3 bg-gray-300 text-gray-800 rounded-lg text-sm font-semibold hover:bg-gray-400 transition text-center"
            >
                Batal
            </a>
            <form method="POST" action="{{ route('app.schedules.destroy', $schedule) }}" style="flex: 1;" onsubmit="return confirm('Hapus jadwal?');">
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
    </form>
</div>
@endsection
