<script lang="ts" setup>
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

defineProps<{
    modelValue: string | number | null;
    label: string;
    options: { value: string | number; label: string }[];
    error?: string;
    hint?: string;
    required?: boolean;
    placeholder?: string;
}>();

defineEmits<{ (e: 'update:modelValue', value: string): void }>();
</script>

<template>
    <FieldWrapper :error="error" :hint="hint" :label="label" :required="required">
        <select
            :value="modelValue ?? ''"
            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-[#bf045b]"
            @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option v-if="placeholder" disabled value="">{{ placeholder }}</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
    </FieldWrapper>
</template>
