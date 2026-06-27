<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-border-base bg-bg-surface p-6 shadow-sm text-text-primary transition-all duration-200'
]) }}>
    {{ $slot }}
</div>