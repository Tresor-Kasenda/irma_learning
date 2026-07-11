<script lang="ts" setup>
import type {Component} from 'vue';
import {Link} from '@inertiajs/vue3';

defineProps<{
    label: string;
    value: string | number;
    icon?: Component;
    accent?: string;
    href?: string;
}>();
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        :href="href"
        :aria-label="href ? 'Voir ' + label : undefined"
        class="admin-panel group flex min-h-28 items-center gap-4 border p-5 transition hover:border-slate-300 hover:bg-slate-50 dark:hover:border-white/20 dark:hover:bg-[#10243b]"
    >
        <span
            :class="accent ?? 'bg-[#7d254a]/35 text-[#ef477d]'"
            class="grid size-11 shrink-0 place-items-center transition group-hover:scale-105"
        >
            <component :is="icon" v-if="icon" class="size-5" :stroke-width="1.7"/>
        </span>
        <div class="min-w-0">
            <p class="admin-heading text-2xl font-semibold tracking-tight">{{ value }}</p>
            <p class="mt-1 truncate text-sm text-slate-500">{{ label }}</p>
        </div>
    </component>
</template>
