<script lang="ts" setup>
import {router} from '@inertiajs/vue3';
import {ref} from 'vue';

const props = withDefaults(
    defineProps<{
        href: string;
        method?: 'post' | 'patch' | 'put' | 'delete';
        title?: string;
        message?: string;
        confirmLabel?: string;
        danger?: boolean;
        data?: Record<string, any>;
    }>(),
    {
        method: 'post',
        title: 'Confirmer l\'action',
        message: 'Êtes-vous sûr ? Cette action est irréversible.',
        confirmLabel: 'Confirmer',
        danger: false,
        data: () => ({}),
    },
);

const open = ref(false);
const processing = ref(false);

function confirm(): void {
    processing.value = true;
    const visit = (router as any)[props.method];
    visit(props.href, props.data, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            open.value = false;
        },
    });
}
</script>

<template>
    <button type="button" @click="open = true">
        <slot/>
    </button>

    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"/>
            <div class="relative w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold text-slate-900">{{ title }}</h3>
                <p class="mt-2 text-sm text-slate-500">{{ message }}</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        class="h-9 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        type="button"
                        @click="open = false"
                    >
                        Annuler
                    </button>
                    <button
                        :class="danger ? 'bg-red-600 hover:bg-red-500' : 'bg-[#bf045b] hover:opacity-90'"
                        :disabled="processing"
                        class="h-9 rounded-lg px-4 text-sm font-semibold text-white transition disabled:opacity-60"
                        type="button"
                        @click="confirm"
                    >
                        {{ processing ? '…' : confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
