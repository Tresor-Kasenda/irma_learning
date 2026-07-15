<script lang="ts" setup>
import {Head, Link, router, useForm} from '@inertiajs/vue3';
import {ArrowLeft, BookOpen, ClipboardCheck, Clock3, GraduationCap, Layers3, Plus, Save, Settings2, Trash2} from '@lucide/vue';
import {computed, ref} from 'vue';
import ExamEditor from '@/Components/Admin/ExamEditor.vue';
import FileUpload from '@/Components/Admin/FileUpload.vue';
import PdfProcessingStatus from '@/Components/Admin/PdfProcessingStatus.vue';
import ResourceFormModal from '@/Components/Admin/ResourceFormModal.vue';
import MarkdownEditor from '@/Components/Admin/Fields/MarkdownEditor.vue';
import NumberField from '@/Components/Admin/Fields/NumberField.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {readVideoDurationMinutes} from '@/utilities/media';
import {notify} from '@/utilities/toast';
import {createEmptyExam, type ExamEditorData} from '@/types/admin-exam';

interface ProcessingMetadata {
    page_count?: number;
    image_count?: number;
    ocr_required_pages?: number[];
    warnings?: string[];
}

interface ChapterData {
    id: number;
    title: string;
    content_type: string;
    content: string;
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

interface SectionData {
    id: number;
    formation_id: number;
    title: string;
    description: string | null;
    duration: number | null;
    is_active: boolean;
    chapters: ChapterData[];
    exam?: ExamEditorData | null;
}

interface FormationOption {
    value: number;
    label: string;
}

interface ChapterRow {
    id: number | null;
    title: string;
    content_type: string;
    content: string;
    video_url: string | null;
    media_url: string | null;
    video: File | null;
    media: File | null;
    processing_status: string | null;
    processing_error: string | null;
    processing_metadata: ProcessingMetadata | null;
    processed_at: string | null;
    duration_minutes: number | null;
    is_free: boolean;
    is_active: boolean;
}

const props = defineProps<{
    section: SectionData | null;
    formations: FormationOption[];
    preselectedFormationId: number | null;
}>();

const isEdit = computed(() => props.section !== null);

const contentTypeOptions = [
    {value: 'text', label: 'Texte'},
    {value: 'video', label: 'Vidéo'},
    {value: 'pdf', label: 'PDF'},
];

const form = useForm<{
    formation_id: number | null;
    title: string;
    description: string;
    duration: number | null;
    is_active: boolean;
    chapters: ChapterRow[];
    exam: ExamEditorData | null;
}>({
    formation_id: props.section?.formation_id ?? props.preselectedFormationId ?? null,
    title: props.section?.title ?? '',
    description: props.section?.description ?? '',
    duration: props.section?.duration ?? null,
    is_active: props.section?.is_active ?? true,
    chapters: props.section?.chapters.map((chapter) => ({
        id: chapter.id,
        title: chapter.title,
        content_type: chapter.content_type,
        content: chapter.content ?? '',
        video_url: chapter.video_url,
        media_url: chapter.media_url,
        video: null,
        media: null,
        processing_status: chapter.processing_status,
        processing_error: chapter.processing_error,
        processing_metadata: chapter.processing_metadata,
        processed_at: chapter.processed_at,
        duration_minutes: chapter.duration_minutes,
        is_free: chapter.is_free,
        is_active: chapter.is_active,
    })) ?? [],
    exam: props.section?.exam ?? null,
});

function addChapter(): void {
    form.chapters.push({
        id: null,
        title: '',
        content_type: 'text',
        content: '',
        video_url: null,
        media_url: null,
        video: null,
        media: null,
        processing_status: null,
        processing_error: null,
        processing_metadata: null,
        processed_at: null,
        duration_minutes: null,
        is_free: false,
        is_active: true,
    });
}

function removeChapter(index: number): void {
    form.chapters.splice(index, 1);
}

function chapterError(index: number): string | undefined {
    return (form.errors as Record<string, string>)[`chapters.${index}.title`];
}

function chapterFieldError(index: number, field: string): string | undefined {
    return (form.errors as Record<string, string>)[`chapters.${index}.${field}`];
}

function fileName(path: string | null | undefined): string {
    return path ? path.split('/').pop() ?? path : '';
}

function estimateReadingMinutes(content: string): number | null {
    const text = content
        .replace(/```(?:[\w-]+)?\n?([\s\S]*?)```/g, '$1')
        .replace(/[`*_>#\[\]()|~-]/g, ' ')
        .trim();

    if (text === '') {
        return null;
    }

    return Math.max(1, Math.ceil(text.split(/\s+/u).length / 200));
}

function recalculateSectionDuration(): void {
    const total = form.chapters.reduce((minutes, chapter) => minutes + (chapter.duration_minutes ?? 0), 0);
    form.duration = total > 0 ? total : null;
}

function updateChapterContent(chapter: ChapterRow, content: string): void {
    chapter.content = content;

    if (chapter.content_type === 'text' || chapter.content_type === 'pdf') {
        chapter.duration_minutes = estimateReadingMinutes(content);
        recalculateSectionDuration();
    }
}

function updateChapterType(chapter: ChapterRow, contentType: string): void {
    chapter.content_type = contentType;

    if (contentType !== 'video') {
        chapter.video = null;
    }
    if (contentType !== 'pdf') {
        chapter.media = null;
    }

    if (contentType === 'text' || contentType === 'pdf') {
        chapter.duration_minutes = estimateReadingMinutes(chapter.content);
        recalculateSectionDuration();
    }
}

async function updateChapterVideo(chapter: ChapterRow, file: File | null): Promise<void> {
    chapter.video = file;
    const duration = await readVideoDurationMinutes(file);

    if (duration !== null) {
        chapter.duration_minutes = duration;
        recalculateSectionDuration();
    } else if (! file && ! chapter.video_url) {
        chapter.duration_minutes = null;
        recalculateSectionDuration();
    }
}

function retryChapterExtraction(chapter: ChapterRow): void {
    if (! chapter.id) {
        return;
    }

    router.post(safeRoute('admin.chapters.extract-pdf', chapter.id), {}, {
        preserveScroll: true,
        onError: () => notify({type: 'error', message: 'Impossible de relancer l’extraction PDF.'}),
    });
}

const examModalOpen = ref(false);
const examSnapshot = ref<ExamEditorData | null>(null);

function openExamModal(): void {
    examSnapshot.value = form.exam ? structuredClone(form.exam) : null;
    form.exam ??= createEmptyExam();
    examModalOpen.value = true;
}

function cancelExamModal(): void {
    form.exam = examSnapshot.value ? structuredClone(examSnapshot.value) : null;
    examModalOpen.value = false;
}

function applyExamModal(): void {
    examModalOpen.value = false;
}

function removeExam(): void {
    form.exam = null;
    examModalOpen.value = false;
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        chapters: data.chapters.filter((chapter) => chapter.title.trim() !== ''),
    }));

    const onError = () => notify({type: 'error', message: 'Vérifiez les champs signalés avant d’enregistrer.'});

    if (props.section) {
        form.post(safeRoute('admin.sections.update', props.section.id), {forceFormData: true, onError});
    } else {
        form.post(safeRoute('admin.sections.store'), {forceFormData: true, onError});
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier la section' : 'Nouvelle section'"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.sections.index')" class="admin-muted transition hover:text-[#a23362]">
                Sections
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text font-medium">{{ isEdit ? 'Modifier' : 'Nouvelle' }}</span>
        </template>

        <form class="mx-auto min-w-0 max-w-7xl overflow-x-clip" @submit.prevent="submit">
            <div class="mb-7 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                <Link
                    :href="safeRoute('admin.sections.index')"
                    aria-label="Retour aux sections"
                    class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                >
                    <ArrowLeft class="size-5" :stroke-width="1.7"/>
                </Link>
                <div class="min-w-0 flex-1">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="bg-[#7d254a]/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#7d254a] dark:bg-[#7d254a]/50 dark:text-rose-200">
                            {{ isEdit ? 'Modification' : 'Création' }}
                        </span>
                        <span v-if="form.isDirty" class="text-xs text-amber-300">Modifications non enregistrées</span>
                    </div>
                    <h1 class="admin-heading text-2xl font-semibold tracking-tight sm:text-3xl">
                        {{ isEdit ? 'Modifier la section' : 'Nouvelle section' }}
                    </h1>
                    <p class="admin-muted mt-2 max-w-2xl text-sm leading-6">
                        Rattachez la section à une formation, puis ajoutez ses chapitres.
                    </p>
                </div>
                <button
                    v-if="isEdit"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 border border-[#a23362] px-4 text-sm font-semibold text-[#a23362] transition hover:bg-[#a23362]/10"
                    type="button"
                    @click="openExamModal"
                >
                    <ClipboardCheck class="size-4" :stroke-width="1.8"/>
                    {{ form.exam ? 'Modifier l’examen' : 'Créer l’examen' }}
                </button>
            </div>

            <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="grid min-w-0 gap-6">
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/35 text-[#ef477d]">
                                <Layers3 class="size-5" :stroke-width="1.7"/>
                            </span>
                            <div>
                                <h2 class="admin-heading font-semibold">Contenu de la section</h2>
                                <p class="admin-muted mt-1 text-xs leading-5">Le titre et la description situent cette partie du programme.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 p-5 sm:p-6">
                            <TextField
                                v-model="form.title"
                                :error="form.errors.title"
                                label="Titre de la section"
                                placeholder="Ex. Introduction et fondamentaux"
                                required
                            />
                            <TextareaField
                                v-model="form.description"
                                :error="form.errors.description"
                                :rows="3"
                                label="Description"
                                placeholder="Décrivez brièvement ce que couvre cette section."
                            />
                        </div>
                    </section>

                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex flex-col gap-4 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex items-start gap-3">
                                <span class="grid size-10 shrink-0 place-items-center bg-sky-400/10 text-sky-300">
                                    <BookOpen class="size-5" :stroke-width="1.7"/>
                                </span>
                                <div>
                                    <h2 class="admin-heading font-semibold">Chapitres</h2>
                                    <p class="admin-muted mt-1 text-xs leading-5">Le contenu détaillé (vidéo, PDF, texte) se gère ensuite dans chaque chapitre.</p>
                                </div>
                            </div>
                            <button
                                class="inline-flex h-10 shrink-0 items-center justify-center gap-2 border border-[#a23362] px-4 text-sm font-semibold text-[#7d254a] transition hover:bg-[#7d254a]/10 dark:text-rose-200 dark:hover:bg-[#7d254a]/30"
                                type="button"
                                @click="addChapter"
                            >
                                <Plus class="size-4" :stroke-width="2"/>
                                Ajouter un chapitre
                            </button>
                        </div>

                        <div class="grid gap-3 p-5 sm:p-6">
                            <article
                                v-for="(chapter, index) in form.chapters"
                                :key="chapter.id ?? `new-${index}`"
                                class="admin-panel-muted min-w-0 overflow-hidden border"
                            >
                                <div class="admin-divider flex items-center gap-3 border-b px-4 py-3">
                                    <span class="admin-text grid size-7 shrink-0 place-items-center bg-slate-200 text-xs font-bold dark:bg-white/5">
                                        {{ String(index + 1).padStart(2, '0') }}
                                    </span>
                                    <p class="admin-heading min-w-0 flex-1 truncate text-sm font-medium">
                                        {{ chapter.title || `Chapitre ${index + 1}` }}
                                    </p>
                                    <button
                                        :aria-label="`Retirer le chapitre ${index + 1}`"
                                        class="grid size-8 shrink-0 place-items-center text-slate-500 transition hover:bg-rose-400/10 hover:text-rose-400"
                                        type="button"
                                        @click="removeChapter(index)"
                                    >
                                        <Trash2 class="size-4" :stroke-width="1.8"/>
                                    </button>
                                </div>

                                <div class="grid gap-4 p-4">
                                    <div>
                                        <label :for="`chapter-title-${index}`" class="admin-muted mb-2 block text-xs font-semibold uppercase tracking-[0.08em]">
                                            Titre du chapitre <span class="text-[#ef477d]">*</span>
                                        </label>
                                        <input
                                            :id="`chapter-title-${index}`"
                                            v-model="chapter.title"
                                            class="admin-field h-11 w-full border px-3 text-sm outline-none transition"
                                            placeholder="Ex. Installer l’environnement"
                                            type="text"
                                        />
                                        <p v-if="chapterError(index)" class="mt-1.5 text-xs text-rose-400">{{ chapterError(index) }}</p>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <SearchableSelect
                                            :clearable="false"
                                            :model-value="chapter.content_type"
                                            :options="contentTypeOptions"
                                            :searchable="false"
                                            label="Type de contenu"
                                            @update:model-value="updateChapterType(chapter, $event as string)"
                                        />
                                        <NumberField
                                            v-model="chapter.duration_minutes"
                                            :icon="Clock3"
                                            :id="`chapter-duration-${index}`"
                                            :min="0"
                                            label="Durée"
                                            placeholder="0"
                                            suffix="min"
                                        />
                                    </div>

                                    <MarkdownEditor
                                        v-if="chapter.content_type === 'text'"
                                        :id="`chapter-markdown-${index}`"
                                        label="Contenu du chapitre"
                                        :model-value="chapter.content"
                                        :error="chapterFieldError(index, 'content')"
                                        hint="La durée est estimée automatiquement à partir du temps de lecture."
                                        placeholder="# Titre du chapitre\n\nCommencez à rédiger…"
                                        @update:model-value="updateChapterContent(chapter, $event)"
                                    />

                                    <FileUpload
                                        v-else-if="chapter.content_type === 'video'"
                                        :model-value="chapter.video"
                                        accept="video/*"
                                        :current-name="fileName(chapter.video_url)"
                                        :error="chapterFieldError(index, 'video')"
                                        hint="MP4, WebM, OGG ou MOV, 500 Mo maximum."
                                        label="Fichier vidéo"
                                        :max-size-mb="500"
                                        :progress="chapter.video ? form.progress?.percentage ?? null : null"
                                        @update:model-value="updateChapterVideo(chapter, $event)"
                                    />

                                    <div v-else-if="chapter.content_type === 'pdf'" class="grid min-w-0 gap-5">
                                        <FileUpload
                                            v-model="chapter.media"
                                            accept="application/pdf"
                                            :current-name="fileName(chapter.media_url)"
                                            :error="chapterFieldError(index, 'media')"
                                            hint="Le contenu, la durée et la couverture seront extraits automatiquement du PDF."
                                            label="Fichier PDF"
                                            :max-size-mb="50"
                                            :progress="chapter.media ? form.progress?.percentage ?? null : null"
                                        />
                                        <div v-if="chapter.media" class="border border-sky-400/30 bg-sky-400/10 p-3 text-xs leading-5 text-sky-700 dark:text-sky-200">
                                            Le PDF sera analysé en arrière-plan après l’enregistrement. Le Markdown restera modifiable après extraction.
                                        </div>
                                        <PdfProcessingStatus
                                            v-else-if="chapter.processing_status"
                                            :error="chapter.processing_error"
                                            :metadata="chapter.processing_metadata"
                                            :retryable="Boolean(chapter.id && chapter.media_url)"
                                            :status="chapter.processing_status"
                                            @retry="retryChapterExtraction(chapter)"
                                        />
                                        <MarkdownEditor
                                            :id="`chapter-pdf-markdown-${index}`"
                                            :error="chapterFieldError(index, 'content')"
                                            label="Contenu Markdown du PDF"
                                            :model-value="chapter.content"
                                            hint="Le contenu extrait du PDF peut être relu et corrigé ici."
                                            placeholder="# Contenu extrait du PDF"
                                            @update:model-value="updateChapterContent(chapter, $event)"
                                        />
                                    </div>

                                    <div class="admin-divider flex flex-wrap gap-6 border-t pt-4">
                                        <ToggleField v-model="chapter.is_active" compact label="Chapitre actif"/>
                                        <ToggleField v-model="chapter.is_free" compact label="Aperçu gratuit"/>
                                    </div>
                                </div>
                            </article>

                            <div v-if="form.chapters.length === 0" class="admin-divider border border-dashed px-5 py-10 text-center">
                                <BookOpen class="mx-auto size-8 text-slate-600" :stroke-width="1.5"/>
                                <p class="admin-heading mt-3 text-sm font-medium">Aucun chapitre</p>
                                <p class="admin-muted mt-1 text-xs">Ajoutez un premier chapitre à cette section.</p>
                                <button class="mt-4 text-sm font-semibold text-[#ef477d] hover:text-rose-300" type="button" @click="addChapter">
                                    Ajouter un chapitre
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <GraduationCap class="size-5 text-[#ef477d]" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Formation</h2>
                                <p class="admin-muted mt-0.5 text-xs">Rattachement au catalogue</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <SearchableSelect
                                :clearable="false"
                                :error="form.errors.formation_id"
                                :model-value="form.formation_id"
                                :options="formations"
                                label="Formation"
                                placeholder="Choisir une formation…"
                                required
                                @update:model-value="form.formation_id = ($event as number)"
                            />
                        </div>
                    </section>

                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Settings2 class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <div>
                                <h2 class="admin-heading font-semibold">Réglages</h2>
                                <p class="admin-muted mt-0.5 text-xs">Durée et visibilité</p>
                            </div>
                        </div>
                        <div class="grid gap-5 p-5">
                            <NumberField
                                v-model="form.duration"
                                :error="form.errors.duration"
                                :icon="Clock3"
                                id="section-duration"
                                :min="0"
                                hint="Laissez vide pour un calcul automatique."
                                label="Durée (minutes)"
                                placeholder="0"
                                suffix="min"
                            />
                            <ToggleField
                                v-model="form.is_active"
                                hint="La section est visible dans le parcours."
                                label="Section active"
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
                        :href="safeRoute('admin.sections.index')"
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
                        {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Enregistrer' : 'Créer la section') }}
                    </button>
                </div>
            </div>
        </form>

        <ResourceFormModal
            :show="examModalOpen"
            :processing="form.processing"
            :title="form.exam?.id ? 'Modifier l’examen de la section' : 'Créer l’examen de la section'"
            size="xl"
            :slide-over="false"
            submit-label="Appliquer à la section"
            @close="cancelExamModal"
            @submit="applyExamModal"
        >
            <ExamEditor
                v-if="form.exam"
                v-model="form.exam"
                :errors="form.errors as Record<string, string>"
                error-prefix="exam."
            />
            <button
                v-if="form.exam"
                class="inline-flex h-10 items-center gap-2 border border-rose-500/40 px-4 text-sm font-semibold text-rose-500 transition hover:bg-rose-500/10"
                type="button"
                @click="removeExam"
            >
                <Trash2 class="size-4"/>
                Retirer l’examen de cette section
            </button>
        </ResourceFormModal>
    </AdminLayout>
</template>
