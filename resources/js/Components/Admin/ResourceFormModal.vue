<script lang="ts" setup>
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
            <div class="absolute inset-0 bg-slate-900/50" @click="emit('close')"/>

            <form
                :class="slideOver ? 'h-full w-full max-w-xl' : 'w-full max-w-2xl rounded-xl'"
                class="relative flex flex-col overflow-hidden border border-slate-200 bg-white shadow-xl"
                @submit.prevent="emit('submit')"
            >
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">{{ title }}</h2>
                    <button
                        class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        type="button"
                        @click="emit('close')"
                    >
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                    <slot/>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                    <button
                        class="h-10 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        type="button"
                        @click="emit('close')"
                    >
                        Annuler
                    </button>
                    <button
                        :disabled="processing"
                        class="h-10 rounded-lg bg-[#bf045b] px-5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        type="submit"
                    >
                        {{ processing ? 'Enregistrement…' : submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </Teleport>
</template>
