<script lang="ts" setup>
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

const props = withDefaults(defineProps<{
    modelValue: string | number | null;
    label: string;
    options: { value: string | number; label: string }[];
    id?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    placeholder?: string;
    compact?: boolean;
    hideLabel?: boolean;
    variant?: 'default' | 'learning';
}>(), {
    compact: false,
    hideLabel: false,
    variant: 'default',
});

defineEmits<{ (e: 'update:modelValue', value: string): void }>();
</script>

<template>
    <FieldWrapper
        :error="error"
        :for-id="id"
        :hint="hint"
        :label="hideLabel ? undefined : label"
        :required="required"
    >
        <label v-if="hideLabel" :for="id" class="sr-only">{{ label }}</label>
        <select
            :id="id"
            :value="modelValue ?? ''"
            :class="[
                props.compact ? 'h-10 pl-3 pr-8' : 'h-11 px-3',
                props.variant === 'learning'
                    ? 'learning-field focus:border-sky-400/60'
                    : 'admin-field focus:border-[#c23a72]',
            ]"
            class="w-full border text-sm outline-none transition"
            @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option v-if="placeholder" disabled value="">{{ placeholder }}</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
    </FieldWrapper>
</template>
