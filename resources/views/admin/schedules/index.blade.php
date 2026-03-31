@extends('admin.layouts.app')

@section('title', 'Jadwal Obat')

@section('content')
<div class="p-8">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
            <strong>✓ Sukses!</strong> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
            <strong>✗ Error!</strong> {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Jadwal Obat Pasien</h1>
        <a href="{{ route('admin.schedules.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
            + Buat Jadwal Baru
        </a>
    </div>

    <!-- Statistics Bar -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Total Jadwal</p>
            <p class="text-2xl font-bold text-blue-600">{{ $schedules->total() }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Jadwal Aktif</p>
            <p class="text-2xl font-bold text-green-600">
                @php
                    $activeCount = $schedules->getCollection()->filter(fn($s) => $s->is_active)->count();
                @endphp
                {{ $activeCount }}
            </p>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Jadwal Nonaktif</p>
            <p class="text-2xl font-bold text-orange-600">
                @php
                    $inactiveCount = $schedules->getCollection()->filter(fn($s) => !$s->is_active)->count();
                @endphp
                {{ $inactiveCount }}
            </p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($schedules->count() > 0)
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">No</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Pasien</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Obat</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Jam</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Periode</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Sumber</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $index => $schedule)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="px-6 py-3 text-gray-900">{{ ($schedules->currentPage() - 1) * 10 + $loop->iteration }}</td>
                            <td class="px-6 py-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $schedule->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">ID: {{ $schedule->user_id }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <p class="font-medium text-gray-900">{{ $schedule->medicine->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $schedule->medicine->dose ?? 0 }} {{ $schedule->medicine->unit ?? '' }}</p>
                            </td>
                            <td class="px-6 py-3 text-gray-900">
                                @php
                                    // Check if time is JSON (multiple times)
                                    $times = $schedule->time;
                                    if (strpos($times, '[') === 0) {
                                        // It's JSON
                                        $timeArray = json_decode($times, true);
                                        $timeDisplay = implode(', ', array_map(fn($t) => substr($t, 0, 5), $timeArray));
                                    } else {
                                        // It's a single time
                                        $timeDisplay = \Carbon\Carbon::parse($times)->format('H:i');
                                    }
                                @endphp
                                {{ $timeDisplay ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                <span class="text-xs">{{ $schedule->start_date ?? 'N/A' }}</span><br>
                                <span class="text-xs text-gray-500">hingga</span><br>
                                <span class="text-xs">{{ $schedule->end_date ?? 'Ongoing' }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $schedule->source === 'resep' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($schedule->source) ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $schedule->is_active ? '✓ Aktif' : '✗ Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-white border-t border-gray-200">
                {{ $schedules->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-gray-500 text-lg">Belum ada jadwal obat.</p>
                <a href="{{ route('admin.schedules.create') }}" class="text-blue-600 hover:text-blue-800 font-semibold mt-2 inline-block">
                    Buat jadwal sekarang →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection