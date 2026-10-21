@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex min-h-[44px] w-full items-center gap-3 rounded-control border-l-2 border-gold-600 bg-aequitas-50 px-3 py-2.5 text-sm font-semibold text-aequitas-900 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2'
            : 'flex min-h-[44px] w-full items-center gap-3 rounded-control border-l-2 border-transparent px-3 py-2.5 text-sm font-medium text-muted transition duration-150 ease-in-out hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
