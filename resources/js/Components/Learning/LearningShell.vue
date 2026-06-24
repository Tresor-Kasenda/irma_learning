<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import type { LearningCatalogStats } from '@/types/learning';

type ActiveItem = 'dashboard' | 'formations' | 'in-progress' | 'certified' | 'enterprise';

const props = withDefaults(
    defineProps<{
        activeItem: ActiveItem;
        catalogStats?: LearningCatalogStats | null;
    }>(),
    {
        catalogStats: null,
    },
);

const page = usePage();
const mobileSidebarOpen = ref(false);
const profileMenuOpen = ref(false);

const currentUser = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => Boolean(currentUser.value));
const accountName = computed(() => currentUser.value?.name ?? 'Visiteur');
const accountRole = computed(() => (isAuthenticated.value ? 'Apprenant' : 'Compte invité'));

const learningNavigation = computed(() => [
    {
        key: 'dashboard',
        label: isAuthenticated.value ? 'Tableau de bord' : 'Accueil',
        href: isAuthenticated.value ? route('dashboard') : route('home-page'),
        icon: 'home',
    },
    {
        key: 'formations',
        label: 'Formations',
        href: route('certifications'),
        icon: 'book-open',
    },
    {
        key: 'in-progress',
        label: 'En cours',
        href: isAuthenticated.value
            ? route('certifications', { category: 'in-progress' })
            : route('login'),
        icon: 'chart-bar',
    },
    {
        key: 'certified',
        label: 'Certifiantes',
        href: route('certifications', { category: 'certified' }),
        icon: 'academic-cap',
    },
    {
        key: 'enterprise',
        label: 'Entreprise',
        href: route('certifications', { category: 'enterprise' }),
        icon: 'users',
    },
]);

const formatNavigation = computed(() => [
    { label: 'Vidéos', href: route('certifications', { content: 'video' }), icon: 'video-camera', count: props.catalogStats?.videos },
    { label: 'PDF', href: route('certifications', { content: 'pdf' }), icon: 'document', count: props.catalogStats?.pdfs },
    { label: 'Textes', href: route('certifications', { content: 'text' }), icon: 'document-text', count: props.catalogStats?.texts },
]);
</script>

<template>
    <div class="min-h-screen bg-[#071525] text-slate-100">
        <button
            v-if="mobileSidebarOpen"
            type="button"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            aria-label="Fermer la navigation"
            @click="mobileSidebarOpen = false"
        />

        <!-- Profile menu backdrop -->
        <button
            v-if="profileMenuOpen"
            type="button"
            class="fixed inset-0 z-30"
            aria-label="Fermer le menu profil"
            @click="profileMenuOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[252px] flex-col border-r border-white/10 bg-[#08192b] transition-transform duration-200 lg:translate-x-0"
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-20 items-center justify-between border-b border-white/10 px-5">
                <Link :href="route('home-page')" class="flex items-center gap-2" @click="mobileSidebarOpen = false">
                    <img src="/images/irma-logo-base.svg" alt="IRMA" class="h-10 w-auto" />
                    <img src="/images/irma-text.svg" alt="IRMA Learning" class="h-8 w-auto" />
                </Link>
                <button
                    type="button"
                    class="grid size-9 place-items-center border border-white/10 text-slate-300 lg:hidden"
                    aria-label="Fermer le menu"
                    @click="mobileSidebarOpen = false"
                >
                    <LearningIcon name="x-mark" class="size-5 brightness-0 invert" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-5">
                <p class="px-3 text-[11px] font-semibold uppercase text-slate-500">Apprendre</p>
                <nav class="mt-2 grid gap-1">
                    <Link
                        v-for="item in learningNavigation"
                        :key="item.key"
                        :href="item.href"
                        class="flex h-10 items-center gap-3 px-3 text-sm transition"
                        :class="activeItem === item.key
                            ? 'bg-[#7d254a] font-semibold text-white'
                            : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        @click="mobileSidebarOpen = false"
                    >
                        <LearningIcon
                            :name="item.icon"
                            class="size-5 brightness-0 invert"
                            :class="activeItem === item.key ? '' : 'opacity-70'"
                        />
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="my-5 border-t border-white/10" />

                <p class="px-3 text-[11px] font-semibold uppercase text-slate-500">Formats</p>
                <nav class="mt-2 grid gap-1">
                    <Link
                        v-for="item in formatNavigation"
                        :key="item.label"
                        :href="item.href"
                        class="flex h-10 items-center gap-3 px-3 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="mobileSidebarOpen = false"
                    >
                        <LearningIcon :name="item.icon" class="size-5 brightness-0 invert opacity-70" />
                        {{ item.label }}
                        <span v-if="item.count !== undefined" class="ml-auto text-xs text-slate-500">{{ item.count }}</span>
                    </Link>
                </nav>

                <div class="my-5 border-t border-white/10" />

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
                        src="/images/avatar.webp"
                        alt=""
                        class="size-10 object-cover object-top"
                    />
                    <span v-else class="grid size-10 place-items-center bg-white/5">
                        <LearningIcon name="user-circle" class="size-6 brightness-0 invert opacity-70" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ accountName }}</span>
                        <span class="block text-xs text-slate-500">{{ accountRole }}</span>
                    </span>
                    <LearningIcon name="arrow-right" class="size-4 brightness-0 invert opacity-50" />
                </Link>
            </div>
        </aside>

        <main class="min-h-screen lg:pl-[252px]">
            <header class="flex h-16 items-center justify-between border-b border-white/10 px-4 sm:px-6 lg:px-8">
                <button
                    type="button"
                    class="grid size-10 place-items-center border border-white/10 lg:hidden"
                    aria-label="Ouvrir la navigation"
                    @click="mobileSidebarOpen = true"
                >
                    <LearningIcon name="bars-3" class="size-5 brightness-0 invert" />
                </button>
                <div class="hidden items-center gap-2 text-xs text-slate-500 sm:flex">
                    <span>IRMA Learning</span>
                    <span>/</span>
                    <slot name="breadcrumb">
                        <span class="text-slate-300">Espace d'apprentissage</span>
                    </slot>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <slot name="header-actions" />
                    <button
                        type="button"
                        class="relative grid size-10 place-items-center border border-white/10 text-slate-300 transition hover:bg-white/5"
                        title="Notifications"
                    >
                        <LearningIcon name="bell" class="size-5 brightness-0 invert opacity-80" />
                        <span class="absolute right-1.5 top-1.5 size-2 bg-[#ef477d]" />
                    </button>

                    <!-- Profile dropdown -->
                    <div class="relative hidden sm:block">
                        <button
                            type="button"
                            class="flex items-center gap-2 transition hover:opacity-80"
                            @click="profileMenuOpen = !profileMenuOpen"
                        >
                            <img
                                v-if="isAuthenticated"
                                src="/images/avatar.webp"
                                alt=""
                                class="size-8 object-cover object-top"
                            />
                            <span v-else class="grid size-8 place-items-center bg-white/10">
                                <LearningIcon name="user-circle" class="size-5 brightness-0 invert opacity-60" />
                            </span>
                            <span class="text-sm font-medium text-white">{{ accountName }}</span>
                            <LearningIcon
                                name="chevron-down"
                                class="size-4 brightness-0 invert opacity-60 transition"
                                :class="profileMenuOpen ? 'rotate-180' : ''"
                            />
                        </button>

                        <!-- Dropdown menu -->
                        <div
                            v-if="profileMenuOpen"
                            class="absolute right-0 top-full z-40 mt-2 w-52 border border-white/10 bg-[#0e2035] shadow-xl"
                        >
                            <div class="border-b border-white/10 px-4 py-3">
                                <p class="text-sm font-semibold text-white truncate">{{ accountName }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ accountRole }}</p>
                            </div>
                            <nav class="py-1">
                                <Link
                                    :href="isAuthenticated ? route('profile.edit') : route('login')"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                                    @click="profileMenuOpen = false"
                                >
                                    <LearningIcon name="user" class="size-4 brightness-0 invert opacity-60" />
                                    Mon profil
                                </Link>
                                <Link
                                    v-if="isAuthenticated"
                                    :href="route('dashboard')"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                                    @click="profileMenuOpen = false"
                                >
                                    <LearningIcon name="home" class="size-4 brightness-0 invert opacity-60" />
                                    Tableau de bord
                                </Link>
                            </nav>
                            <div v-if="isAuthenticated" class="border-t border-white/10 py-1">
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-rose-400 transition hover:bg-white/5 hover:text-rose-300"
                                    @click="profileMenuOpen = false"
                                >
                                    <LearningIcon name="arrow-left-start-on-rectangle" class="size-4 brightness-0 saturate-0 invert opacity-60" />
                                    Se déconnecter
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <slot />
        </main>
    </div>
</template>
