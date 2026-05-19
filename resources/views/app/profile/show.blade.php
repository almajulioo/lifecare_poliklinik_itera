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
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12a5 5 0 100-10 5 5 0 000 10z"></path>
                        <path d="M12 14c-6 0-8 3-8 3v3c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-3s-2-3-8-3z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-blue-600 mt-1">{{ auth()->user()->email }}</p>
            </div>
        </div>

        {{-- Profile Details --}}
        <div class="bg-white rounded-lg border border-slate-200 p-4 mb-6 space-y-4">
            <div class="border-b pb-3">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Status</p>
                <p class="text-sm font-medium text-slate-900 mt-1">
                    @if(auth()->user()->role_user == 'mahasiswa')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Mahasiswa</span>
                    @elseif(auth()->user()->role_user == 'pegawai')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Pegawai</span>
                    @else
                        {{ auth()->user()->role_user }}
                    @endif
                </p>
            </div>
            
            @if(auth()->user()->nim)
            <div class="border-b pb-3">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">NIM</p>
                <p class="text-sm font-medium text-slate-900 mt-1">{{ auth()->user()->nim }}</p>
            </div>
            @endif

            @if(auth()->user()->prodi)
            <div class="border-b pb-3">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Program Studi</p>
                <p class="text-sm font-medium text-slate-900 mt-1">{{ auth()->user()->prodi }}</p>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-4 border-b pb-3">
                @if(auth()->user()->age)
                <div>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Usia</p>
                    <p class="text-sm font-medium text-slate-900 mt-1">{{ auth()->user()->age }} tahun</p>
                </div>
                @endif

                @if(auth()->user()->gender)
                <div>
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Jenis Kelamin</p>
                    <p class="text-sm font-medium text-slate-900 mt-1">
                        @if(auth()->user()->gender == 'laki-laki')
                            Laki-laki
                        @elseif(auth()->user()->gender == 'perempuan')
                            Perempuan
                        @else
                            {{ auth()->user()->gender }}
                        @endif
                    </p>
                </div>
                @endif
            </div>

            @if(auth()->user()->phone)
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Nomor Telepon</p>
                <p class="text-sm font-medium text-slate-900 mt-1">
                    <a href="tel:{{ auth()->user()->phone }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                        {{ auth()->user()->phone }}
                    </a>
                </p>
            </div>
            @endif
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 gap-3 mb-6">
            <a href="{{ route('app.compliance.show') }}" class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200 text-center hover:border-purple-300 transition">
                <svg class="w-6 h-6 mx-auto mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="text-xs text-purple-700 font-semibold">Statistik Kepatuhan</p>
            </a>
            <a href="{{ route('app.history.index') }}" class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200 text-center hover:border-indigo-300 transition">
                <svg class="w-6 h-6 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-xs text-indigo-700 font-semibold">Riwayat Minum</p>
            </a>
        </div>

        {{-- Action Buttons --}}
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-slate-900">Pengaturan</h3>
            
            <a href="{{ route('app.settings') }}" class="block w-full bg-white rounded-lg p-4 border border-slate-200 text-center font-medium text-slate-900 hover:bg-slate-50 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Pengaturan Notifikasi</span>
            </a>

            <button id="btn-test-notification" class="w-full bg-indigo-50 rounded-lg p-4 border border-indigo-200 text-center font-medium text-indigo-600 hover:bg-indigo-100 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span>Kirim Test Notifikasi</span>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full bg-red-50 rounded-lg p-4 border border-red-200 text-center font-medium text-red-600 hover:bg-red-100 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
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
