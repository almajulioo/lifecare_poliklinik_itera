{{-- Mobile Bottom Navigation Component --}}
@props(['active' => 'dashboard'])

<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-area-inset-bottom">
    <div class="flex justify-around items-center h-20">
        {{-- Dashboard Tab --}}
        <a href="{{ route('app.dashboard') }}" 
           class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full
               {{ $active == 'dashboard' ? 'text-black font-semibold' : 'text-gray-500' }}
           @class(['transition-colors duration-200', 'hover:text-black' => $active != 'dashboard'])>
            <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            <span class="text-xs font-medium">Dashboard</span>
        </a>

        {{-- Medications Tab --}}
        <a href="{{ route('app.medications.index') }}" 
           class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full
               {{ $active == 'medications' ? 'text-black font-semibold' : 'text-gray-500' }}
           @class(['transition-colors duration-200', 'hover:text-black' => $active != 'medications'])>
            <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <span class="text-xs font-medium">Obat</span>
        </a>

        {{-- History Tab --}}
        <a href="{{ route('app.history.index') }}" 
           class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full
               {{ $active == 'history' ? 'text-black font-semibold' : 'text-gray-500' }}
           @class(['transition-colors duration-200', 'hover:text-black' => $active != 'history'])>
            <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V6h14v14z"/>
            </svg>
            <span class="text-xs font-medium">Riwayat</span>
        </a>

        {{-- Profile Tab --}}
        <a href="{{ route('app.profile.show') }}" 
           class="flex flex-col items-center justify-center py-2 px-4 flex-1 h-full
               {{ $active == 'profile' ? 'text-black font-semibold' : 'text-gray-500' }}
           @class(['transition-colors duration-200', 'hover:text-black' => $active != 'profile'])>
            <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span class="text-xs font-medium">Profil</span>
        </a>
    </div>
</nav>

<style>
    .safe-area-inset-bottom {
        padding-bottom: env(safe-area-inset-bottom);
    }
</style>
