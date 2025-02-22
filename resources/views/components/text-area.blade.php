@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['class' => 'ui-form-input form-input-md rounded-md peer w-full h-24']) }}></textarea>