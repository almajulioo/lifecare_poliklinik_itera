@props(['title', 'class' => ''])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 {{ $class }}">
    @if($title)
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
    </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>