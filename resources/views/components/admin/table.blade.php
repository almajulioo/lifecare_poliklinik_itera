@props(['responsive' => true])

<div class="overflow-x-auto">
    <table class="w-full text-sm {{ $responsive ? 'min-w-max' : '' }}">
        {{ $slot }}
    </table>
</div>
