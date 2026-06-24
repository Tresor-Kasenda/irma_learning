<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-white">
                Supprimer le compte
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Avant
                de supprimer votre compte, veuillez télécharger les données ou informations que vous souhaitez conserver.
            </p>
        </header>

        <button
            type="button"
            @click="confirmUserDeletion"
            class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-500"
        >
            Supprimer le compte
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="bg-[#0e2035] p-6">
                <h2 class="text-lg font-medium text-white">
                    Êtes-vous sûr de vouloir supprimer votre compte ?
                </h2>

                <p class="mt-1 text-sm text-slate-400">
                    Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées.
                    Veuillez entrer votre mot de passe pour confirmer la suppression définitive de votre compte.
                </p>

                <div class="mt-6">
                    <label for="password" class="sr-only">Mot de passe</label>

                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Mot de passe"
                        @keyup.enter="deleteUser"
                        class="block w-3/4 rounded-md border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="inline-flex items-center justify-center rounded-md border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                    >
                        Annuler
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        :class="{ 'opacity-60': form.processing }"
                        @click="deleteUser"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-500"
                    >
                        Supprimer le compte
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
