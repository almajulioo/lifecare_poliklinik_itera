@extends('admin.layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Pengguna')

@section('content')
<div class="space-y-8">
    <!-- Page Header dengan Deskripsi -->
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Pengguna Aplikasi</h2>
            <p class="text-sm text-gray-600 mt-1">Kelola dan pantau semua pengguna yang terdaftar</p>
        </div>
        <a href="{{ route('admin.pengguna.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Pengguna
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="flex gap-4">
        <div class="flex-1">
            <input 
                type="text" 
                placeholder="Cari berdasarkan nama, email, atau ID..."
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
        </div>
        <select class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700">
            <option>Semua Status</option>
            <option>Aktif</option>
            <option>Tidak Aktif</option>
            <option>Dinonaktifkan</option>
        </select>
    </div>

    <!-- Users Table -->
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">PENGGUNA</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">EMAIL</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ROLE</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">TANGGAL REGISTRASI</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if($user->role_user == 'mahasiswa')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Mahasiswa
                                    </span>
                                @elseif($user->role_user == 'pegawai')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                        Pegawai
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        {{ $user->role_user ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-medium">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-600">
                                Belum ada pengguna
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan 1-6 dari 50 pengguna</p>
                <div class="flex gap-2">
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">Sebelumnya</button>
                    <button class="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm">1</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">2</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">Selanjutnya</button>
                </div>
            </div>
        @endif
    </x-admin.card>
</div>
@endsection
