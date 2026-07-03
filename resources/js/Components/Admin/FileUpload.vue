<script lang="ts" setup>
import {File, FileText, ImagePlus, RefreshCw, Trash2, Video} from '@lucide/vue';
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

const fileKind = computed(() => {
    const type = props.modelValue?.type ?? '';
    const currentFile = (props.currentName ?? props.currentUrl ?? '').toLowerCase();

    if (type.startsWith('image/')) {
        return 'image';
    }
    if (type.startsWith('video/')) {
        return 'video';
    }
    if (type === 'application/pdf') {
        return 'pdf';
    }
    if (props.accept?.includes('video') || /\.(mp4|webm|ogg|mov)$/.test(currentFile)) {
        return 'video';
    }
    if (props.accept?.includes('pdf') || currentFile.endsWith('.pdf')) {
        return 'pdf';
    }
    if (props.accept?.includes('image') || /\.(jpe?g|png|webp|gif)$/.test(currentFile)) {
        return 'image';
    }

    return 'file';
});
const previewImage = computed(() => fileKind.value === 'image'
    ? objectUrl.value ?? (props.modelValue ? null : props.currentUrl ?? null)
    : null);
const hasFile = computed(() => Boolean(props.modelValue || props.currentUrl || props.currentName));

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

const normalizedProgress = computed(() => Math.min(100, Math.max(0, Math.round(props.progress ?? 0))));
const uploading = computed(() => props.progress !== null && props.progress !== undefined && normalizedProgress.value < 100);
</script>

<template>
    <div class="min-w-0 max-w-full">
        <span v-if="label" class="admin-muted mb-2 block text-xs font-semibold uppercase tracking-[0.08em]">{{ label }}</span>

        <!-- Fichier sélectionné -->
        <div v-if="hasFile" class="admin-panel-muted min-w-0 max-w-full overflow-hidden border p-3">
            <div class="flex min-w-0 items-start gap-3">
                <img v-if="previewImage" :src="previewImage" alt="" class="size-14 shrink-0 object-cover"/>
                <span v-else class="grid size-14 shrink-0 place-items-center bg-slate-200 text-slate-500 dark:bg-white/5 dark:text-slate-400">
                    <Video v-if="fileKind === 'video'" class="size-6" :stroke-width="1.6"/>
                    <FileText v-else-if="fileKind === 'pdf'" class="size-6" :stroke-width="1.6"/>
                    <File v-else class="size-6" :stroke-width="1.6"/>
                </span>
                <div class="min-w-0 flex-1">
                    <p
                        :title="modelValue?.name ?? currentName ?? 'Fichier actuel'"
                        class="admin-heading line-clamp-2 break-all text-sm font-medium leading-5"
                    >
                        {{ modelValue?.name ?? currentName ?? 'Fichier actuel' }}
                    </p>
                    <p v-if="modelValue" class="admin-muted text-xs">{{ formatSize(modelValue.size) }}</p>
                    <p v-else class="admin-muted text-xs">Fichier déjà enregistré</p>
                </div>
                <button
                    class="grid size-8 shrink-0 place-items-center text-slate-500 transition hover:bg-rose-400/10 hover:text-rose-400"
                    title="Retirer"
                    type="button"
                    @click="clear"
                >
                    <Trash2 class="size-4" :stroke-width="1.8"/>
                </button>
            </div>

            <!-- Progression -->
            <div v-if="uploading" class="mt-3">
                <div class="mb-1 flex justify-between text-xs text-slate-500">
                    <span>Envoi en cours…</span>
                    <span>{{ normalizedProgress }}%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                    <div :style="{ width: `${normalizedProgress}%` }" class="h-full bg-[#bf045b] transition-[width]"/>
                </div>
            </div>

            <button
                class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-[#ef477d] hover:text-rose-300"
                type="button"
                @click="browse"
            >
                <RefreshCw class="size-3.5"/>
                Remplacer le fichier
            </button>
        </div>

        <!-- Zone de dépôt -->
        <button
            v-else
            :class="dragging ? 'border-[#c23a72] bg-[#7d254a]/10' : 'admin-panel-muted hover:border-slate-400 dark:hover:border-white/30'"
            class="flex w-full flex-col items-center justify-center gap-2 border border-dashed px-4 py-8 text-center transition"
            type="button"
            @click="browse"
            @dragenter.prevent="dragging = true"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <ImagePlus class="size-8 text-slate-500" :stroke-width="1.5"/>
            <span class="admin-text text-sm font-medium">
                Glissez-déposez un fichier, ou <span class="text-[#ef477d]">parcourir</span>
            </span>
            <span class="admin-muted text-xs">Max {{ maxSizeMb }} Mo</span>
        </button>

        <input
            ref="inputRef"
            :accept="accept"
            class="hidden"
            type="file"
            @change="onChange"
        />

        <p v-if="localError || error" class="mt-1.5 text-xs text-rose-400">{{ localError ?? error }}</p>
        <p v-else-if="hint" class="admin-muted mt-1.5 text-xs leading-5">{{ hint }}</p>
    </div>
</template>
