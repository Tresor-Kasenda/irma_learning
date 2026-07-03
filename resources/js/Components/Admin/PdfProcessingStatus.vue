<script lang="ts" setup>
import {AlertTriangle, CheckCircle2, LoaderCircle, RefreshCw, ScanLine} from '@lucide/vue';
import {computed} from 'vue';

interface ProcessingMetadata {
    page_count?: number;
    image_count?: number;
    ocr_required_pages?: number[];
    warnings?: string[];
}

const props = defineProps<{
    status?: string | null;
    error?: string | null;
    metadata?: ProcessingMetadata | null;
    retryable?: boolean;
}>();

const emit = defineEmits<{ (event: 'retry'): void }>();

const isRunning = computed(() => props.status === 'pending' || props.status === 'processing');
const statusLabel = computed(() => ({
    pending: 'Extraction en attente',
    processing: 'Analyse Python en cours',
    completed: 'Extraction terminée',
    needs_ocr: 'Extraction terminée avec OCR requis',
    failed: 'Échec de l’extraction',
}[props.status ?? ''] ?? 'PDF prêt à être analysé'));
</script>

<template>
    <div
        v-if="status"
        :class="status === 'failed'
            ? 'border-rose-400/30 bg-rose-400/10'
            : status === 'needs_ocr'
                ? 'border-amber-400/30 bg-amber-400/10'
                : 'admin-panel-muted'"
        class="min-w-0 border p-4"
    >
        <div class="flex min-w-0 items-start gap-3">
            <LoaderCircle v-if="isRunning" class="mt-0.5 size-5 shrink-0 animate-spin text-sky-400" :stroke-width="1.8"/>
            <CheckCircle2 v-else-if="status === 'completed'" class="mt-0.5 size-5 shrink-0 text-emerald-400" :stroke-width="1.8"/>
            <ScanLine v-else-if="status === 'needs_ocr'" class="mt-0.5 size-5 shrink-0 text-amber-400" :stroke-width="1.8"/>
            <AlertTriangle v-else class="mt-0.5 size-5 shrink-0 text-rose-400" :stroke-width="1.8"/>

            <div class="min-w-0 flex-1">
                <p class="admin-heading text-sm font-semibold">{{ statusLabel }}</p>
                <p v-if="isRunning" class="admin-muted mt-1 text-xs leading-5">
                    Le document est traité en arrière-plan. Le Markdown sera disponible après actualisation.
                </p>
                <p v-else-if="error" class="mt-1 break-words text-xs leading-5 text-rose-300">{{ error }}</p>
                <p v-else-if="metadata?.page_count" class="admin-muted mt-1 text-xs leading-5">
                    {{ metadata.page_count }} page(s) · {{ metadata.image_count ?? 0 }} image(s) extraite(s)
                </p>

                <ul v-if="metadata?.warnings?.length" class="mt-2 grid gap-1 text-xs leading-5 text-amber-300">
                    <li v-for="warning in metadata.warnings" :key="warning">{{ warning }}</li>
                </ul>

                <button
                    v-if="retryable && (status === 'failed' || status === 'needs_ocr' || status === 'completed')"
                    class="mt-3 inline-flex h-9 items-center gap-2 border border-[#a23362] px-3 text-xs font-semibold text-[#a23362] transition hover:bg-[#a23362]/10 dark:text-rose-200"
                    type="button"
                    @click="emit('retry')"
                >
                    <RefreshCw class="size-3.5" :stroke-width="1.8"/>
                    Relancer l’extraction
                </button>
            </div>
        </div>
    </div>
</template>
