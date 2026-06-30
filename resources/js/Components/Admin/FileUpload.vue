<script lang="ts" setup>
import {computed, onBeforeUnmount, ref, watch} from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        accept?: string;
        label?: string;
        currentUrl?: string | null;
        currentName?: string | null;
        progress?: number | null;
        error?: string;
        hint?: string;
        maxSizeMb?: number;
    }>(),
    {
        accept: 'image/*',
        maxSizeMb: 50,
    },
);

const emit = defineEmits<{ (e: 'update:modelValue', value: File | null): void }>();

const inputRef = ref<HTMLInputElement | null>(null);
const dragging = ref(false);
const localError = ref<string | null>(null);
const objectUrl = ref<string | null>(null);

watch(
    () => props.modelValue,
    (file) => {
        if (objectUrl.value) {
            URL.revokeObjectURL(objectUrl.value);
            objectUrl.value = null;
        }
        if (file && file.type.startsWith('image/')) {
            objectUrl.value = URL.createObjectURL(file);
        }
    },
);

onBeforeUnmount(() => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
    }
});

const previewImage = computed(() => objectUrl.value ?? (props.modelValue ? null : props.currentUrl ?? null));
const fileKind = computed(() => {
    const type = props.modelValue?.type ?? '';
    if (type.startsWith('image/')) {
        return 'image';
    }
    if (type.startsWith('video/')) {
        return 'video';
    }
    if (type === 'application/pdf') {
        return 'pdf';
    }

    return 'file';
});

function formatSize(bytes: number): string {
    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} Ko`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;
}

function pickFile(file: File | undefined | null): void {
    localError.value = null;

    if (!file) {
        return;
    }
    if (props.maxSizeMb && file.size > props.maxSizeMb * 1024 * 1024) {
        localError.value = `Le fichier dépasse ${props.maxSizeMb} Mo.`;

        return;
    }

    emit('update:modelValue', file);
}

function onDrop(event: DragEvent): void {
    dragging.value = false;
    pickFile(event.dataTransfer?.files?.[0]);
}

function onChange(event: Event): void {
    pickFile((event.target as HTMLInputElement).files?.[0]);
}

function clear(): void {
    emit('update:modelValue', null);
    localError.value = null;
    if (inputRef.value) {
        inputRef.value.value = '';
    }
}

function browse(): void {
    inputRef.value?.click();
}

const uploading = computed(() => props.progress !== null && props.progress !== undefined && props.progress < 100);
</script>

<template>
    <div>
        <span v-if="label" class="mb-1 block text-sm font-medium text-slate-700">{{ label }}</span>

        <!-- Fichier sélectionné -->
        <div v-if="modelValue || currentUrl" class="rounded-lg border border-slate-200 bg-slate-50 p-3">
            <div class="flex items-center gap-3">
                <img v-if="previewImage" :src="previewImage" alt="" class="size-14 shrink-0 rounded-md object-cover"/>
                <span v-else class="grid size-14 shrink-0 place-items-center rounded-md bg-white text-slate-400">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path v-if="fileKind === 'video'" d="M15 10l4.5-2.5v9L15 14M4 6h11v12H4z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path v-else-if="fileKind === 'pdf'" d="M7 3h7l5 5v13H7zM14 3v5h5M9 13h6M9 17h4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path v-else d="M7 3h7l5 5v13H7zM14 3v5h5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-slate-700">
                        {{ modelValue?.name ?? currentName ?? 'Fichier actuel' }}
                    </p>
                    <p v-if="modelValue" class="text-xs text-slate-400">{{ formatSize(modelValue.size) }}</p>
                    <p v-else class="text-xs text-slate-400">Fichier déjà enregistré</p>
                </div>
                <button
                    class="grid size-8 place-items-center rounded-md text-slate-400 hover:bg-red-50 hover:text-red-500"
                    title="Retirer"
                    type="button"
                    @click="clear"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <!-- Progression -->
            <div v-if="uploading" class="mt-3">
                <div class="mb-1 flex justify-between text-xs text-slate-500">
                    <span>Envoi en cours…</span>
                    <span>{{ progress }}%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-slate-200">
                    <div :style="{ width: `${progress}%` }" class="h-full bg-[#bf045b] transition-[width]"/>
                </div>
            </div>

            <button
                class="mt-2 text-xs font-medium text-[#bf045b] hover:underline"
                type="button"
                @click="browse"
            >
                Remplacer le fichier
            </button>
        </div>

        <!-- Zone de dépôt -->
        <button
            v-else
            :class="dragging ? 'border-[#bf045b] bg-[#bf045b]/5' : 'border-slate-300 hover:border-slate-400'"
            class="flex w-full flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-8 text-center transition"
            type="button"
            @click="browse"
            @dragenter.prevent="dragging = true"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <svg class="size-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-sm font-medium text-slate-600">
                Glissez-déposez un fichier, ou <span class="text-[#bf045b]">parcourir</span>
            </span>
            <span class="text-xs text-slate-400">Max {{ maxSizeMb }} Mo</span>
        </button>

        <input
            ref="inputRef"
            :accept="accept"
            class="hidden"
            type="file"
            @change="onChange"
        />

        <p v-if="localError || error" class="mt-1 text-xs text-red-600">{{ localError ?? error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-400">{{ hint }}</p>
    </div>
</template>
