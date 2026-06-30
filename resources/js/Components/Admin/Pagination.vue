<script lang="ts" setup>
import {Link} from '@inertiajs/vue3';

interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    links: PageLink[];
    from: number | null;
    to: number | null;
    total: number;
}>();
</script>

<template>
    <div
        v-if="links.length > 3"
        class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-xs text-slate-500">
            Affichage de <span class="font-medium text-slate-700">{{ from ?? 0 }}</span> à
            <span class="font-medium text-slate-700">{{ to ?? 0 }}</span> sur
            <span class="font-medium text-slate-700">{{ total }}</span>
        </p>
        <nav class="flex flex-wrap gap-1" aria-label="Pagination">
            <template v-for="(link, index) in links" :key="index">
                <Link
                    v-if="link.url"
                    :class="link.active
                        ? 'border-[#bf045b] bg-[#bf045b] text-white'
                        : 'border-slate-200 text-slate-600 hover:bg-slate-100'"
                    :href="link.url"
                    class="grid min-h-8 min-w-8 place-items-center rounded-md border px-2.5 text-xs transition"
                    preserve-scroll
                    preserve-state
                >
                    <span v-html="link.label"/>
                </Link>
                <span
                    v-else
                    class="grid min-h-8 min-w-8 place-items-center rounded-md border border-slate-100 px-2.5 text-xs text-slate-300"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
