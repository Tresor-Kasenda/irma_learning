<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ status?: string }>();

const form = useForm({ email: '' });

const submit = () => { form.post(route('password.email')); };
</script>

<template>
    <GuestLayout>
        <Head title="Mot de passe oublié" />

        <form @submit.prevent="submit"
            class="border border-gray-200/40 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">

            <div class="p-5 sm:p-8">
                <Link :href="route('home-page')" class="block">
                    <img src="/images/irma-logo-base.svg" alt="logo Irma" class="h-16 w-auto mb-5 mx-auto" />
                </Link>

                <div class="text-center">
                    <h1 class="text-gray-900 mb-1 text-xl font-semibold">Mot de passe oublié ?</h1>
                    <p class="text-sm text-gray-600">
                        Pas de souci. Indiquez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                    </p>
                </div>

                <div v-if="status" class="mt-4 text-sm font-medium text-green-600 text-center">
                    {{ status }}
                </div>

                <hr class="my-8 border-gray-300/60" />

                <div class="space-y-6">
                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                        <input id="email" v-model="form.email" type="email" name="email"
                            placeholder="Ex: users@example.com" required autofocus autocomplete="username"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        {{ form.processing ? 'Envoi...' : 'Envoyer le lien de réinitialisation' }}
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 rounded px-5 sm:px-6 py-4">
                <p class="text-center text-sm text-gray-600">
                    Retourner à la
                    <Link :href="route('login')" class="font-medium text-irma-primary hover:opacity-80">connexion</Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
