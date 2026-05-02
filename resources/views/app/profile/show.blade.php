@extends('layouts.app_mobile')

@section('title', 'Profil Saya')

@section('content')

<div class="pb-28 bg-gradient-to-b from-slate-50 to-white min-h-screen">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <h1 class="text-2xl font-bold text-slate-900">Profil Saya</h1>
            <p class="text-sm text-slate-600 mt-1">Kelola informasi dan pengaturan Anda</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-4">
        {{-- User Profile Card --}}
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200 mb-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                    <span class="text-white text-2xl">👤</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-blue-600 mt-1">{{ auth()->user()->email }}</p>
            </div>
        </div>

        {{-- Profile Details --}}
        <div class="bg-white rounded-lg border border-slate-200 p-4 mb-6 space-y-4">
            <div class="border-b pb-3">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Role</p>
                <p class="text-sm font-medium text-slate-900 mt-1">{{ auth()->user()->role_user }}</p>
            </div>
            
            @if(auth()->user()->nim)
            <div class="border-b pb-3">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">NIM</p>
                <p class="text-sm font-medium text-slate-900 mt-1">{{ auth()->user()->nim }}</p>
            </div>
            @endif

            @if(auth()->user()->prodi)
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Program Studi</p>
                <p class="text-sm font-medium text-slate-900 mt-1">{{ auth()->user()->prodi }}</p>
            </div>
            @endif
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 gap-3 mb-6">
            <a href="{{ route('app.compliance.show') }}" class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200 text-center hover:border-purple-300 transition">
                <div class="text-2xl mb-1">📊</div>
                <p class="text-xs text-purple-700 font-semibold">Statistik Kepatuhan</p>
            </a>
            <a href="{{ route('app.history.index') }}" class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200 text-center hover:border-indigo-300 transition">
                <div class="text-2xl mb-1">📅</div>
                <p class="text-xs text-indigo-700 font-semibold">Riwayat Minum</p>
            </a>
        </div>

        {{-- Action Buttons --}}
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-slate-900">Pengaturan</h3>
            
            <a href="{{ route('app.settings') }}" class="block w-full bg-white rounded-lg p-4 border border-slate-200 text-center font-medium text-slate-900 hover:bg-slate-50 transition flex items-center justify-center gap-2">
                <span>⚙️</span>
                <span>Pengaturan Notifikasi</span>
            </a>

            <button id="btn-test-notification" class="w-full bg-indigo-50 rounded-lg p-4 border border-indigo-200 text-center font-medium text-indigo-600 hover:bg-indigo-100 transition flex items-center justify-center gap-2">
                <span>🔔</span>
                <span>Kirim Test Notifikasi</span>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full bg-red-50 rounded-lg p-4 border border-red-200 text-center font-medium text-red-600 hover:bg-red-100 transition flex items-center justify-center gap-2">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testBtn = document.getElementById('btn-test-notification');
        if (testBtn) {
            testBtn.addEventListener('click', function() {
                const btn = this;
                const originalContent = btn.innerHTML;
                
                btn.disabled = true;
                btn.innerHTML = '<span class="animate-spin">🌀</span><span>Mengirim...</span>';

                fetch("{{ route('api.test-notification') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Berhasil! Silakan cek HP/Browser Anda untuk notifikasi OneSignal.');
                    } else {
                        console.error('Gagal: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim notifikasi. Pastikan Anda sudah login.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
            });
        }
    });
</script>

<x-mobile-bottom-nav active="profile" />

@endsection
