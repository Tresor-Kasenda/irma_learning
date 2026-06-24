<script lang="ts" setup>
import {Link, usePage} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

const props = defineProps<{
    mobileSidebarOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:mobileSidebarOpen', value: boolean): void;
}>();

const page = usePage();
const profileMenuOpen = ref(false);

const currentUser = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => Boolean(currentUser.value));
const accountName = computed(() => currentUser.value?.name ?? 'Visiteur');
const accountRole = computed(() => (isAuthenticated.value ? 'Apprenant' : 'Compte invité'));

const toggleMobileSidebar = () => {
    emit('update:mobileSidebarOpen', !props.mobileSidebarOpen);
};
</script>

<template>
    <header class="flex h-16 items-center justify-between border-b border-white/10 px-4 sm:px-6 lg:px-8">
        <!-- Profile menu backdrop -->
        <button
            v-if="profileMenuOpen"
            aria-label="Fermer le menu profil"
            class="fixed inset-0 z-30"
            type="button"
            @click="profileMenuOpen = false"
        />

        <button
            aria-label="Ouvrir la navigation"
            class="grid size-10 place-items-center border border-white/10 lg:hidden"
            type="button"
            @click="toggleMobileSidebar"
        >
            <LearningIcon class="size-5 brightness-0 invert" name="bars-3"/>
        </button>
        <div class="hidden items-center gap-2 text-xs text-slate-500 sm:flex">
            <span>IRMA Learning</span>
            <span>/</span>
            <slot name="breadcrumb">
                <span class="text-slate-300">Espace d'apprentissage</span>
            </slot>
        </div>
        <div class="ml-auto flex items-center gap-3">
            <slot name="header-actions"/>
            <button
                class="relative grid size-10 place-items-center border border-white/10 text-slate-300 transition hover:bg-white/5"
                title="Notifications"
                type="button"
            >
                <LearningIcon class="size-5 brightness-0 invert opacity-80" name="bell"/>
                <span class="absolute right-1.5 top-1.5 size-2 bg-[#ef477d]"/>
            </button>

            <!-- Profile dropdown -->
            <div class="relative hidden sm:block">
                <button
                    class="flex items-center gap-2 transition hover:opacity-80"
                    type="button"
                    @click="profileMenuOpen = !profileMenuOpen"
                >
                    <img
                        v-if="isAuthenticated"
                        alt=""
                        class="size-8 object-cover object-top"
                        src="/images/avatar.webp"
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
                            <LearningIcon class="size-4 brightness-0 invert opacity-60" name="user"/>
                            Mon profil
                        </Link>
                        <Link
                            v-if="isAuthenticated"
                            :href="route('dashboard')"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                            @click="profileMenuOpen = false"
                        >
                            <LearningIcon class="size-4 brightness-0 invert opacity-60" name="home"/>
                            Tableau de bord
                        </Link>
                    </nav>
                    <div v-if="isAuthenticated" class="border-t border-white/10 py-1">
                        <Link
                            :href="route('logout')"
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
            </div>
        </div>
    </header>
</template>
