<script lang="ts" setup>
import {router} from '@inertiajs/vue3';
import {TriangleAlert, X} from '@lucide/vue';
import {ref, useAttrs} from 'vue';

defineOptions({inheritAttrs: false});

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
const attrs = useAttrs();

function confirm(): void {
    processing.value = true;

    router.visit(props.href, {
        method: props.method,
        data: props.data,
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            open.value = false;
        },
    });
}
</script>

<template>
    <button v-bind="attrs" type="button" @click="open = true">
        <slot/>
    </button>

    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <button aria-label="Fermer la confirmation" class="absolute inset-0 bg-black/70" type="button" @click="open = false"/>
            <div class="admin-panel relative w-full max-w-md border p-6 shadow-2xl shadow-black/30">
                <button aria-label="Fermer" class="admin-muted admin-hover absolute right-4 top-4 grid size-8 place-items-center" type="button" @click="open = false">
                    <X class="size-4"/>
                </button>
                <span :class="danger ? 'bg-rose-400/10 text-rose-300' : 'bg-amber-400/10 text-amber-300'" class="grid size-10 place-items-center">
                    <TriangleAlert class="size-5" :stroke-width="1.7"/>
                </span>
                <h3 class="admin-heading mt-4 text-base font-semibold">{{ title }}</h3>
                <p class="admin-muted mt-2 text-sm leading-6">{{ message }}</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        class="admin-divider admin-text admin-hover h-10 border px-4 text-sm font-medium transition"
                        type="button"
                        @click="open = false"
                    >
                        Annuler
                    </button>
                    <button
                        :class="danger ? 'bg-rose-600 hover:bg-rose-500' : 'bg-[#a23362] hover:bg-[#b2386e]'"
                        :disabled="processing"
                        class="h-10 px-4 text-sm font-semibold text-white transition disabled:opacity-60"
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
