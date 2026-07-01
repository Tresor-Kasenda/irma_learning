<script lang="ts" setup>
import {X} from '@lucide/vue';

withDefaults(
    defineProps<{
        show: boolean;
        title: string;
        processing?: boolean;
        submitLabel?: string;
        slideOver?: boolean;
    }>(),
    {
        processing: false,
        submitLabel: 'Enregistrer',
        slideOver: true,
    },
);

const emit = defineEmits<{ (e: 'close'): void; (e: 'submit'): void }>();
</script>

<template>
    <Teleport to="body">
        <div v-if="show" :class="slideOver ? 'justify-end' : 'items-center justify-center p-4'"
             class="fixed inset-0 z-60 flex">
            <button aria-label="Fermer le formulaire" class="absolute inset-0 bg-black/70" type="button" @click="emit('close')"/>

            <form
                :class="slideOver ? 'h-full w-full max-w-xl' : 'w-full max-w-2xl'"
                class="admin-panel admin-text relative flex flex-col overflow-hidden border shadow-2xl shadow-black/30"
                @submit.prevent="emit('submit')"
            >
                <div class="admin-divider flex items-center justify-between border-b px-6 py-4">
                    <h2 class="admin-heading text-base font-semibold">{{ title }}</h2>
                    <button
                        aria-label="Fermer"
                        class="admin-muted admin-hover grid size-8 place-items-center transition"
                        type="button"
                        @click="emit('close')"
                    >
                        <X class="size-5"/>
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <slot/>
                </div>

                <div class="admin-divider flex justify-end gap-2 border-t px-6 py-4">
                    <button
                        class="admin-divider admin-text admin-hover h-10 border px-4 text-sm font-medium transition"
                        type="button"
                        @click="emit('close')"
                    >
                        Annuler
                    </button>
                    <button
                        :disabled="processing"
                        class="h-10 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e] disabled:opacity-60"
                        type="submit"
                    >
                        {{ processing ? 'Enregistrement…' : submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </Teleport>
</template>
