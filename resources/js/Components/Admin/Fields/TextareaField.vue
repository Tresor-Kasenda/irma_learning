<script lang="ts" setup>
import {nextTick, onMounted, ref, watch} from 'vue';
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        label?: string;
        error?: string;
        hint?: string;
        required?: boolean;
        rows?: number;
        placeholder?: string;
    }>(),
    {rows: 3},
);

const emit = defineEmits<{ (e: 'update:modelValue', value: string): void }>();

const el = ref<HTMLTextAreaElement | null>(null);

function resize(): void {
    const textarea = el.value;
    if (!textarea) {
        return;
    }
    textarea.style.height = 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
}

function onInput(event: Event): void {
    emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
    resize();
}

onMounted(resize);
watch(() => props.modelValue, () => nextTick(resize));
</script>

<template>
    <FieldWrapper :error="error" :hint="hint" :label="label" :required="required">
        <textarea
            ref="el"
            :placeholder="placeholder"
            :rows="rows"
            :value="modelValue ?? ''"
            class="admin-field block min-h-24 w-full resize-none overflow-hidden border px-3 py-3 text-sm leading-6 outline-none transition"
            @input="onInput"
        />
    </FieldWrapper>
</template>
