<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700'
    ]) }}
>

    {{ $slot }}

</button>