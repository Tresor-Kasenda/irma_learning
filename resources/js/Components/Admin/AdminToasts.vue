<script lang="ts" setup>
import {CheckCircle2, Info, TriangleAlert, X} from '@lucide/vue';
import type {AdminToastType} from '@/utilities/toast';

export interface AdminToastMessage {
    id: number;
    type: AdminToastType;
    message: string;
}

defineProps<{
    toasts: AdminToastMessage[];
}>();

defineEmits<{
    (event: 'dismiss', id: number): void;
}>();

const toastClasses: Record<AdminToastType, string> = {
    success: 'border-emerald-400/30 bg-emerald-50 text-emerald-950 dark:bg-[#0d2926] dark:text-emerald-100',
    error: 'border-rose-400/30 bg-rose-50 text-rose-950 dark:bg-[#321725] dark:text-rose-100',
    info: 'border-sky-400/30 bg-sky-50 text-sky-950 dark:bg-[#10283b] dark:text-sky-100',
};
</script>

<template>
    <div class="pointer-events-none fixed right-4 top-20 z-[80] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-6 sm:w-full" aria-live="polite">
        <TransitionGroup
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-x-4 opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="translate-x-4 opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="toastClasses[toast.type]"
                class="pointer-events-auto flex items-start gap-3 border px-4 py-3 shadow-2xl shadow-slate-950/15"
                role="status"
            >
                <CheckCircle2 v-if="toast.type === 'success'" class="mt-0.5 size-5 shrink-0 text-emerald-500" :stroke-width="1.8"/>
                <TriangleAlert v-else-if="toast.type === 'error'" class="mt-0.5 size-5 shrink-0 text-rose-500" :stroke-width="1.8"/>
                <Info v-else class="mt-0.5 size-5 shrink-0 text-sky-500" :stroke-width="1.8"/>
                <p class="min-w-0 flex-1 text-sm font-medium leading-5">{{ toast.message }}</p>
                <button
                    :aria-label="`Fermer la notification : ${toast.message}`"
                    class="grid size-6 shrink-0 place-items-center opacity-60 transition hover:bg-black/5 hover:opacity-100 dark:hover:bg-white/10"
                    type="button"
                    @click="$emit('dismiss', toast.id)"
                >
                    <X class="size-4"/>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
