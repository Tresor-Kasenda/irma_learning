<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import {Eye, EyeOff} from '@lucide/vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
const showPassword = ref(false);
const page = usePage();
const allowRegistration = computed(() => Boolean((page.props.appSettings as {allow_registration?: boolean})?.allow_registration));
const logoUrl = computed(() => (page.props.appSettings as {logo_url?: string})?.logo_url ?? '/images/irma-logo-base.svg');
const appSettings = computed(() => (page.props.appSettings as Record<string, unknown>) ?? {});
const appName = computed(() => (appSettings.value.name as string) ?? '');
const authPageSubtitle = computed(() => (appSettings.value.auth_page_subtitle as string) ?? '');
const authLoginSubtitle = computed(() => (appSettings.value.auth_login_subtitle as string) ?? '');

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
                    <img :src="logoUrl" :alt="`Logo ${appName}`"
                        class="h-16 w-auto mb-5 mx-auto" />
                </Link>

                <div class="text-center">
                    <h1 class="text-gray-900 mb-1 text-xl font-semibold">{{ appName ? `Bienvenue sur ${appName}` : authPageSubtitle }}</h1>
                    <p class="text-sm text-gray-500">{{ authLoginSubtitle }}</p>
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
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password" class="text-sm font-medium text-gray-700">Mot de passe</label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-11 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                            />
                            <button
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                class="absolute inset-y-0 right-0 grid w-11 place-items-center text-gray-500 transition hover:text-irma-primary"
                                type="button"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-4"/>
                                <Eye v-else class="size-4"/>
                            </button>
                        </div>
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

            <div v-if="allowRegistration" class="bg-gray-50 rounded px-5 sm:px-6 py-4">
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
