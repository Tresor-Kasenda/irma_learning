<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ status?: string }>();

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const form = useForm({});

const submit = () => { form.post(route('verification.send')); };
</script>

<template>
    <GuestLayout>
        <Head title="Vérification de l'email" />

        <div class="border border-gray-200/40 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">
            <div class="p-5 sm:p-8">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-irma-primary/10">
                        <svg class="h-8 w-8 text-irma-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <h1 class="text-gray-900 mb-1 text-xl font-semibold">Vérifiez votre email</h1>
                    <p class="text-sm text-gray-600 mt-2">
                        Merci pour votre inscription ! Avant de commencer, veuillez vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer.
                    </p>
                </div>

                <div v-if="verificationLinkSent" class="mt-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700 text-center">
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </div>

                <hr class="my-6 border-gray-300/60" />

                <div class="flex flex-col gap-4">
                    <form @submit.prevent="submit">
                        <button type="submit" :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            {{ form.processing ? 'Envoi...' : "Renvoyer l'email de vérification" }}
                        </button>
                    </form>

                    <Link :href="route('logout')" method="post" as="button"
                        class="text-center text-sm text-gray-500 hover:text-gray-700 underline">
                        Se déconnecter
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
