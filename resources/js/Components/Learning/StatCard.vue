<script setup lang="ts">
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

defineProps<{
    label: string;
    value: number | string;
    icon: string;
    tone: 'rose' | 'emerald' | 'amber' | 'blue';
}>();

const tones = {
    rose: 'bg-rose-500/10 text-rose-300',
    emerald: 'bg-emerald-500/10 text-emerald-300',
    amber: 'bg-amber-500/10 text-amber-300',
    blue: 'bg-blue-500/10 text-blue-300',
};
</script>

<template>
    <div class="flex min-h-28 items-center gap-3 border border-white/10 bg-[#101d2d] p-3 sm:gap-4 sm:p-4">
        <span class="grid size-10 shrink-0 place-items-center sm:size-11" :class="tones[tone]">
            <LearningIcon :name="icon" class="size-5 brightness-0 invert" />
        </span>
        <div>
            <p class="text-2xl font-semibold text-white">{{ value }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ label }}</p>
        </div>
    </div>
</template>
