<script lang="ts" setup>
import {Link} from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    ChartNoAxesColumnIncreasing,
    ClipboardCheck,
    Clock3,
    FileText,
    Home,
    KeyRound,
    Layers3,
    Settings,
    Users,
    X,
} from '@lucide/vue';
import {computed, onBeforeUnmount, onMounted, ref} from 'vue';
import type {Component} from 'vue';
import {useUiStore} from '@/stores';
import {safeCurrent, safeRoute} from '@/utilities/route';

defineProps<{ open: boolean }>();
const emit = defineEmits<{ (e: 'update:open', value: boolean): void }>();

interface NavItem {
    label: string;
    route: string;
    icon: Component;
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
            {label: 'Tableau de bord', route: 'admin.dashboard', icon: Home},
            {label: 'Formations', route: 'admin.formations.index', icon: BookOpen},
            {label: 'Sections', route: 'admin.sections.index', icon: Layers3},
            {label: 'Chapitres', route: 'admin.chapters.index', icon: FileText},
        ],
    },
    {
        label: 'Évaluations',
        items: [
            {label: 'Examens', route: 'admin.exams.index', icon: ClipboardCheck},
            {label: 'Tentatives', route: 'admin.attempts.index', icon: Clock3},
        ],
    },
    {
        label: 'Apprenants',
        items: [
            {label: 'Inscriptions', route: 'admin.enrollments.index', icon: Users},
            {label: 'Progression', route: 'admin.progress.index', icon: ChartNoAxesColumnIncreasing},
            {label: 'Certificats', route: 'admin.certificates.index', icon: Award},
            {label: 'Codes d\'accès', route: 'admin.access-codes.index', icon: KeyRound},
        ],
    },
    {
        label: 'Administration',
        items: [
            {label: 'Utilisateurs', route: 'admin.users.index', icon: Users},
            {label: 'Paramètres', route: 'admin.settings.edit', icon: Settings},
        ],
    },
]);

const uiStore = useUiStore();
const isDesktop = ref(false);
let desktopMediaQuery: MediaQueryList | null = null;

function updateDesktopState(event?: MediaQueryListEvent): void {
    isDesktop.value = event?.matches ?? desktopMediaQuery?.matches ?? false;
}

onMounted(() => {
    desktopMediaQuery = window.matchMedia('(min-width: 1024px)');
    updateDesktopState();
    desktopMediaQuery.addEventListener('change', updateDesktopState);
});

onBeforeUnmount(() => desktopMediaQuery?.removeEventListener('change', updateDesktopState));

function isActive(routeName: string): boolean {
    return safeCurrent(routeName);
}

function href(routeName: string): string {
    return safeRoute(routeName);
}

function isAvailable(routeName: string): boolean {
    return href(routeName) !== '#';
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
            :aria-hidden="!open && !isDesktop"
            :class="[
                open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                uiStore.sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
            ]"
            class="admin-sidebar admin-divider fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r transition-all duration-200"
            :inert="!open && !isDesktop"
        >
            <div
                :class="uiStore.sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                class="admin-divider flex h-16 shrink-0 items-center gap-3 border-b px-5"
            >
                <Link :href="href('admin.dashboard')" class="flex min-w-0 items-center gap-3" @click="close">
                    <img alt="IRMA" class="h-10 w-auto shrink-0" src="/images/irma-logo-base.svg"/>
                    <span v-if="!uiStore.sidebarCollapsed" class="min-w-0 lg:block">
                        <span class="admin-heading block truncate text-sm font-semibold">IRMA Admin</span>
                        <span class="block text-[10px] uppercase tracking-[0.18em] text-slate-500">Console de gestion</span>
                    </span>
                </Link>
                <button
                    aria-label="Fermer le menu"
                    class="admin-divider admin-text admin-hover ml-auto grid size-9 place-items-center border lg:hidden"
                    type="button"
                    @click="close"
                >
                    <X class="size-5"/>
                </button>
            </div>

            <nav :class="uiStore.sidebarCollapsed ? 'lg:px-2' : ''" class="flex-1 overflow-y-auto px-4 py-5">
                <div v-for="group in groups" :key="group.label" class="mb-5">
                    <p
                        v-if="!uiStore.sidebarCollapsed"
                        class="admin-faint px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.14em]"
                    >
                        {{ group.label }}
                    </p>
                    <div class="grid gap-1">
                        <component
                            :is="isAvailable(item.route) ? Link : 'span'"
                            v-for="item in group.items"
                            :key="item.label"
                            :class="[
                                isActive(item.route)
                                    ? 'bg-[#7d254a] font-semibold text-white'
                                    : 'admin-text admin-hover',
                                uiStore.sidebarCollapsed ? 'lg:justify-center lg:px-0' : 'px-3',
                                !isAvailable(item.route) ? 'cursor-not-allowed opacity-40 hover:bg-transparent' : '',
                            ]"
                            :aria-disabled="!isAvailable(item.route)"
                            :href="isAvailable(item.route) ? href(item.route) : undefined"
                            :title="uiStore.sidebarCollapsed ? item.label : undefined"
                            class="flex h-10 items-center gap-3 px-3 text-sm transition"
                            @click="isAvailable(item.route) && close()"
                        >
                            <component :is="item.icon" class="size-5 shrink-0" :stroke-width="1.7"/>
                            <span v-if="!uiStore.sidebarCollapsed">{{ item.label }}</span>
                        </component>
                    </div>
                </div>
            </nav>

            <div v-if="!uiStore.sidebarCollapsed" class="admin-divider border-t p-4">
                <Link
                    :href="href('dashboard')"
                    class="admin-panel-muted admin-text flex items-center gap-3 border border-[#a23362]/40 px-3 py-3 text-sm transition hover:border-[#a23362] hover:text-[#a23362]"
                >
                    <BookOpen class="size-5 text-[#ef477d]" :stroke-width="1.7"/>
                    <span>
                        <span class="block font-medium">Espace Learning</span>
                        <span class="block text-xs text-slate-500">Voir la plateforme</span>
                    </span>
                </Link>
            </div>
        </aside>
    </div>
</template>
