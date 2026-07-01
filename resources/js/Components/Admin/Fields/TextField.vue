<script lang="ts" setup>
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

withDefaults(
    defineProps<{
        modelValue: string | number | null;
        label: string;
        type?: string;
        error?: string;
        hint?: string;
        required?: boolean;
        placeholder?: string;
        inputClass?: string;
    }>(),
    {type: 'text'},
);

defineEmits<{ (e: 'update:modelValue', value: string): void }>();
</script>

<template>
    <FieldWrapper :error="error" :hint="hint" :label="label" :required="required">
        <input
            :placeholder="placeholder"
            :type="type"
            :value="modelValue"
            :class="inputClass"
            class="admin-field h-11 w-full border px-3 text-sm outline-none transition"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
    </FieldWrapper>
</template>
