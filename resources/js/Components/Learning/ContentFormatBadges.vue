<script setup lang="ts">
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

withDefaults(
    defineProps<{
        videos?: number;
        pdfs?: number;
        texts?: number;
        compact?: boolean;
    }>(),
    {
        videos: 0,
        pdfs: 0,
        texts: 0,
        compact: false,
    },
);
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <span
            class="inline-flex items-center gap-1.5 border border-[#d2386e]/30 bg-[#261a29] text-slate-200"
            :class="compact ? 'h-7 px-2 text-[11px]' : 'h-8 px-2.5 text-xs'"
        >
            <span class="grid size-4 place-items-center bg-[#d2386e]">
                <LearningIcon name="play" class="size-2.5 brightness-0 invert" />
            </span>
            {{ videos }} vidéos
        </span>
        <span
            class="inline-flex items-center gap-1.5 border border-amber-500/25 bg-[#29231b] text-slate-200"
            :class="compact ? 'h-7 px-2 text-[11px]' : 'h-8 px-2.5 text-xs'"
        >
            <span class="grid size-4 place-items-center bg-amber-500">
                <LearningIcon name="document" class="size-2.5 brightness-0 invert" />
            </span>
            {{ pdfs }} PDF
        </span>
        <span
            class="inline-flex items-center gap-1.5 border border-blue-500/25 bg-[#17243a] text-slate-200"
            :class="compact ? 'h-7 px-2 text-[11px]' : 'h-8 px-2.5 text-xs'"
        >
            <span class="grid size-4 place-items-center bg-blue-500">
                <LearningIcon name="document-text" class="size-2.5 brightness-0 invert" />
            </span>
            {{ texts }} textes
        </span>
    </div>
</template>

