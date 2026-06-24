<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <form @submit.prevent="submit"
            class="border border-gray-200/60 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">

            <div class="p-5 sm:p-8">
                <Link :href="route('home-page')" class="block">
                    <img src="/images/irma-logo-base.svg" alt="logo Irma"
                        class="h-16 w-auto mb-5 mx-auto" />
                </Link>

                <div class="text-center">
                    <h1 class="text-gray-900 mb-1 text-xl font-semibold">Bienvenue sur Irma</h1>
                    <p class="text-sm text-gray-500">Identifiez-vous pour accéder à votre compte</p>
                </div>

                <div v-if="status" class="mt-4 text-sm font-medium text-green-600 text-center">
                    {{ status }}
                </div>

                <hr class="my-8 border-gray-300/60" />

                <div class="space-y-6">
                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            name="email"
                            placeholder="Ex: users@example.com"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password" class="text-sm font-medium text-gray-700">Mot de passe</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        />
                        <InputError :message="form.errors.password" />

                        <div class="flex justify-between w-full pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-gray-300 text-irma-primary focus:ring-irma-primary"
                                />
                                <span class="text-sm text-gray-600">Se souvenir de moi</span>
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-irma-primary hover:opacity-80"
                            >
                                Mot de passe oublié ?
                            </Link>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 rounded px-5 sm:px-6 py-4">
                <p class="text-center text-sm text-gray-600">
                    Vous n'avez pas de compte ?
                    <Link :href="route('register')" class="font-medium text-irma-primary hover:opacity-80">
                        S'inscrire
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
