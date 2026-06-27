@props([
    'route',
    'icon',
    'title',
])

@php
    $active = request()->routeIs($route);
@endphp

<a href="{{ route($route) }}"
    @class([
        'flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200',
        'bg-emerald-600 text-white shadow-md' => $active,
        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! $active,
    ])>

    <x-dynamic-component
        :component="'lucide-'.$icon"
        class="h-5 w-5"/>

    <span class="font-medium">
        {{ $title }}
    </span>

</a>