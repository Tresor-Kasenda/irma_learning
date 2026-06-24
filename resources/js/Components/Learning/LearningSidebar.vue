<script lang="ts" setup>
import {Link, usePage} from '@inertiajs/vue3';
import {computed} from 'vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import type {LearningCatalogStats} from '@/types/learning';

type ActiveItem = 'dashboard' | 'formations' | 'in-progress' | 'certified' | 'enterprise';

const props = withDefaults(
    defineProps<{
        activeItem?: ActiveItem;
        catalogStats?: LearningCatalogStats | null;
        mobileSidebarOpen: boolean;
    }>(),
    {
        catalogStats: null,
    },
);

const emit = defineEmits<{
    (e: 'update:mobileSidebarOpen', value: boolean): void;
}>();

const page = usePage();

const currentUser = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => Boolean(currentUser.value));
const accountName = computed(() => currentUser.value?.name ?? 'Visiteur');
const accountRole = computed(() => (isAuthenticated.value ? 'Apprenant' : 'Compte invité'));

const currentActiveItem = computed(() => {
    if (props.activeItem) {
        return props.activeItem;
    }

    const filters = (page.props as any).filters;

    if (route().current('dashboard')) return 'dashboard';
    if (route().current('student.learnings')) return 'formations';
    if (route().current('student.progress')) return 'in-progress';
    if (route().current('certificats')) return 'certified';
    if (route().current('student.learnings') && filters?.category === 'enterprise') return 'enterprise';

    if (route().current('student.learnings') && !filters?.content) return 'formations';

    return props.activeItem;
});

const learningNavigation = computed(() => [
    {
        key: 'dashboard',
        label: 'Tableau de bord',
        href: route('dashboard'),
        icon: 'home',
    },
    {
        key: 'formations',
        label: 'Formations',
        href: route('student.learnings'),
        icon: 'book-open',
    },
    {
        key: 'in-progress',
        label: 'En cours',
        href: route('student.progress'),
        icon: 'chart-bar',
    },
    {
        key: 'certified',
        label: 'Certifiantes',
        href: route('certificats'),
        icon: 'academic-cap',
    },
    {
        key: 'enterprise',
        label: 'Entreprise',
        href: route('student.learnings', {category: 'enterprise'}),
        icon: 'users',
    },
]);

const formatNavigation = computed(() => [
    {
        label: 'Vidéos',
        href: route('student.learnings', {content: 'video'}),
        icon: 'video-camera',
        count: props.catalogStats?.videos
    },
    {
        label: 'PDF',
        href: route('student.learnings', {content: 'pdf'}),
        icon: 'document',
        count: props.catalogStats?.pdfs
    },
    {
        label: 'Textes',
        href: route('student.learnings', {content: 'text'}),
        icon: 'document-text',
        count: props.catalogStats?.texts
    },
]);

const isFormatActive = (content: string) => {
    return route().current('student.learnings') && (page.props as any).filters?.content === content;
};

const closeSidebar = () => {
    emit('update:mobileSidebarOpen', false);
};
</script>

<template>
    <div>
        <button
            v-if="mobileSidebarOpen"
            aria-label="Fermer la navigation"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            type="button"
            @click="closeSidebar"
        />

        <aside
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-63 flex-col border-r border-white/10 bg-[#08192b] transition-transform duration-200 lg:translate-x-0"
        >
            <div class="flex h-20 items-center justify-between border-b border-white/10 px-5">
                <Link :href="route('home-page')" class="flex items-center gap-2" @click="closeSidebar">
                    <img alt="IRMA" class="h-10 w-auto" src="/images/irma-logo-base.svg"/>
                    <img alt="IRMA Learning" class="h-8 w-auto" src="/images/irma-text.svg"/>
                </Link>
                <button
                    aria-label="Fermer le menu"
                    class="grid size-9 place-items-center border border-white/10 text-slate-300 lg:hidden"
                    type="button"
                    @click="closeSidebar"
                >
                    <LearningIcon class="size-5 brightness-0 invert" name="x-mark"/>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-5">
                <p class="px-3 text-[11px] font-semibold uppercase text-slate-500">Apprendre</p>
                <nav class="mt-2 grid gap-1">
                    <Link
                        v-for="item in learningNavigation"
                        :key="item.key"
                        :class="currentActiveItem === item.key
                            ? 'bg-[#7d254a] font-semibold text-white'
                            : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        :href="item.href"
                        class="flex h-10 items-center gap-3 px-3 text-sm transition"
                        @click="closeSidebar"
                    >
                        <LearningIcon
                            :class="currentActiveItem === item.key ? '' : 'opacity-70'"
                            :name="item.icon"
                            class="size-5 brightness-0 invert"
                        />
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="my-5 border-t border-white/10"/>

                <p class="px-3 text-[11px] font-semibold uppercase text-slate-500">Formats</p>
                <nav class="mt-2 grid gap-1">
                    <Link
                        v-for="item in formatNavigation"
                        :key="item.label"
                        :class="isFormatActive(item.label.toLowerCase() === 'vidéos' ? 'video' : (item.label.toLowerCase() === 'pdf' ? 'pdf' : 'text'))
                            ? 'bg-[#7d254a] font-semibold text-white'
                            : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        :href="item.href"
                        class="flex h-10 items-center gap-3 px-3 text-sm transition"
                        @click="closeSidebar"
                    >
                        <LearningIcon
                            :class="isFormatActive(item.label.toLowerCase() === 'vidéos' ? 'video' : (item.label.toLowerCase() === 'pdf' ? 'pdf' : 'text')) ? '' : 'opacity-70'"
                            :name="item.icon"
                            class="size-5 brightness-0 invert"
                        />
                        {{ item.label }}
                        <span v-if="item.count !== undefined" class="ml-auto text-xs text-slate-500">
                            {{ item.count }}
                        </span>
                    </Link>
                </nav>

                <div class="my-5 border-t border-white/10"/>

                <div class="border border-[#a23362]/50 bg-[#151c2c] p-4">
                    <p class="text-sm font-semibold text-[#ff6f9d]">Catalogue professionnel</p>
                    <p class="mt-2 text-xs leading-5 text-slate-400">
                        Des parcours vidéo, PDF et texte conçus pour progresser à votre rythme.
                    </p>
                    <Link
                        :href="route('pages.pricings')"
                        class="mt-4 inline-flex h-9 w-full items-center justify-center bg-[#8e2853] px-3 text-sm font-semibold text-white transition hover:bg-[#a23362]"
                    >
                        Voir les tarifs
                    </Link>
                </div>
            </div>

            <div class="border-t border-white/10 p-4">
                <Link
                    :href="isAuthenticated ? route('profile.edit') : route('login')"
                    class="flex items-center gap-3 p-2 transition hover:bg-white/5"
                >
                    <img
                        v-if="isAuthenticated"
                        alt=""
                        class="size-10 object-cover object-top"
                        src="/images/avatar.webp"
                    />
                    <span v-else class="grid size-10 place-items-center bg-white/5">
                        <LearningIcon class="size-6 brightness-0 invert opacity-70" name="user-circle"/>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ accountName }}</span>
                        <span class="block text-xs text-slate-500">{{ accountRole }}</span>
                    </span>
                    <LearningIcon class="size-4 brightness-0 invert opacity-50" name="arrow-right"/>
                </Link>
            </div>
        </aside>
    </div>
</template>
