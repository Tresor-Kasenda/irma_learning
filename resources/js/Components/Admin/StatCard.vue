<script lang="ts" setup>
defineProps<{
    label: string;
    value: string | number;
    icon?: string;
    accent?: string;
}>();
</script>

<template>
    <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
        <span
            :class="accent ?? 'bg-[#bf045b]/10 text-[#bf045b]'"
            class="grid size-11 shrink-0 place-items-center rounded-lg"
        >
            <svg v-if="icon" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                <path :d="icon" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <div class="min-w-0">
            <p class="text-2xl font-semibold text-slate-900">{{ value }}</p>
            <p class="truncate text-sm text-slate-500">{{ label }}</p>
        </div>
    </div>
</template>
