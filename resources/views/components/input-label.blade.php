@props(['value'])

<label {{ $attributes->merge(['class' => 'text-fg-title text-sm']) }}>
    {{ $value ?? $slot }}
</label>
