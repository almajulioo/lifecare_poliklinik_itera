@extends('layouts.app_mobile')

@section('title', 'Buat Jadwal Obat Baru')
@section('header', 'Jadwal Baru')

@section('content')
<div class="space-y-4">

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

    @if(session('warning'))
        <div class="p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-sm">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('app.schedules.store') }}" class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
        @csrf
        <input type="hidden" name="source" value="mandiri">

        <!-- Obat -->
        <div>
            <label for="medicine_id" class="block text-sm font-semibold text-gray-900 mb-1">
                Obat <span class="text-red-600">*</span>
            </label>
            <select
                id="medicine_id"
                name="medicine_id"
                required
                class="w-full px-3 py-2 border {{ $errors->has('medicine_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
            >
                <option value="">-- Pilih --</option>
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}" {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
                        {{ $medicine->name }} ({{ $medicine->dose }} {{ $medicine->unit }})
                    </option>
                @endforeach
            </select>
            @error('medicine_id')
                <p class="text-red-600 text-xs mt-2 flex items-center gap-1">
                    <span>●</span> {{ $message }}
                </p>
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
                value="{{ old('start_date') ?? now()->toDateString() }}"
                required
                class="w-full px-3 py-2 border {{ $errors->has('start_date') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
            />
            @error('start_date')
                <p class="text-red-600 text-xs mt-2 flex items-center gap-1">
                    <span>●</span> {{ $message }}
                </p>
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
                value="{{ old('end_date') }}"
                class="w-full px-3 py-2 border {{ $errors->has('end_date') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
            />
            @error('end_date')
                <p class="text-red-600 text-xs mt-2 flex items-center gap-1">
                    <span>●</span> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Jam Minum -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-1">
                Jam Minum <span class="text-red-600">*</span>
            </label>
            <div id="time-inputs-container" class="space-y-2">
                <div class="time-input-wrapper flex gap-2 items-end">
                    <div class="flex-1">
                        <input
                            type="time"
                            name="times[]"
                            value="{{ old('times.0') }}"
                            required
                            class="w-full px-3 py-2 border {{ $errors->has('times') || $errors->has('times.0') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
                        />
                    </div>
                    <button type="button" onclick="addTimeInput()" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                        +
                    </button>
                </div>
            </div>
            @error('times')
                <p class="text-red-600 text-xs mt-2 flex items-center gap-1">
                    <span>●</span> {{ $message }}
                </p>
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
                class="w-full px-3 py-2 border {{ $errors->has('frequency') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
            >
                <option value="">-- Tidak ada --</option>
                <option value="1x sehari" {{ old('frequency') == '1x sehari' ? 'selected' : '' }}>1x sehari</option>
                <option value="2x sehari" {{ old('frequency') == '2x sehari' ? 'selected' : '' }}>2x sehari</option>
                <option value="3x sehari" {{ old('frequency') == '3x sehari' ? 'selected' : '' }}>3x sehari</option>
                <option value="4x sehari" {{ old('frequency') == '4x sehari' ? 'selected' : '' }}>4x sehari</option>
            </select>
            @error('frequency')
                <p class="text-red-600 text-xs mt-2 flex items-center gap-1">
                    <span>●</span> {{ $message }}
                </p>
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
                value="{{ old('duration_days') }}"
                min="1"
                max="365"
                placeholder="Contoh: 7"
                class="w-full px-3 py-2 border {{ $errors->has('duration_days') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-blue-500 transition"
            />
            @error('duration_days')
                <p class="text-red-600 text-xs mt-2 flex items-center gap-1">
                    <span>●</span> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="flex gap-2 pt-2">
            <button
                type="submit"
                class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
            >
                Simpan
            </button>
            <a
                href="{{ route('app.schedules.index') }}"
                class="flex-1 px-4 py-3 bg-gray-300 text-gray-800 rounded-lg text-sm font-semibold hover:bg-gray-400 transition text-center"
            >
                Batal
            </a>
        </div>
    </form>
</div>

<script>
let timeInputCount = 1;

function addTimeInput() {
    timeInputCount++;
    const container = document.getElementById('time-inputs-container');
    
    const wrapper = document.createElement('div');
    wrapper.className = 'time-input-wrapper flex gap-2 items-end';
    
    const input = document.createElement('input');
    input.type = 'time';
    input.name = 'times[]';
    input.className = 'flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500';
    
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium';
    removeBtn.textContent = 'Hapus';
    removeBtn.onclick = function() {
        wrapper.remove();
    };
    
    wrapper.appendChild(input);
    wrapper.appendChild(removeBtn);
    container.appendChild(wrapper);
}
</script>
@endsection
