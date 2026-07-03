<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    certificate: {
        certificate_number: string;
        holder_name: string;
        formation_title: string;
        issue_date: string;
        final_score: number | string | null;
        valid: boolean;
    };
}>();

const score = computed(() => Math.round(Number(props.certificate.final_score ?? 0)));
</script>

<template>
    <GuestLayout>
        <Head title="Vérification de certificat" />

        <div class="border border-gray-200/40 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
            <div class="p-5 sm:p-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h1 class="text-gray-900 mb-1 text-xl font-semibold">Certificat vérifié</h1>
                <p class="text-sm text-emerald-600 font-medium mb-6">Ce certificat est authentique et valide</p>

                <div class="space-y-3 text-left bg-gray-50 rounded-lg p-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Titulaire</p>
                        <p class="text-sm font-semibold text-gray-900">{{ certificate.holder_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Formation</p>
                        <p class="text-sm font-semibold text-gray-900">{{ certificate.formation_title }}</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Délivré le</p>
                            <p class="text-sm font-semibold text-gray-900">{{ certificate.issue_date }}</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Score</p>
                            <p class="text-sm font-semibold text-emerald-600">{{ score }}%</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">N° de certificat</p>
                        <p class="text-sm font-mono text-gray-900">{{ certificate.certificate_number }}</p>
                    </div>
                </div>

                <Link href="/" class="mt-6 inline-flex items-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90">
                    Retour à l'accueil
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
