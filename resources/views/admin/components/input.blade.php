@props(['icon' => null, 'leadingIcon' => null])

<div class="relative">
    <input 
        type="text"
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent']) }}
    />
    @if($leadingIcon)
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        {{ $leadingIcon }}
    </span>
    @endif
</div>