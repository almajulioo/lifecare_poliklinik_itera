@extends('layouts.app_mobile')

@section('title', 'Riwayat Minum Obat')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Obat</h1>
            <p class="text-sm text-slate-600 mt-1">Lihat riwayat minum obat dan kepatuhan Anda</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-4">
        <!-- Statistics Summary -->
        <div id="stats-summary" class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <div class="text-sm text-blue-600 font-medium">Kepatuhan</div>
                <div class="text-2xl font-bold text-blue-900 mt-1" id="compliance-percent">{{ $stats['overall_compliance'] }}%</div>
                <div class="text-xs text-blue-700 mt-1">{{ $stats['total_days'] }} hari</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <div class="text-sm text-green-600 font-medium">Hari Sempurna</div>
                <div class="text-2xl font-bold text-green-900 mt-1">{{ $stats['perfect_days'] }}</div>
                <div class="text-xs text-green-700 mt-1">Semua tepat waktu</div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                <div class="text-sm text-orange-600 font-medium">Terlewat</div>
                <div class="text-2xl font-bold text-orange-900 mt-1">{{ $stats['zero_days'] }}</div>
                <div class="text-xs text-orange-700 mt-1">Tidak ada yang diminum</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 mb-6">
            <form id="filter-form" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="from_date" class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                        <input 
                            type="date" 
                            id="from_date" 
                            name="from_date" 
                            value="{{ $fromDate }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label for="to_date" class="block text-xs font-semibold text-slate-700 mb-1">Hingga Tanggal</label>
                        <input 
                            type="date" 
                            id="to_date" 
                            name="to_date" 
                            value="{{ $toDate }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                @if ($medicines->count() > 0)
                <div>
                    <label for="medicine_id" class="block text-xs font-semibold text-slate-700 mb-1">Filter Obat</label>
                    <select 
                        id="medicine_id" 
                        name="medicine_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Semua Obat</option>
                        @foreach ($medicines as $medicine)
                        <option value="{{ $medicine->id }}" {{ $selectedMedicineId == $medicine->id ? 'selected' : '' }}>
                            {{ $medicine->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition"
                    >
                        Cari
                    </button>
                    <button 
                        type="button" 
                        id="export-btn"
                        class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-300 transition"
                    >
                        <span class="inline-block mr-1">📥</span> Unduh CSV
                    </button>
                </div>
            </form>
        </div>

        <!-- Daily Compliance Chart -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 mb-6">
            <h3 class="font-semibold text-slate-900 mb-3">Tren Kepatuhan</h3>
            <div id="compliance-chart" style="height: 200px; display: flex; align-items: flex-end; gap: 2px;">
                <!-- Populated by JavaScript -->
            </div>
        </div>

        <!-- History List -->
        <div class="space-y-2 mb-6">
            <h3 class="font-semibold text-slate-900 px-0">Riwayat Detail</h3>
            
            @if ($logs->count() > 0)
                @foreach ($logs as $log)
                <div class="bg-white rounded-lg border border-slate-200 p-4 hover:border-slate-300 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-900">
                                {{ $log->medicationSchedule?->medicine->name ?? 'Obat Tidak Diketahui' }}
                            </h4>
                            <p class="text-xs text-slate-600 mt-1">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </p>
                            @if ($log->note)
                            <p class="text-xs text-slate-700 mt-2 bg-slate-50 px-2 py-1 rounded">
                                {{ $log->note }}
                            </p>
                            @endif
                        </div>
                        <div class="ml-3">
                            @if ($log->status === 'taken')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Diminum
                            </span>
                            @elseif ($log->status === 'skipped')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                ⊘ Terlewat
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                ◌ {{ ucfirst($log->status) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    @if ($log->offline_synced)
                    <div class="mt-2 flex items-center text-xs text-slate-500">
                        <span class="inline-block">🔄 Sinkronisasi offline</span>
                    </div>
                    @endif
                </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $logs->links('pagination::tailwind') }}
                </div>
            @else
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-8 text-center">
                    <p class="text-slate-600">Tidak ada riwayat dalam periode ini</p>
                </div>
            @endif
        </div>
    </div>
</div>

<x-mobile-bottom-nav active="history" />

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Export CSV
    document.getElementById('export-btn').addEventListener('click', function () {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        
        const params = new URLSearchParams();
        if (fromDate) params.append('from_date', fromDate);
        if (toDate) params.append('to_date', toDate);

        window.location.href = `/app/history/export?${params.toString()}`;
    });

    // Draw compliance chart
    const dailyCompliance = @json($dailyCompliance);
    const maxCount = Math.max(...dailyCompliance.map(d => d.count), 1);
    const chartContainer = document.getElementById('compliance-chart');

    dailyCompliance.forEach(day => {
        const percentage = (day.count / maxCount) * 100;
        const bar = document.createElement('div');
        bar.className = 'flex-1 bg-gradient-to-t from-blue-500 to-blue-400 rounded-t hover:from-blue-600 hover:to-blue-500 transition cursor-pointer relative group';
        bar.style.minHeight = percentage + '%';
        bar.style.minHeight = Math.max(5, percentage) + 'px';
        
        // Tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute -top-8 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap';
        tooltip.textContent = day.date + ': ' + day.count + ' obat';
        bar.appendChild(tooltip);
        
        chartContainer.appendChild(bar);
    });

    // Filter form
    document.getElementById('filter-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = `/app/history?${params.toString()}`;
    });
});
</script>

@endsection
