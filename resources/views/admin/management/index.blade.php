@extends('admin.layouts.app')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Admin</h1>
            <p class="mt-2 text-sm text-gray-600">Kelola akun admin poliklinik</p>
        </div>
        <a href="{{ route('admin.management.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Admin
        </a>
    </div>

    <!-- Alert Messages -->
    @if ($message = Session::get('success'))
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ $message }}</p>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800">{{ $message }}</p>
        </div>
    @endif

    <!-- Admin Table -->
    <div class="mt-8 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Dibuat</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($admins as $admin)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $admin->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100">
                                    <span class="text-teal-700 font-semibold">{{ strtoupper(substr($admin->email, 0, 1)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">{{ $admin->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $admin->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <form method="POST" action="{{ route('admin.management.destroy', $admin) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-600">
                            Tidak ada admin terdaftar
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $admins->links() }}
    </div>
</div>
@endsection
