<script lang="ts" setup>
import type {Component} from 'vue';
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

const props = withDefaults(
    defineProps<{
        modelValue: number | null;
        id: string;
        label: string;
        icon?: Component;
        error?: string;
        hint?: string;
        required?: boolean;
        placeholder?: string;
        min?: number;
        max?: number;
        step?: number;
        suffix?: string;
    }>(),
    {
        step: 1,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: number | null): void;
}>();

function updateValue(event: Event): void {
    const value = (event.target as HTMLInputElement).value;
    emit('update:modelValue', value === '' ? null : Number(value));
}
</script>

<template>
    <FieldWrapper :error="error" :for-id="id" :hint="hint" :label="label" :required="required">
        <div class="relative">
            <component
                :is="props.icon"
                v-if="props.icon"
                class="admin-faint pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2"
                :stroke-width="1.7"
            />
            <input
                :id="id"
                :class="[props.icon ? 'pl-9' : 'pl-3', suffix ? 'pr-12' : 'pr-3']"
                :max="max"
                :min="min"
                :placeholder="placeholder"
                :step="step"
                :value="modelValue ?? ''"
                class="admin-field h-11 w-full border text-sm outline-none transition"
                inputmode="decimal"
                type="number"
                @input="updateValue"
            />
            <span v-if="suffix" class="admin-faint pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold uppercase">
                {{ suffix }}
            </span>
        </div>
    </FieldWrapper>
</template>
