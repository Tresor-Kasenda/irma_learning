<script lang="ts" setup>
import {Link, router, usePage} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import {safeRoute} from '@/utilities/route';

defineEmits<{ (e: 'toggle'): void }>();

const page = usePage();
const userMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user ?? null);

function logout(): void {
    router.post(safeRoute('logout'));
}
</script>

<template>
    <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button
                aria-label="Ouvrir la navigation"
                class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-600 lg:hidden"
                type="button"
                @click="$emit('toggle')"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                </svg>
            </button>
            <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Fil d'ariane">
                <slot name="breadcrumb"/>
            </nav>
        </div>

        <div class="relative">
            <button
                class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100"
                type="button"
                @click="userMenuOpen = !userMenuOpen"
            >
                <span class="grid size-8 place-items-center rounded-full bg-[#bf045b]/10 text-xs font-bold text-[#bf045b]">
                    {{ (user?.name ?? 'A').charAt(0).toUpperCase() }}
                </span>
                <span class="hidden font-medium sm:inline">{{ user?.name ?? 'Admin' }}</span>
                <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-48 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                @click="userMenuOpen = false"
            >
                <Link :href="safeRoute('profile.edit')" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">
                    Profil
                </Link>
                <button
                    class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                    type="button"
                    @click="logout"
                >
                    Déconnexion
                </button>
            </div>
        </div>
    </header>
</template>
