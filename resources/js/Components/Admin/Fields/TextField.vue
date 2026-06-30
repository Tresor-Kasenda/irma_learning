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
            class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-[#bf045b]"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
    </FieldWrapper>
</template>
