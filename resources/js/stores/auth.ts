import { usePage } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed } from 'vue';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage<{ auth: { user: User } }>();

    const user = computed(() => page.props.auth?.user ?? null);
    const isAuthenticated = computed(() => user.value !== null);

    return { user, isAuthenticated };
});
