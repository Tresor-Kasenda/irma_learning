<script lang="ts" setup>
import {useForm} from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {safeRoute} from '@/utilities/route';

interface AccessFormation {
    id: number;
    slug: string;
    title: string;
    short_description?: string | null;
    image?: string | null;
}

const props = defineProps<{
    formation: AccessFormation;
}>();

const form = useForm({
    code: '',
});

function submit(): void {
    form.post(safeRoute('student.formations.validate-code', props.formation.id), {
        onFinish: () => form.reset('code'),
    });
}
</script>

<template>
    <PublicLayout :title="`Accéder à ${formation.title}`">
        <div class="mx-auto flex min-h-[70vh] max-w-7xl items-center justify-center px-5 py-28">
            <div class="w-full max-w-md rounded-lg border border-gray-200/60 bg-white p-1 shadow-lg shadow-gray-200/40">
                <div class="p-6 sm:p-8">
                    <h1 class="text-xl font-semibold text-gray-900">Accéder à la formation</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Saisissez le code d'accès reçu pour rejoindre
                        <span class="font-medium text-gray-700">{{ formation.title }}</span>.
                    </p>

                    <form class="mt-6 space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="code">
                                Code d'accès
                            </label>
                            <input
                                id="code"
                                v-model="form.code"
                                autocomplete="off"
                                autofocus
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-irma-primary focus:ring-irma-primary"
                                placeholder="Entrez votre code d'accès"
                                type="text"
                            />
                            <InputError :message="form.errors.code" class="mt-2"/>
                        </div>

                        <button
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center rounded-md bg-irma-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                            type="submit"
                        >
                            {{ form.processing ? 'Validation en cours…' : 'Valider' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
