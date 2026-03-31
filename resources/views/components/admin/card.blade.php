@props(['title' => null, 'class' => ''])

<div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow {{ $class }}">
    @if($title ?? false)
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
    </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
