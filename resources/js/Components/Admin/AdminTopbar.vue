<script lang="ts" setup>
import {Link, router, usePage} from '@inertiajs/vue3';
import {ChevronDown, LogOut, Menu, Moon, PanelLeftClose, Sun, UserRound} from '@lucide/vue';
import {computed, ref} from 'vue';
import {useUiStore} from '@/stores';
import {safeRoute} from '@/utilities/route';

defineEmits<{ (e: 'toggle'): void }>();

const page = usePage();
const userMenuOpen = ref(false);
const uiStore = useUiStore();

const user = computed(() => page.props.auth?.user ?? null);
const avatarUrl = computed(() => user.value?.avatar_url ?? null);
const themeLabel = computed(() => uiStore.theme === 'dark' ? 'Activer le thème clair' : 'Activer le thème sombre');

function logout(): void {
    router.post(safeRoute('logout'));
}
</script>

<template>
    <header class="admin-topbar admin-divider sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b px-4 backdrop-blur sm:px-6 lg:px-8">
        <button
            v-if="userMenuOpen"
            aria-label="Fermer le menu du compte"
            class="fixed inset-0 z-30"
            type="button"
            @click="userMenuOpen = false"
        />

        <div class="relative z-40 flex min-w-0 items-center gap-4">
            <button
                aria-label="Ouvrir la navigation"
                class="admin-divider admin-text admin-hover grid size-10 place-items-center border transition lg:hidden"
                type="button"
                @click="$emit('toggle')"
            >
                <Menu class="size-5" :stroke-width="1.7"/>
            </button>
            <button
                :aria-label="uiStore.sidebarCollapsed ? 'Déployer la barre latérale' : 'Réduire la barre latérale'"
                class="admin-divider admin-text admin-hover hidden size-10 place-items-center border transition lg:grid"
                type="button"
                @click="uiStore.toggleSidebarCollapsed"
            >
                <PanelLeftClose
                    :class="uiStore.sidebarCollapsed ? 'rotate-180' : ''"
                    class="size-5 transition"
                    :stroke-width="1.7"
                />
            </button>
            <nav class="hidden items-center gap-2 text-xs text-slate-500 sm:flex" aria-label="Fil d'ariane">
                <span>IRMA Admin</span>
                <span>/</span>
                <slot name="breadcrumb"/>
            </nav>
        </div>

        <div class="relative z-40 ml-auto flex items-center gap-3">
            <slot name="header-actions"/>

            <button
                :aria-label="themeLabel"
                :title="themeLabel"
                class="admin-divider admin-text admin-hover grid size-10 place-items-center border transition"
                type="button"
                @click="uiStore.toggleTheme"
            >
                <Sun v-if="uiStore.theme === 'dark'" class="size-5" :stroke-width="1.7"/>
                <Moon v-else class="size-5" :stroke-width="1.7"/>
            </button>

            <button
                aria-label="Ouvrir le menu du compte"
                :aria-expanded="userMenuOpen"
                class="admin-divider admin-text flex items-center gap-2 border-l pl-3 text-sm transition hover:text-[#a23362]"
                type="button"
                @click="userMenuOpen = !userMenuOpen"
            >
                <img v-if="avatarUrl" :src="avatarUrl" alt="" class="size-9 object-cover object-top"/>
                <span v-else class="grid size-9 place-items-center bg-[#7d254a] text-xs font-bold text-white">
                    {{ (user?.name ?? 'A').charAt(0).toUpperCase() }}
                </span>
                <span class="hidden min-w-0 text-left sm:block">
                    <span class="admin-heading block max-w-32 truncate font-medium">{{ user?.name ?? 'Admin' }}</span>
                    <span class="block text-[10px] uppercase tracking-wide text-slate-500">Administrateur</span>
                </span>
                <ChevronDown :class="userMenuOpen ? 'rotate-180' : ''" class="size-4 text-slate-500 transition"/>
            </button>

            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="translate-y-1 opacity-0"
                leave-active-class="transition duration-100 ease-in"
                leave-to-class="translate-y-1 opacity-0"
            >
                <div
                    v-if="userMenuOpen"
                    class="admin-panel absolute right-0 top-full mt-3 w-56 border py-1 shadow-2xl shadow-black/20"
                    @click="userMenuOpen = false"
                >
                    <div class="admin-divider border-b px-4 py-3 sm:hidden">
                        <p class="admin-heading truncate text-sm font-semibold">{{ user?.name ?? 'Admin' }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">Administrateur</p>
                    </div>
                    <Link
                        :href="safeRoute('profile.edit')"
                        class="admin-text admin-hover flex items-center gap-3 px-4 py-2.5 text-sm transition"
                    >
                        <UserRound class="size-4" :stroke-width="1.7"/>
                        Mon profil
                    </Link>
                    <button
                        class="admin-divider flex w-full items-center gap-3 border-t px-4 py-2.5 text-left text-sm text-rose-500 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-white/5 dark:hover:text-rose-300"
                        type="button"
                        @click="logout"
                    >
                        <LogOut class="size-4" :stroke-width="1.7"/>
                        Se déconnecter
                    </button>
                </div>
            </Transition>
        </div>
    </header>
</template>
