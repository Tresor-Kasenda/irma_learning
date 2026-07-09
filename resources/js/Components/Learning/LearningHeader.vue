<script lang="ts" setup>
import {Link, usePage} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import {PanelLeftClose, PanelLeftOpen} from '@lucide/vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import {useUiStore} from "@/stores";
import {safeRoute} from "@/utilities/route";

const props = defineProps<{
    mobileSidebarOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:mobileSidebarOpen', value: boolean): void;
}>();

const page = usePage();
const uiStore = useUiStore();
const profileMenuOpen = ref(false);
const notificationMenuOpen = ref(false);

const currentUser = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => Boolean(currentUser.value));
const accountName = computed(() => currentUser.value?.name ?? 'Visiteur');
const accountRole = computed(() => (isAuthenticated.value ? 'Apprenant' : 'Compte invité'));

const toggleMobileSidebar = () => {
    emit('update:mobileSidebarOpen', !props.mobileSidebarOpen);
};

const toggleSidebarCollapsed = () => {
    uiStore.toggleSidebarCollapsed();
};

const closeMenus = () => {
    profileMenuOpen.value = false;
    notificationMenuOpen.value = false;
};
</script>

<template>
    <header class="flex h-16 items-center justify-between border-b border-white/10 px-4 sm:px-6 lg:px-8">
        <!-- Backdrops -->
        <div
            v-if="profileMenuOpen || notificationMenuOpen"
            class="fixed inset-0 z-30"
            @click="closeMenus"
        />

        <div class="flex items-center gap-4">
            <button
                aria-label="Ouvrir la navigation"
                class="grid size-10 place-items-center border border-white/10 lg:hidden"
                type="button"
                @click="toggleMobileSidebar"
            >
                <LearningIcon class="size-5 brightness-0 invert" name="bars-3"/>
            </button>

            <button
                aria-label="Réduire la barre latérale"
                class="hidden size-10 place-items-center border border-white/10 text-slate-300 transition hover:bg-white/5 lg:grid"
                type="button"
                @click="toggleSidebarCollapsed"
            >
                <PanelLeftOpen v-if="uiStore.sidebarCollapsed" class="size-5" :stroke-width="1.7"/>
                <PanelLeftClose v-else class="size-5" :stroke-width="1.7"/>
            </button>

            <div class="hidden items-center gap-2 text-xs text-slate-500 sm:flex">
                <span>IRMA Learning</span>
                <span>/</span>
                <slot name="breadcrumb">
                    <span class="text-slate-300">Espace d'apprentissage</span>
                </slot>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-3">
            <slot name="header-actions"/>

            <!-- Notifications dropdown -->
            <div class="relative">
                <button
                    class="relative grid size-10 place-items-center border border-white/10 text-slate-300 transition hover:bg-white/5"
                    title="Notifications"
                    type="button"
                    @click="notificationMenuOpen = !notificationMenuOpen; profileMenuOpen = false"
                >
                    <LearningIcon class="size-5 brightness-0 invert opacity-80" name="bell"/>
                    <span class="absolute right-1.5 top-1.5 size-2 bg-[#ef477d]"/>
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <div
                        v-if="notificationMenuOpen"
                        class="absolute right-0 top-full z-40 mt-2 w-80 border border-white/10 bg-[#0e2035] shadow-xl"
                    >
                        <div class="border-b border-white/10 px-4 py-3">
                            <p class="text-sm font-semibold text-white">Notifications</p>
                        </div>
                        <div class="max-h-96 overflow-y-auto py-2">
                            <div class="px-4 py-8 text-center">
                                <LearningIcon class="mx-auto size-12 brightness-0 invert opacity-10 mb-3" name="bell"/>
                                <p class="text-sm text-slate-500">Aucune nouvelle notification</p>
                            </div>
                        </div>
                        <div class="border-t border-white/10 p-2 text-center">
                            <button class="text-xs text-[#ef477d] hover:underline" type="button">
                                Tout marquer comme lu
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Profile dropdown -->
            <div class="relative hidden sm:block">
                <button
                    class="flex items-center gap-2 transition hover:opacity-80"
                    type="button"
                    @click="profileMenuOpen = !profileMenuOpen; notificationMenuOpen = false"
                >
                    <img
                        v-if="isAuthenticated"
                        :src="currentUser?.avatar_url ?? '/images/avatar.webp'"
                        alt=""
                        class="size-8 object-cover object-top"
                    />
                    <span v-else class="grid size-8 place-items-center bg-white/10">
                        <LearningIcon class="size-5 brightness-0 invert opacity-60" name="user-circle"/>
                    </span>
                    <span class="text-sm font-medium text-white">{{ accountName }}</span>
                    <LearningIcon
                        :class="profileMenuOpen ? 'rotate-180' : ''"
                        class="size-4 brightness-0 invert opacity-60 transition"
                        name="chevron-down"
                    />
                </button>

                <!-- Dropdown menu -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
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
                                :href="isAuthenticated ? safeRoute('profile.edit') : safeRoute('login')"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                                @click="profileMenuOpen = false"
                            >
                                <LearningIcon class="size-4 brightness-0 invert opacity-60" name="user"/>
                                Mon profil
                            </Link>
                            <Link
                                v-if="isAuthenticated"
                                :href="safeRoute('dashboard')"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                                @click="profileMenuOpen = false"
                            >
                                <LearningIcon class="size-4 brightness-0 invert opacity-60" name="home"/>
                                Tableau de bord
                            </Link>
                        </nav>
                        <div v-if="isAuthenticated" class="border-t border-white/10 py-1">
                            <Link
                                :href="safeRoute('logout')"
                                as="button"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-rose-400 transition hover:bg-white/5 hover:text-rose-300"
                                method="post"
                                @click="profileMenuOpen = false"
                            >
                                <LearningIcon class="size-4 brightness-0 saturate-0 invert opacity-60"
                                              name="arrow-left-start-on-rectangle"/>
                                Se déconnecter
                            </Link>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
