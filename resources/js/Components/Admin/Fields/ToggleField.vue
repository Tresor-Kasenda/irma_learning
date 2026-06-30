<script lang="ts" setup>
defineProps<{
    modelValue: boolean;
    label: string;
    hint?: string;
}>();

defineEmits<{ (e: 'update:modelValue', value: boolean): void }>();
</script>

<template>
    <label class="flex cursor-pointer items-center justify-between gap-4">
        <span>
            <span class="block text-sm font-medium text-slate-700">{{ label }}</span>
            <span v-if="hint" class="block text-xs text-slate-400">{{ hint }}</span>
        </span>
        <button
            :class="modelValue ? 'bg-[#bf045b]' : 'bg-slate-300'"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
            type="button"
            @click="$emit('update:modelValue', !modelValue)"
        >
            <span
                :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
                class="inline-block size-4 rounded-full bg-white transition"
            />
        </button>
    </label>
</template>
