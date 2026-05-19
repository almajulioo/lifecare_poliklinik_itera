@extends('layouts.app_mobile')

@section('title', 'Riwayat Minum Obat')

@section('content')
<div class="min-h-screen bg-white pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">Riwayat Minum Obat</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-4">
        <!-- Quick Stats -->
        <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="bg-blue-50 p-3 rounded border border-blue-200">
                <p class="text-xs text-blue-600 font-semibold">Kepatuhan</p>
                <p class="text-lg font-bold text-blue-900">{{ $stats['overall_compliance'] }}%</p>
            </div>
            <div class="bg-green-50 p-3 rounded border border-green-200">
                <p class="text-xs text-green-600 font-semibold">Sempurna</p>
                <p class="text-lg font-bold text-green-900">{{ $stats['perfect_days'] }}</p>
            </div>
            <div class="bg-orange-50 p-3 rounded border border-orange-200">
                <p class="text-xs text-orange-600 font-semibold">Terlewat</p>
                <p class="text-lg font-bold text-orange-900">{{ $stats['zero_days'] }}</p>
            </div>
        </div>


        <!-- History List -->
        <div class="mb-6">
            <p class="font-semibold text-sm text-gray-900 mb-3">Riwayat</p>
            @if ($logs && $logs->count() > 0)
                @foreach ($logs as $log)
                <div class="border-l-4 border-blue-400 bg-white p-3 rounded mb-2 flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 text-sm">
                            {{ $log->medicationSchedule?->medicine->name ?? 'Obat Tidak Diketahui' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </p>
                        @if ($log->note)
                        <p class="text-xs text-gray-600 mt-1 italic">{{ $log->note }}</p>
                        @endif
                    </div>
                    <div class="ml-2">
                        @if ($log->status == 'taken')
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">✓</span>
                        @elseif ($log->status == 'skipped')
                        <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded">✗</span>
                        @else
                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded">-</span>
                        @endif
                    </div>
                </div>
                @endforeach

                @if (method_exists($logs, 'hasPages') && $logs->hasPages())
                <div class="mt-4">
                    {{ $logs->links('pagination::simple-tailwind') }}
                </div>
                @endif
            @else
                <div class="text-center py-8 text-gray-500">
                    Tidak ada riwayat dalam periode ini
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('filter-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(this));
    window.location.href = `/app/history?${params.toString()}`;
});

document.getElementById('export-btn').addEventListener('click', function () {
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    const params = new URLSearchParams();
    if (fromDate) params.append('from_date', fromDate);
    if (toDate) params.append('to_date', toDate);
    window.location.href = `/app/history/export?${params.toString()}`;
});

// Draw simple compliance chart
const data = @json($dailyCompliance) || [];
const container = document.getElementById('compliance-chart');
if (data && Array.isArray(data) && data.length > 0) {
    const validData = data.filter(d => d && typeof d.count === 'number');
    if (validData.length > 0) {
        const max = Math.max(...validData.map(d => d.count), 1);
        validData.forEach(day => {
            const percentage = (day.count / max) * 100;
            const bar = document.createElement('div');
            bar.style.flex = '1';
            bar.style.height = percentage + '%';
            bar.style.minHeight = '3px';
            bar.style.backgroundColor = percentage > 75 ? '#10b981' : percentage > 50 ? '#3b82f6' : '#ef4444';
            bar.style.borderRadius = '2px';
            bar.title = (day.date || 'Unknown') + ': ' + day.count + ' obat';
            container.appendChild(bar);
        });
    }
}
</script>
@endsection

@section('bottom-nav')
<div class="flex justify-around items-center h-20 px-2">
    <a href="{{ route('app.dashboard') }}" class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full text-gray-500 hover:text-black transition-colors">
        <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        <span class="text-xs font-medium">Dashboard</span>
    </a>
    <a href="{{ route('app.medications.index') }}" class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full text-gray-500 hover:text-black transition-colors">
        <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        <span class="text-xs font-medium">Obat</span>
    </a>
    <a href="{{ route('app.history.index') }}" class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full text-black font-semibold">
        <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V6h14v14z"/></svg>
        <span class="text-xs font-medium">Riwayat</span>
    </a>
    <a href="{{ route('app.profile.show') }}" class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full text-gray-500 hover:text-black transition-colors">
        <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <span class="text-xs font-medium">Profil</span>
    </a>
</div>
@endsection
