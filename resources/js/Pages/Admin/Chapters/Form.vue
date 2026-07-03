<script lang="ts" setup>
import {Head, Link, router, useForm} from '@inertiajs/vue3';
import {ArrowLeft, Clock3, FileText, Layers3, Save, Settings2} from '@lucide/vue';
import {computed} from 'vue';
import FileUpload from '@/Components/Admin/FileUpload.vue';
import PdfProcessingStatus from '@/Components/Admin/PdfProcessingStatus.vue';
import MarkdownEditor from '@/Components/Admin/Fields/MarkdownEditor.vue';
import NumberField from '@/Components/Admin/Fields/NumberField.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {notify} from '@/utilities/toast';
import {readVideoDurationMinutes} from '@/utilities/media';

interface ProcessingMetadata {
    page_count?: number;
    image_count?: number;
    ocr_required_pages?: number[];
    warnings?: string[];
}

interface ChapterData {
    id: number;
    section_id: number;
    title: string;
    description: string | null;
    content: string | null;
    content_type: string;
    video_url: string | null;
    media_url: string | null;
    processing_status: string | null;
    processing_error: string | null;
    processing_metadata: ProcessingMetadata | null;
    processed_at: string | null;
    duration_minutes: number | null;
    is_free: boolean;
    is_active: boolean;
}

interface SectionOption {
    value: number;
    label: string;
}

const props = defineProps<{
    chapter: ChapterData | null;
    sections: SectionOption[];
    preselectedSectionId: number | null;
}>();

const isEdit = computed(() => props.chapter !== null);

const contentTypeOptions = [
    {value: 'text', label: 'Texte'},
    {value: 'video', label: 'Vidéo'},
    {value: 'pdf', label: 'PDF'},
];

const form = useForm<{
    section_id: number | null;
    title: string;
    description: string;
    content_type: string;
    content: string;
    duration_minutes: number | null;
    is_free: boolean;
    is_active: boolean;
    video: File | null;
    media: File | null;
}>({
    section_id: props.chapter?.section_id ?? props.preselectedSectionId ?? null,
    title: props.chapter?.title ?? '',
    description: props.chapter?.description ?? '',
    content_type: props.chapter?.content_type ?? 'text',
    content: props.chapter?.content ?? '',
    duration_minutes: props.chapter?.duration_minutes ?? null,
    is_free: props.chapter?.is_free ?? false,
    is_active: props.chapter?.is_active ?? true,
    video: null,
    media: null,
});

function fileName(path: string | null | undefined): string {
    return path ? path.split('/').pop() ?? path : '';
}

function estimateReadingMinutes(content: string): number | null {
    const text = content
        .replace(/```(?:[\w-]+)?\n?([\s\S]*?)```/g, '$1')
        .replace(/[`*_>#\[\]()|~-]/g, ' ')
        .trim();

    return text === '' ? null : Math.max(1, Math.ceil(text.split(/\s+/u).length / 200));
}

function updateContent(content: string): void {
    form.content = content;
    form.duration_minutes = estimateReadingMinutes(content);
}

function updateContentType(contentType: string): void {
    form.content_type = contentType;

    if (contentType !== 'video') {
        form.video = null;
    }
    if (contentType !== 'pdf') {
        form.media = null;
    }
    if (contentType === 'text' || contentType === 'pdf') {
        form.duration_minutes = estimateReadingMinutes(form.content);
    }
}

async function updateVideo(file: File | null): Promise<void> {
    form.video = file;
    const duration = await readVideoDurationMinutes(file);

    if (duration !== null) {
        form.duration_minutes = duration;
    } else if (! file && ! props.chapter?.video_url) {
        form.duration_minutes = null;
    }
}

function retryExtraction(): void {
    if (! props.chapter) {
        return;
    }

    router.post(safeRoute('admin.chapters.extract-pdf', props.chapter.id), {}, {
        preserveScroll: true,
        onError: () => notify({type: 'error', message: 'Impossible de relancer l’extraction PDF.'}),
    });
}

function submit(): void {
    const onError = () => notify({type: 'error', message: 'Vérifiez les champs signalés avant d’enregistrer.'});

    if (props.chapter) {
        form.post(safeRoute('admin.chapters.update', props.chapter.id), {forceFormData: true, onError});
    } else {
        form.post(safeRoute('admin.chapters.store'), {forceFormData: true, onError});
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier le chapitre' : 'Nouveau chapitre'"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.chapters.index')" class="admin-muted transition hover:text-[#a23362]">
                Chapitres
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text font-medium">{{ isEdit ? 'Modifier' : 'Nouveau' }}</span>
        </template>

        <form class="mx-auto max-w-7xl" @submit.prevent="submit">
            <div class="mb-7 flex items-start gap-4">
                <Link
                    :href="safeRoute('admin.chapters.index')"
                    aria-label="Retour aux chapitres"
                    class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                >
                    <ArrowLeft class="size-5" :stroke-width="1.7"/>
                </Link>
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="bg-[#7d254a]/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#7d254a] dark:bg-[#7d254a]/50 dark:text-rose-200">
                            {{ isEdit ? 'Modification' : 'Création' }}
                        </span>
                        <span v-if="form.isDirty" class="text-xs text-amber-300">Modifications non enregistrées</span>
                    </div>
                    <h1 class="admin-heading text-2xl font-semibold tracking-tight sm:text-3xl">
                        {{ isEdit ? 'Modifier le chapitre' : 'Nouveau chapitre' }}
                    </h1>
                </div>
            </div>

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="grid gap-6">
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/35 text-[#ef477d]">
                                <FileText class="size-5" :stroke-width="1.7"/>
                            </span>
                            <div>
                                <h2 class="admin-heading font-semibold">Contenu du chapitre</h2>
                                <p class="admin-muted mt-1 text-xs leading-5">Le contenu dépend du type sélectionné.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField
                                v-model="form.title"
                                :error="form.errors.title"
                                label="Titre du chapitre"
                                placeholder="Ex. Installer l’environnement de développement"
                                required
                            />
                            <TextareaField
                                v-model="form.description"
                                :error="form.errors.description"
                                :rows="2"
                                label="Description"
                                placeholder="Résumé court du chapitre."
                            />

                            <SearchableSelect
                                :clearable="false"
                                :error="form.errors.content_type"
                                :model-value="form.content_type"
                                :options="contentTypeOptions"
                                :searchable="false"
                                label="Type de contenu"
                                required
                                @update:model-value="updateContentType($event as string)"
                            />

                            <!-- Texte -->
                            <MarkdownEditor
                                v-if="form.content_type === 'text'"
                                :error="form.errors.content"
                                id="chapter-markdown"
                                label="Contenu du chapitre"
                                :model-value="form.content"
                                hint="La durée est estimée automatiquement à partir du temps de lecture."
                                placeholder="# Titre du chapitre\n\nCommencez à rédiger…"
                                @update:model-value="updateContent"
                            />

                            <!-- Vidéo -->
                            <div v-else-if="form.content_type === 'video'">
                                <FileUpload
                                    :model-value="form.video"
                                    :current-name="fileName(chapter?.video_url)"
                                    :error="form.errors.video"
                                    :max-size-mb="500"
                                    :progress="form.video ? form.progress?.percentage ?? null : null"
                                    accept="video/*"
                                    hint="MP4, WebM, OGG ou MOV, 500 Mo maximum."
                                    label="Fichier vidéo"
                                    @update:model-value="updateVideo"
                                />
                            </div>

                            <!-- PDF -->
                            <div v-else-if="form.content_type === 'pdf'" class="grid gap-5">
                                <FileUpload
                                    v-model="form.media"
                                    :current-name="fileName(chapter?.media_url)"
                                    :error="form.errors.media"
                                    :max-size-mb="50"
                                    :progress="form.media ? form.progress?.percentage ?? null : null"
                                    accept="application/pdf"
                                    hint="Le contenu, la durée et la couverture seront extraits automatiquement du PDF."
                                    label="Fichier PDF"
                                />
                                <div v-if="form.media" class="border border-sky-400/30 bg-sky-400/10 p-3 text-xs leading-5 text-sky-700 dark:text-sky-200">
                                    Le PDF sera analysé en arrière-plan après l’enregistrement. Le texte, les tableaux, les images et les pages complexes seront ajoutés au Markdown.
                                </div>
                                <PdfProcessingStatus
                                    v-else-if="chapter?.processing_status"
                                    :error="chapter.processing_error"
                                    :metadata="chapter.processing_metadata"
                                    :retryable="Boolean(chapter.id && chapter.media_url)"
                                    :status="chapter.processing_status"
                                    @retry="retryExtraction"
                                />
                                <MarkdownEditor
                                    :error="form.errors.content"
                                    id="chapter-pdf-markdown"
                                    label="Contenu Markdown du PDF"
                                    :model-value="form.content"
                                    hint="Le contenu extrait du PDF peut être relu et corrigé ici."
                                    placeholder="# Contenu extrait du PDF"
                                    @update:model-value="updateContent"
                                />
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="grid gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Layers3 class="size-5 text-[#ef477d]" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Section</h2>
                                <p class="admin-muted mt-0.5 text-xs">Rattachement au programme</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <SearchableSelect
                                :clearable="false"
                                :error="form.errors.section_id"
                                :model-value="form.section_id"
                                :options="sections"
                                label="Section"
                                placeholder="Choisir une section…"
                                required
                                @update:model-value="form.section_id = ($event as number)"
                            />
                        </div>
                    </section>

                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Settings2 class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Réglages</h2>
                                <p class="admin-muted mt-0.5 text-xs">Durée et visibilité</p>
                            </div>
                        </div>
                        <div class="grid gap-5 p-5">
                            <NumberField
                                v-model="form.duration_minutes"
                                :error="form.errors.duration_minutes"
                                :icon="Clock3"
                                id="chapter-duration"
                                :min="0"
                                hint="Calculée automatiquement pour les PDF."
                                label="Durée (minutes)"
                                placeholder="0"
                                suffix="min"
                            />
                            <ToggleField
                                v-model="form.is_free"
                                hint="Consultable sans inscription (aperçu)."
                                label="Aperçu gratuit"
                            />
                            <ToggleField
                                v-model="form.is_active"
                                hint="Le chapitre est visible dans la section."
                                label="Chapitre actif"
                            />
                        </div>
                    </section>
                </aside>
            </div>

            <div class="admin-panel sticky bottom-0 z-20 mt-6 flex flex-col-reverse gap-3 border p-4 shadow-2xl shadow-black/15 sm:flex-row sm:items-center sm:justify-between">
                <p class="admin-muted text-xs">
                    Les champs marqués d’un <span class="text-[#ef477d]">*</span> sont obligatoires.
                </p>
                <div class="flex items-center justify-end gap-2">
                    <Link
                        :href="safeRoute('admin.chapters.index')"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center border px-4 text-sm font-medium transition"
                    >
                        Annuler
                    </Link>
                    <button
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e] disabled:cursor-not-allowed disabled:opacity-60"
                        type="submit"
                    >
                        <Save class="size-4" :stroke-width="1.8"/>
                        {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Enregistrer' : 'Créer le chapitre') }}
                    </button>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
