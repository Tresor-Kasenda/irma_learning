<script lang="ts" setup>
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

withDefaults(
    defineProps<{
        modelValue: string | null;
        label: string;
        error?: string;
        hint?: string;
        required?: boolean;
        rows?: number;
        placeholder?: string;
    }>(),
    {rows: 3},
);

defineEmits<{ (e: 'update:modelValue', value: string): void }>();
</script>

<template>
    <FieldWrapper :error="error" :hint="hint" :label="label" :required="required">
        <textarea
            :placeholder="placeholder"
            :rows="rows"
            :value="modelValue ?? ''"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#bf045b]"
            @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
    </FieldWrapper>
</template>
