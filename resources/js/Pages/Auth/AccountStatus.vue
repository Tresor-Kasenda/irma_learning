<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    status: 'inactive' | 'suspended' | 'invalid-certificate' | 'expired-certificate'
    message?: string
}>();

const messages: Record<string, { title: string; description: string; icon: string }> = {
    inactive: {
        title: 'Compte inactif',
        description: 'Votre compte est actuellement inactif. Veuillez contacter le support pour réactiver votre accès.',
        icon: 'alert',
    },
    suspended: {
        title: 'Compte suspendu',
        description: 'Votre compte a été suspendu. Veuillez contacter le support pour plus d\'informations.',
        icon: 'alert',
    },
    'invalid-certificate': {
        title: 'Certificat introuvable',
        description: 'Ce certificat est introuvable. Vérifiez le lien de vérification.',
        icon: 'search',
    },
    'expired-certificate': {
        title: 'Certificat non valide',
        description: 'Ce certificat a expiré ou a été révoqué.',
        icon: 'alert',
    },
};
</script>

<template>
    <GuestLayout>
        <Head :title="messages[status]?.title ?? 'Information'" />

        <div class="border border-gray-200/40 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
            <div class="p-5 sm:p-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <svg v-if="messages[status]?.icon === 'search'" class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <svg v-else class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>

                <h1 class="text-gray-900 mb-2 text-xl font-semibold">{{ messages[status]?.title ?? 'Information' }}</h1>
                <p class="text-sm text-gray-600 mb-6">{{ message ?? messages[status]?.description }}</p>

                <Link href="/" class="inline-flex items-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90">
                    Retour à l'accueil
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
