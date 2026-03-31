@props(['title', 'value', 'icon' => null, 'color' => 'blue'])

@php
$colors = [
    'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
    'green' => 'bg-green-50 text-green-600 border-green-200',
    'red' => 'bg-red-50 text-red-600 border-red-200',
    'yellow' => 'bg-yellow-50 text-yellow-600 border-yellow-200',
];
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 mb-2">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
        </div>
        @if($icon)
        <div class="text-4xl {{ $colors[$color] ?? $colors['blue'] }} p-3 rounded-lg">
            {{ $icon }}
        </div>
        @endif
    </div>
</div>