@props(['color' => 'blue'])

@php
$colors = [
    'blue' => 'bg-blue-100 text-blue-800',
    'green' => 'bg-green-100 text-green-800',
    'red' => 'bg-red-100 text-red-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'purple' => 'bg-purple-100 text-purple-800',
    'pink' => 'bg-pink-100 text-pink-800',
    'gray' => 'bg-gray-100 text-gray-800',
];
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$color] ?? $colors['blue'] }}">
    {{ $slot }}
</span>
