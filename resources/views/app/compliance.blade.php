@extends('layouts.app_mobile')

@section('title', 'Statistik Kepatuhan')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <h1 class="text-2xl font-bold text-slate-900">Statistik Kepatuhan</h1>
            <p class="text-sm text-slate-600 mt-1">Analytics untuk kesehatan Anda</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-4">
        <!-- Period Summary Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6">
            <!-- Today -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-5 border border-indigo-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm text-indigo-600 font-semibold uppercase tracking-wide">Hari Ini</div>
                        <div class="mt-3 text-4xl font-bold text-indigo-900" id="today-compliance">-</div>
                        <div class="text-xs text-indigo-700 mt-2">
                            <span id="today-count">-</span> / <span id="today-total">-</span> obat
                        </div>
                    </div>
                    <div class="text-4xl">📅</div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-5 border border-purple-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm text-purple-600 font-semibold uppercase tracking-wide">Minggu Ini</div>
                        <div class="mt-3 text-4xl font-bold text-purple-900" id="week-compliance">-</div>
                        <div class="text-xs text-purple-700 mt-2">
                            <span id="week-perfect">-</span> hari sempurna dari 7
                        </div>
                    </div>
                    <div class="text-4xl">📊</div>
                </div>
            </div>

            <!-- This Month -->
            <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg p-5 border border-pink-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm text-pink-600 font-semibold uppercase tracking-wide">Bulan Ini</div>
                        <div class="mt-3 text-4xl font-bold text-pink-900" id="month-compliance">-</div>
                        <div class="text-xs text-pink-700 mt-2">
                            <span id="month-count">-</span> / <span id="month-total">-</span> obat
                        </div>
                    </div>
                    <div class="text-4xl">📈</div>
                </div>
            </div>
        </div>

        <!-- 12-Week Trend Chart -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 mb-6">
            <h3 class="font-semibold text-slate-900 mb-3">Tren Kepatuhan 12 Minggu</h3>
            <div id="weekly-chart" style="height: 250px; display: flex; align-items: flex-end; gap: 3px; padding: 10px 0;">
                <!-- Populated by JavaScript -->
            </div>
            <p class="text-xs text-slate-500 mt-3 text-center">Rata-rata kepatuhan per minggu</p>
        </div>

        <!-- Insights -->
        <div class="space-y-3 mb-6">
            <h3 class="font-semibold text-slate-900">Insights</h3>
            
            <div id="insights-container">
                <!-- Populated by JavaScript -->
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-3">
            <a href="/app/history" class="block px-4 py-3 bg-blue-600 text-white rounded-lg font-medium text-center hover:bg-blue-700 transition">
                <span class="block text-lg">📅</span>
                <span class="text-sm">Lihat Riwayat</span>
            </a>
            <button onclick="exportReport()" class="px-4 py-3 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition">
                <span class="block text-lg">📥</span>
                <span class="text-sm">Unduh Laporan</span>
            </button>
        </div>
    </div>
</div>

<x-mobile-bottom-nav active="profile" />

<script>
let weeklyStats = [];

async function loadStatistics() {
    try {
        // Load summary stats
        const summaryResponse = await fetch('/app/statistics/summary');
        const summaryData = await summaryResponse.json();

        if (summaryData.success) {
            // Today
            document.getElementById('today-compliance').textContent = summaryData.today.compliance + '%';
            document.getElementById('today-count').textContent = summaryData.today.taken;
            document.getElementById('today-total').textContent = summaryData.today.expected;

            // This Week
            document.getElementById('week-compliance').textContent = summaryData.week.compliance + '%';
            document.getElementById('week-perfect').textContent = summaryData.week.perfect_days;

            // This Month
            document.getElementById('month-compliance').textContent = summaryData.month.compliance + '%';
            document.getElementById('month-count').textContent = summaryData.month.taken;
            document.getElementById('month-total').textContent = summaryData.month.expected;

            generateInsights(summaryData);
        }

        // Load weekly trend
        const trendResponse = await fetch('/app/statistics/weekly-stats');
        const trendData = await trendResponse.json();

        if (trendData.success) {
            weeklyStats = trendData.stats;
            drawWeeklyChart(weeklyStats);
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

function drawWeeklyChart(stats) {
    const chartContainer = document.getElementById('weekly-chart');
    chartContainer.innerHTML = '';

    const maxCompliance = Math.max(...stats.map(s => s.compliance), 1);

    stats.forEach((stat, index) => {
        const percentage = (stat.compliance / 100) * 100; // Already 0-100

        const bar = document.createElement('div');
        bar.className = 'flex-1 flex flex-col items-center relative group cursor-pointer';

        // Bar
        const barEl = document.createElement('div');
        barEl.className = 'w-full rounded-t transition-all';
        
        // Color based on compliance
        if (stat.compliance >= 90) {
            barEl.className += ' bg-gradient-to-t from-green-500 to-green-400 hover:from-green-600 hover:to-green-500';
        } else if (stat.compliance >= 70) {
            barEl.className += ' bg-gradient-to-t from-blue-500 to-blue-400 hover:from-blue-600 hover:to-blue-500';
        } else if (stat.compliance >= 50) {
            barEl.className += ' bg-gradient-to-t from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500';
        } else {
            barEl.className += ' bg-gradient-to-t from-orange-500 to-orange-400 hover:from-orange-600 hover:to-orange-500';
        }

        barEl.style.height = Math.max(10, percentage * 1.5) + 'px';

        // Tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute -top-10 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none';
        tooltip.textContent = stat.week + ': ' + stat.compliance + '% (' + stat.taken + '/' + stat.expected + ')';

        bar.appendChild(barEl);
        bar.appendChild(tooltip);

        chartContainer.appendChild(bar);
    });
}

function generateInsights(data) {
    const container = document.getElementById('insights-container');
    const insights = [];

    // Daily insight
    if (data.today.compliance === 100) {
        insights.push({
            icon: '⭐',
            text: 'Sempurna! Anda telah minum semua obat hari ini',
            color: 'bg-green-50 border-green-200 text-green-700'
        });
    } else if (data.today.compliance >= 50) {
        insights.push({
            icon: '✓',
            text: `Anda telah minum ${data.today.taken} dari ${data.today.expected} obat hari ini`,
            color: 'bg-blue-50 border-blue-200 text-blue-700'
        });
    } else if (data.today.expected > 0) {
        insights.push({
            icon: '⚠️',
            text: `Masih ada ${data.today.expected - data.today.taken} obat yang perlu diminum hari ini`,
            color: 'bg-orange-50 border-orange-200 text-orange-700'
        });
    }

    // Weekly insight
    if (data.week.compliance === 100) {
        insights.push({
            icon: '🎉',
            text: 'Luar biasa! Kepatuhan minggu ini mencapai 100%',
            color: 'bg-purple-50 border-purple-200 text-purple-700'
        });
    } else if (data.week.compliance >= 80) {
        insights.push({
            icon: '👍',
            text: `Kepatuhan minggu ini ${data.week.compliance}% dengan ${data.week.perfect_days} hari sempurna`,
            color: 'bg-blue-50 border-blue-200 text-blue-700'
        });
    }

    // Monthly insight
    if (data.month.compliance < 50) {
        insights.push({
            icon: '💡',
            text: 'Cobalah menggunakan pengingat untuk meningkatkan kepatuhan Anda',
            color: 'bg-yellow-50 border-yellow-200 text-yellow-700'
        });
    }

    if (insights.length === 0) {
        insights.push({
            icon: '📊',
            text: 'Terus pantau kepatuhan Anda dengan membuka notifikasi pengingat obat',
            color: 'bg-slate-50 border-slate-200 text-slate-700'
        });
    }

    container.innerHTML = insights.map(insight => `
        <div class="border rounded-lg p-4 ${insight.color}">
            <div class="flex items-start gap-3">
                <span class="text-lg">${insight.icon}</span>
                <p class="text-sm">${insight.text}</p>
            </div>
        </div>
    `).join('');
}

function exportReport() {
    const today = new Date().toISOString().split('T')[0];
    const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    window.location.href = `/app/history/export?from_date=${thirtyDaysAgo}&to_date=${today}`;
}

// Load stats on page load
document.addEventListener('DOMContentLoaded', loadStatistics);
</script>

@endsection
