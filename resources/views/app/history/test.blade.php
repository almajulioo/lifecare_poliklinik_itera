@extends('layouts.app_mobile')

@section('title', 'Test Riwayat')

@section('content')
<div class="min-h-screen bg-white pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">Test Riwayat</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-4">
        <p>Halaman test untuk debugging history page</p>
        <pre>
Data available:
- $stats: {{ gettype($stats) }}
- $logs: {{ gettype($logs) }}
- $medicines: {{ gettype($medicines) }}
- $dailyCompliance: {{ gettype($dailyCompliance) }}
        </pre>
    </div>
</div>

<x-mobile-bottom-nav active="history" />

@endsection
