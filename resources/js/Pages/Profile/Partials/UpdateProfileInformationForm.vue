<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-white">
                Informations du profil
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Mettez à jour les informations de votre compte et votre adresse e-mail.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div class="flex flex-col gap-1">
                <label for="name" class="text-sm font-medium text-slate-200">Nom</label>

                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Votre nom"
                    class="block w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                />

                <InputError :message="form.errors.name" />
            </div>

            <div class="flex flex-col gap-1">
                <label for="email" class="text-sm font-medium text-slate-200">Email</label>

                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="Ex: users@example.com"
                    class="block w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm text-slate-300">
                    Votre adresse e-mail n'est pas vérifiée.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-slate-400 underline hover:text-white focus:outline-none"
                    >
                        Cliquez ici pour renvoyer l'e-mail de vérification.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-400"
                >
                    Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60"
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
