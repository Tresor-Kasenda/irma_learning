<script lang="ts" setup>
import {Link} from '@inertiajs/vue3';
import {computed} from 'vue';
import {safeCurrent, safeRoute} from '@/utilities/route';

defineProps<{ open: boolean }>();
const emit = defineEmits<{ (e: 'update:open', value: boolean): void }>();

interface NavItem {
    label: string;
    route: string;
    icon: string;
}

interface NavGroup {
    label: string;
    items: NavItem[];
}

// Les routes encore inexistantes renvoient « # » via safeRoute (migration en cours).
const groups = computed<NavGroup[]>(() => [
    {
        label: 'Catalogue',
        items: [
            {label: 'Tableau de bord', route: 'admin.dashboard', icon: 'M3 12l9-9 9 9M5 10v10h14V10'},
            {label: 'Formations', route: 'admin.formations.index', icon: 'M12 6.2L3 11l9 4.8L21 11 12 6.2z'},
            {label: 'Sections', route: 'admin.sections.index', icon: 'M4 6h16M4 12h16M4 18h10'},
            {label: 'Chapitres', route: 'admin.chapters.index', icon: 'M4 5h16v14H4zM8 5v14'},
        ],
    },
    {
        label: 'Évaluations',
        items: [
            {label: 'Examens', route: 'admin.exams.index', icon: 'M9 12l2 2 4-4M5 4h14v16H5z'},
            {label: 'Tentatives', route: 'admin.attempts.index', icon: 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z'},
        ],
    },
    {
        label: 'Apprenants',
        items: [
            {label: 'Inscriptions', route: 'admin.enrollments.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-1a7 7 0 0114 0v1'},
            {label: 'Progression', route: 'admin.progress.index', icon: 'M4 19V5m0 14h16M8 17v-6m4 6V8m4 9v-3'},
            {label: 'Certificats', route: 'admin.certificates.index', icon: 'M12 15a4 4 0 100-8 4 4 0 000 8zM9 13l-2 6 5-3 5 3-2-6'},
            {label: 'Codes d\'accès', route: 'admin.access-codes.index', icon: 'M15 7a4 4 0 10-3.8 5.3L14 15h2v2h2v2h3v-3l-3.2-3.2A4 4 0 0015 7z'},
        ],
    },
    {
        label: 'Administration',
        items: [
            {label: 'Utilisateurs', route: 'admin.users.index', icon: 'M17 20h5v-1a4 4 0 00-4-4M9 20H4v-1a4 4 0 014-4h4a4 4 0 014 4v1M12 11a4 4 0 100-8 4 4 0 000 8z'},
            {label: 'Paramètres', route: 'admin.settings.edit', icon: 'M12 15a3 3 0 100-6 3 3 0 000 6zM19 12a7 7 0 00-.1-1l2-1.6-2-3.4-2.4 1a7 7 0 00-1.7-1l-.4-2.6H9.6l-.4 2.6a7 7 0 00-1.7 1l-2.4-1-2 3.4L5.1 11a7 7 0 000 2l-2 1.6 2 3.4 2.4-1a7 7 0 001.7 1l.4 2.6h4.8l.4-2.6a7 7 0 001.7-1l2.4 1 2-3.4-2-1.6c.1-.3.1-.7.1-1z'},
        ],
    },
]);

function isActive(routeName: string): boolean {
    return safeCurrent(routeName);
}

function href(routeName: string): string {
    return safeRoute(routeName);
}

function close(): void {
    emit('update:open', false);
}
</script>

<template>
    <div>
        <button
            v-if="open"
            aria-label="Fermer la navigation"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            type="button"
            @click="close"
        />

        <aside
            :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-200"
        >
            <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-200 px-5">
                <img alt="IRMA" class="h-8 w-auto" src="/images/irma-logo-base.svg"/>
                <span class="text-sm font-semibold text-slate-800">Administration</span>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <div v-for="group in groups" :key="group.label" class="mb-5">
                    <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                        {{ group.label }}
                    </p>
                    <div class="grid gap-0.5">
                        <Link
                            v-for="item in group.items"
                            :key="item.label"
                            :class="isActive(item.route)
                                ? 'bg-[#bf045b]/10 font-semibold text-[#bf045b]'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                            :href="href(item.route)"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition"
                            @click="close"
                        >
                            <svg class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path :d="item.icon" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
            </nav>
        </aside>
    </div>
</template>
