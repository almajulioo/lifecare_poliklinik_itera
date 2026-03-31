@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
$baseClasses = 'font-semibold rounded-lg transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800',
    'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 active:bg-gray-400',
    'success' => 'bg-green-600 text-white hover:bg-green-700 active:bg-green-800',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800',
    'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 active:bg-gray-200',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2.5 text-base',
    'lg' => 'px-6 py-3 text-lg',
    'full' => 'w-full px-4 py-2.5 text-base',
];
@endphp

<{{ $type }} {{ $attributes->merge(['class' => "$baseClasses {$variants[$variant]} {$sizes[$size]}"]) }}>
    {{ $slot }}
</{{ $type }}>