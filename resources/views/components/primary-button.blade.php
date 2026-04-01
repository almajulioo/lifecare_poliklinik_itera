<button {{ $attributes->merge(['type' => 'submit']) }} style="background-color: #16bac5; color: white;" class="inline-flex items-center justify-center w-full px-6 py-2.5 border border-transparent rounded-lg font-semibold text-sm uppercase tracking-wide hover:opacity-90 active:opacity-100 focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" :style="{backgroundColor: '#16bac5'}">
    {{ $slot }}
</button>
