@props([
    'label',
    'name',
    'rows' => 3,
])

<div>

    <label
        for="{{ $name }}"
        class="mb-2 block text-sm font-semibold text-text-secondary">

        {{ $label }}

    </label>

    <textarea

        id="{{ $name }}"

        name="{{ $name }}"

        rows="{{ $rows }}"

        {{ $attributes->merge([

            'class' => 'w-full rounded-xl border border-border-base bg-bg-surface px-4 py-3 text-text-primary focus:border-emerald-500 focus:ring-emerald-500 transition-colors duration-200 resize-none'

        ]) }}

    >{{ old($name, $slot) }}</textarea>

    @error($name)

        <p class="mt-2 text-sm text-danger-text">

            {{ $message }}

        </p>

    @enderror

</div>