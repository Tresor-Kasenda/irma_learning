@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'ui-form-input form-input-md rounded-md peer w-full']) }}>
