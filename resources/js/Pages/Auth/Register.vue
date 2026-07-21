<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const page = usePage();
const logoUrl = computed(() => (page.props.appSettings as {logo_url?: string})?.logo_url ?? '/images/irma-logo-base.svg');
const appSettings = computed(() => (page.props.appSettings as Record<string, unknown>) ?? {});
const appName = computed(() => (appSettings.value.name as string) ?? '');
const authRegisterTitle = computed(() => ((appSettings.value.auth_register_title as string) ?? '').replace('{app_name}', appName.value));
const authRegisterSubtitle = computed(() => (appSettings.value.auth_register_subtitle as string) ?? '');

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let score = 0;
    if (p.length >= 8) score++;
    if (p.length >= 12) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[a-z]/.test(p)) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[^a-zA-Z0-9]/.test(p)) score++;
    return Math.min(5, score);
});

const strengthLabel = computed(() => ['', 'Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'][passwordStrength.value] ?? '');
const strengthWidth = computed(() => ['w-0', 'w-1/5', 'w-2/5', 'w-3/5', 'w-4/5', 'w-full'][passwordStrength.value] ?? 'w-0');
const strengthColor = computed(() => passwordStrength.value <= 2 ? 'bg-red-500' : passwordStrength.value <= 4 ? 'bg-yellow-500' : 'bg-green-500');
const strengthTextColor = computed(() => passwordStrength.value === 0 ? 'text-gray-400' : passwordStrength.value <= 2 ? 'text-red-500' : passwordStrength.value <= 4 ? 'text-yellow-500' : 'text-green-500');

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Inscription" />

        <form @submit.prevent="submit"
            class="border border-gray-200/60 w-full max-w-md p-1 shadow-lg shadow-gray-200/40 bg-white rounded-lg">

            <div class="p-5 sm:p-8">
                <Link :href="route('home-page')" class="block">
                    <img :src="logoUrl" :alt="`Logo ${appName}`" class="h-16 w-auto mb-5 mx-auto" />
                </Link>

                <div class="text-center mt-2">
                    <h1 class="text-gray-900 mb-1 text-xl font-semibold">{{ authRegisterTitle }}</h1>
                    <p class="text-sm text-gray-500">{{ authRegisterSubtitle }}</p>
                </div>

                <hr class="my-4 border-gray-300/60" />

                <div class="space-y-5">
                    <div class="flex flex-col gap-1">
                        <label for="name" class="text-sm font-medium text-gray-700">Nom complet</label>
                        <input id="name" v-model="form.name" type="text" name="name" placeholder="Votre nom"
                            required autofocus autocomplete="name"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="username" class="text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                        <input id="username" v-model="form.username" type="text" name="username"
                            placeholder="Votre nom d'utilisateur" required autocomplete="username"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary" />
                        <InputError :message="form.errors.username" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                        <input id="email" v-model="form.email" type="email" name="email"
                            placeholder="Ex: users@example.com" required autocomplete="email"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="password" class="text-sm font-medium text-gray-700">Mot de passe</label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                name="password" placeholder="Entrez votre mot de passe" required autocomplete="new-password"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary" />
                            <button type="button" tabindex="-1" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-1">
                            <div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-1 transition-all duration-300" :class="[strengthWidth, strengthColor]" />
                            </div>
                            <p v-if="form.password" class="text-xs mt-1" :class="strengthTextColor">{{ strengthLabel }}</p>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                        <div class="relative">
                            <input id="password_confirmation" v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation"
                                placeholder="Confirmez votre mot de passe" required autocomplete="new-password"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 caret-irma-primary shadow-sm placeholder:text-gray-400 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary" />
                            <button type="button" tabindex="-1" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        {{ form.processing ? 'Inscription...' : "S'inscrire" }}
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 rounded px-5 sm:px-6 py-4">
                <p class="text-center text-sm text-gray-600">
                    Vous avez déjà un compte ?
                    <Link :href="route('login')" class="font-medium text-irma-primary hover:opacity-80">Se connecter</Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
