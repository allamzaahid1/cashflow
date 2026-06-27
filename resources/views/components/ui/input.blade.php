@props([
    'label',
    'name',
    'type' => 'text',
])

<div>

    <label
        for="{{ $name }}"
        class="mb-2 block text-sm font-semibold text-text-secondary">

        {{ $label }}

    </label>

    <input

        id="{{ $name }}"

        name="{{ $name }}"

        type="{{ $type }}"

        value="{{ old($name) }}"

        {{ $attributes->merge([

            'class' => 'w-full rounded-xl border border-border-base bg-bg-surface px-4 py-3 text-text-primary focus:border-emerald-500 focus:ring-emerald-500 transition-colors duration-200'

        ]) }}

    >

    @error($name)

        <p class="mt-2 text-sm text-danger-text">

            {{ $message }}

        </p>

    @enderror

</div>