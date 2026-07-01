<script lang="ts" setup>
withDefaults(defineProps<{
    modelValue: boolean;
    label: string;
    hint?: string;
    compact?: boolean;
}>(), {
    compact: false,
});

defineEmits<{ (e: 'update:modelValue', value: boolean): void }>();
</script>

<template>
    <div
        :class="compact ? 'gap-3' : 'admin-panel-muted gap-4 border p-3.5 hover:border-slate-300 dark:hover:border-white/20'"
        class="flex items-center justify-between transition"
    >
        <span>
            <span class="admin-heading block text-sm font-medium">{{ label }}</span>
            <span v-if="hint" class="mt-0.5 block text-xs leading-5 text-slate-500">{{ hint }}</span>
        </span>
        <button
            :aria-label="label"
            :aria-checked="modelValue"
            :class="modelValue ? 'bg-[#a23362]' : 'bg-slate-300 dark:bg-slate-700'"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
            role="switch"
            type="button"
            @click="$emit('update:modelValue', !modelValue)"
        >
            <span
                :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
                class="inline-block size-4 rounded-full bg-white transition"
            />
        </button>
    </div>
</template>
