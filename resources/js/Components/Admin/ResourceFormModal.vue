<script lang="ts" setup>
import {X} from '@lucide/vue';
import {computed, onBeforeUnmount, onMounted, watch} from 'vue';

const props = withDefaults(
    defineProps<{
        show: boolean;
        title: string;
        processing?: boolean;
        submitLabel?: string;
        slideOver?: boolean;
        size?: 'md' | 'lg' | 'xl';
    }>(),
    {
        processing: false,
        submitLabel: 'Enregistrer',
        slideOver: true,
        size: 'lg',
    },
);

const emit = defineEmits<{ (e: 'close'): void; (e: 'submit'): void }>();

const widthClass = computed(() => {
    if (props.slideOver) {
        return props.size === 'xl' ? 'h-full w-full max-w-3xl' : 'h-full w-full max-w-xl';
    }

    return {
        md: 'w-full max-w-2xl',
        lg: 'w-full max-w-4xl',
        xl: 'w-full max-w-6xl',
    }[props.size];
});

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape' && props.show && !props.processing) {
        emit('close');
    }
}

watch(() => props.show, (show) => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = show ? 'hidden' : '';
    }
}, {immediate: true});

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" :class="slideOver ? 'justify-end' : 'items-center justify-center p-3 sm:p-5'"
             class="fixed inset-0 z-60 flex">
            <button aria-label="Fermer le formulaire" class="absolute inset-0 bg-black/70" type="button" @click="emit('close')"/>

            <form
                :aria-busy="processing"
                aria-modal="true"
                :class="widthClass"
                class="admin-panel admin-text relative flex max-h-[94vh] min-w-0 flex-col overflow-hidden border shadow-2xl shadow-black/30"
                role="dialog"
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

                <div class="flex-1 min-w-0 space-y-4 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">
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
