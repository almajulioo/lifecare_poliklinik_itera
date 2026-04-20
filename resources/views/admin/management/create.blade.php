@extends('admin.layouts.app')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('admin.management.index') }}" class="text-teal-600 hover:text-teal-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Tambah Admin Baru</h1>

        <form method="POST" action="{{ route('admin.management.store') }}" class="space-y-6">
            @csrf

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Admin</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('email') border-red-500 @enderror"
                    placeholder="admin@itera.ac.id"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    placeholder="Minimal 8 karakter"
                    required
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-600">Minimal 8 karakter, kombinasikan huruf, angka, dan simbol untuk keamanan maksimal</p>
            </div>

            <!-- Password Confirmation Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="mt-2 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    placeholder="Ulangi password"
                    required
                >
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3 pt-4">
                <button 
                    type="submit" 
                    class="flex-1 px-6 py-2.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition font-medium"
                >
                    Tambah Admin
                </button>
                <a 
                    href="{{ route('admin.management.index') }}" 
                    class="flex-1 px-6 py-2.5 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-medium text-center"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
