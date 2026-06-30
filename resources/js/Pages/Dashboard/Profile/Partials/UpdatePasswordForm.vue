<script lang="ts" setup>
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import InputError from "@/Components/InputError.vue";

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-white">
                Mot de passe
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Assurez-vous d'utiliser un mot de passe long et aléatoire pour rester en sécurité.
            </p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="updatePassword">
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-slate-200" for="current_password">Mot de passe actuel</label>

                <div class="relative">
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        class="block w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 pr-10 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        placeholder="Votre mot de passe actuel"
                    />
                    <button
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300"
                        tabindex="-1"
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                    >
                        <svg v-if="!showCurrentPassword" class="h-5 w-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"/>
                        </svg>
                    </button>
                </div>

                <InputError :message="form.errors.current_password"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-slate-200" for="password">Nouveau mot de passe</label>

                <div class="relative">
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        class="block w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 pr-10 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        placeholder="Entrez votre nouveau mot de passe"
                    />
                    <button
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300"
                        tabindex="-1"
                        type="button"
                        @click="showPassword = !showPassword"
                    >
                        <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"/>
                        </svg>
                    </button>
                </div>

                <InputError :message="form.errors.password"/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-slate-200" for="password_confirmation">Confirmer le mot de
                    passe</label>

                <div class="relative">
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        autocomplete="new-password"
                        class="block w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 pr-10 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        placeholder="Confirmez votre nouveau mot de passe"
                    />
                    <button
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300"
                        tabindex="-1"
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                    >
                        <svg v-if="!showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"/>
                        </svg>
                    </button>
                </div>

                <InputError :message="form.errors.password_confirmation"/>
            </div>

            <div class="flex items-center gap-4">
                <button
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60"
                    type="submit"
                >
                    Enregistrer
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-slate-400"
                    >
                        Enregistré.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
