<script lang="ts" setup>
import {useForm} from '@inertiajs/vue3';
import {nextTick, ref} from 'vue';
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";

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
                de supprimer votre compte, veuillez télécharger les données ou informations que vous souhaitez
                conserver.
            </p>
        </header>

        <button
            class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-500"
            type="button"
            @click="confirmUserDeletion"
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
                    <label class="sr-only" for="password">Mot de passe</label>

                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        class="block w-3/4 rounded-md border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-irma-primary focus:outline-none focus:ring-1 focus:ring-irma-primary"
                        placeholder="Mot de passe"
                        type="password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2"/>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        class="inline-flex items-center justify-center rounded-md border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                        type="button"
                        @click="closeModal"
                    >
                        Annuler
                    </button>

                    <button
                        :class="{ 'opacity-60': form.processing }"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-500"
                        type="button"
                        @click="deleteUser"
                    >
                        Supprimer le compte
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
