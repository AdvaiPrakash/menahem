@props([
    'delay' => 0,
    'class' => '',
])

<div
    data-reveal
    data-reveal-delay="{{ $delay }}"
    {{ $attributes->merge(['class' => 'transition-all duration-700 ' . $class]) }}
>
    {{ $slot }}
</div>
