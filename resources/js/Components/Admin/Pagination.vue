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
        v-if="total > 0"
        class="admin-divider flex flex-col gap-3 border-t px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-xs text-slate-500">
            Affichage de <span class="admin-text font-medium">{{ from ?? 0 }}</span> à
            <span class="admin-text font-medium">{{ to ?? 0 }}</span> sur
            <span class="admin-text font-medium">{{ total }}</span>
        </p>
        <nav v-if="links.length > 3" class="flex flex-wrap gap-1" aria-label="Pagination">
            <template v-for="(link, index) in links" :key="index">
                <Link
                    v-if="link.url"
                    :class="link.active
                        ? 'border-[#a23362] bg-[#a23362] text-white'
                        : 'admin-divider admin-text admin-hover'"
                    :href="link.url"
                    class="grid min-h-8 min-w-8 place-items-center border px-2.5 text-xs transition"
                    preserve-scroll
                    preserve-state
                >
                    <span v-html="link.label"/>
                </Link>
                <span
                    v-else
                    class="grid min-h-8 min-w-8 place-items-center border border-white/5 px-2.5 text-xs text-slate-700"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
