@props(['title', 'value', 'icon' => null, 'color' => 'blue'])

@php
$colors = [
    'blue' => 'bg-blue-50 text-blue-600',
    'green' => 'bg-green-50 text-green-600',
    'red' => 'bg-red-50 text-red-600',
    'yellow' => 'bg-yellow-50 text-yellow-600',
    'purple' => 'bg-purple-50 text-purple-600',
];
@endphp

<div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600">{{ $title }}</p>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ $value }}</p>
        </div>
        @if($icon)
        <div class="text-4xl w-14 h-14 flex items-center justify-center rounded-lg {{ $colors[$color] ?? $colors['blue'] }}">
            {{ $icon }}
        </div>
        @endif
    </div>
</div>
