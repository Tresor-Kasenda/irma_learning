<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import CourseCurriculum from '@/Components/Learning/CourseCurriculum.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

interface Chapter {
    id: number;
    section_id: number;
    title: string;
    description: string | null;
    content: string | null;
    content_type: string | null;
    media_url: string | null;
    video_url: string | null;
    duration_minutes: number | null;
    order_position: number;
    is_active: boolean;
    is_free: boolean;
}

interface SectionState {
    id: number;
    unlocked: boolean;
    chapters_complete: boolean;
    exam_id: number | null;
    exam_title: string | null;
    exam_passed: boolean | null;
    exam_missing: boolean;
    complete: boolean;
    needs_exam: boolean;
}

interface Section {
    id: number;
    title: string;
    chapters: Chapter[];
}

interface Formation {
    id: number;
    title: string;
    slug: string;
    is_certifying: boolean;
    sections: Section[];
}

interface FinalAssessment {
    required: boolean;
    ready: boolean;
    exam_id: number | null;
    exam_title: string | null;
    exam_missing: boolean;
    passed: boolean;
    needs_exam: boolean;
}

interface Enrollment {
    id: number;
    progress_percentage: number | string | null;
    status: string;
}

const props = defineProps<{
    formation: Formation;
    enrollment: Enrollment | null;
    allChapters: Chapter[];
    currentChapter: Chapter | null;
    currentChapterIndex: number;
    completedChapters: number[];
    sections: SectionState[];
    finalAssessment: FinalAssessment;
    htmlContent: string;
}>();

const showCurriculum = ref(false);
const contentPane = ref<'pdf' | 'markdown'>(props.currentChapter?.content_type === 'pdf' ? 'pdf' : 'markdown');

watch(
    () => [props.currentChapter?.id, props.currentChapter?.content_type],
    ([, contentType]) => {
        contentPane.value = contentType === 'pdf' ? 'pdf' : 'markdown';
    },
);

const currentSectionState = computed(
    () => props.sections.find((section) => section.id === props.currentChapter?.section_id) ?? null,
);
const lockedSectionIds = computed(
    () => props.sections.filter((section) => !section.unlocked).map((section) => section.id),
);

const currentChapterCompleted = computed(
    () => Boolean(props.currentChapter && props.completedChapters.includes(props.currentChapter.id)),
);
const isLastChapter = computed(() => props.currentChapterIndex >= props.allChapters.length - 1);
const canGoNext = computed(() => currentChapterCompleted.value && !isLastChapter.value);
const canFinishFormation = computed(() => props.finalAssessment.ready && (!props.finalAssessment.required || props.finalAssessment.passed));
const progressPercentage = computed(() => Math.round(Number(props.enrollment?.progress_percentage ?? 0)));

function selectChapter(chapterId: number): void {
    router.get(
        route('course.player', {formation: props.formation.id, chapterId}),
        {},
        {preserveState: true, preserveScroll: true},
    );
    showCurriculum.value = false;
}

function goPrevious(): void {
    if (props.currentChapterIndex > 0) {
        selectChapter(props.allChapters[props.currentChapterIndex - 1].id);
    }
}

function goNext(): void {
    if (canGoNext.value) {
        selectChapter(props.allChapters[props.currentChapterIndex + 1].id);
    }
}

function markComplete(): void {
    if (!props.currentChapter) {
        return;
    }

    router.post(
        route('course.chapter.complete', {
            formation: props.formation.id,
            chapter: props.currentChapter.id,
        }),
    );
}

function takeSectionExam(): void {
    if (currentSectionState.value?.exam_id) {
        router.get(route('exam.take', currentSectionState.value.exam_id));
    }
}

function takeFinalExam(): void {
    if (props.finalAssessment.exam_id) {
        router.get(route('exam.take', props.finalAssessment.exam_id));
    }
}

function mediaUrl(url: string | null): string {
    if (!url) {
        return '';
    }

    return url.startsWith('http') ? url : `/storage/${url}`;
}

function protectedMediaUrl(chapterId: number, type: 'video' | 'pdf'): string {
    return route('media.stream', {chapter: chapterId, type});
}

function disableContextMenu(event: MouseEvent): void {
    event.preventDefault();
}

function contentTypeLabel(contentType: string | null): string {
    const labels: Record<string, string> = {
        video: 'Vidéo',
        pdf: 'Document PDF',
        text: 'Lecture',
    };

    return labels[contentType ?? ''] ?? 'Chapitre';
}
</script>

<template>
    <Head :title="`${formation.title} - Formation`"/>

    <div class="flex h-screen min-h-0 flex-col overflow-hidden bg-[#071525] text-slate-100">
        <header class="sticky top-0 z-40 border-b border-white/10 bg-[#081524]">
            <div class="flex min-h-16 items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="grid size-10 shrink-0 place-items-center border border-white/10 transition hover:bg-white/5"
                        title="Retour au tableau de bord"
                    >
                        <LearningIcon class="size-5 brightness-0 invert" name="arrow-left"/>
                    </Link>
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-semibold text-white sm:text-base">{{ formation.title }}</h1>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Chapitre {{ currentChapterIndex + 1 }} sur {{ allChapters.length }}
                        </p>
                    </div>
                </div>

                <div class="hidden items-center gap-3 sm:flex">
                    <span class="text-xs text-slate-400">{{ progressPercentage }}% complété</span>
                    <div class="h-1.5 w-32 bg-white/10">
                        <div :style="{ width: `${progressPercentage}%` }" class="h-full bg-[#df3e75]"/>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-0 flex-1">
            <main class="h-full min-w-0 flex-1 overflow-y-auto">
                <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
                    <div v-if="currentChapter">
                        <section
                            v-if="currentSectionState?.needs_exam"
                            class="mb-6 flex flex-col gap-4 border border-amber-400/30 bg-amber-400/10 p-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-start gap-3">
                                <LearningIcon class="mt-0.5 size-6 shrink-0 brightness-0 invert" name="academic-cap"/>
                                <div>
                                    <h3 class="text-base font-semibold text-white">Section terminée — évaluez vos acquis</h3>
                                    <p class="mt-1 text-sm text-amber-100/80">
                                        Réussissez l'examen de cette section pour débloquer la suivante.
                                    </p>
                                </div>
                            </div>
                            <button
                                class="inline-flex h-11 shrink-0 items-center justify-center gap-2 bg-amber-500 px-5 text-sm font-semibold text-[#1a1205] transition hover:bg-amber-400"
                                type="button"
                                @click="takeSectionExam"
                            >
                                Passer l'examen de la section
                                <LearningIcon class="size-4" name="arrow-right"/>
                            </button>
                        </section>

                        <div class="sticky top-0 z-20 -mx-4 border-b border-white/10 bg-[#071525]/95 px-4 pb-5 pt-1 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                            <p class="text-[11px] font-semibold uppercase text-[#ff79a5]">
                                {{ contentTypeLabel(currentChapter.content_type) }}
                            </p>
                            <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{
                                    currentChapter.title
                                }}</h2>
                            <p v-if="currentChapter.description"
                               class="mt-3 max-w-3xl text-sm leading-6 text-slate-400">
                                {{ currentChapter.description }}
                            </p>
                        </div>

                        <div
                            v-if="currentChapter.content_type === 'video' && currentChapter.video_url"
                            class="mt-6 aspect-video overflow-hidden border border-white/10 bg-black"
                            @contextmenu.prevent="disableContextMenu"
                        >
                            <video class="size-full" controls controlslist="nodownload" disablePictureInPicture>
                                <source :src="protectedMediaUrl(currentChapter.id, 'video')" type="video/mp4"/>
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        </div>

                        <div v-else-if="currentChapter.content_type === 'pdf'" class="mt-6">
                            <div
                                v-if="currentChapter.media_url && htmlContent"
                                aria-label="Choix du contenu du chapitre"
                                class="mb-4 inline-flex border border-white/10 bg-[#0b1929] p-1"
                                role="tablist"
                            >
                                <button
                                    aria-controls="chapter-pdf-panel"
                                    :aria-selected="contentPane === 'pdf'"
                                    :class="contentPane === 'pdf' ? 'bg-[#a72f5d] text-white' : 'text-slate-400 hover:text-white'"
                                    class="px-4 py-2 text-sm font-semibold transition"
                                    role="tab"
                                    type="button"
                                    @click="contentPane = 'pdf'"
                                >
                                    Document PDF
                                </button>
                                <button
                                    aria-controls="chapter-markdown-panel"
                                    :aria-selected="contentPane === 'markdown'"
                                    :class="contentPane === 'markdown' ? 'bg-[#a72f5d] text-white' : 'text-slate-400 hover:text-white'"
                                    class="px-4 py-2 text-sm font-semibold transition"
                                    role="tab"
                                    type="button"
                                    @click="contentPane = 'markdown'"
                                >
                                    Version texte
                                </button>
                            </div>

                            <div
                                id="chapter-pdf-panel"
                                v-if="contentPane === 'pdf' && currentChapter.media_url"
                                class="overflow-hidden border border-white/10 bg-[#101d2d]"
                                role="tabpanel"
                                @contextmenu.prevent="disableContextMenu"
                            >
                                <div class="flex items-center gap-2 border-b border-white/10 px-4 py-3 text-sm font-medium text-white">
                                    <LearningIcon class="size-4 brightness-0 invert" name="document"/>
                                    Support PDF du chapitre
                                </div>
                                <iframe
                                    :src="protectedMediaUrl(currentChapter.id, 'pdf')"
                                    :title="currentChapter.title"
                                    class="h-[68vh] min-h-[520px] w-full bg-white"
                                />
                            </div>

                            <div
                                id="chapter-markdown-panel"
                                v-else-if="htmlContent"
                                class="rich-markdown border border-white/10 bg-[#101d2d] p-5 text-slate-200 sm:p-7"
                                role="tabpanel"
                                v-html="htmlContent"
                            />
                            <p v-else class="border border-white/10 bg-[#101d2d] p-5 text-sm text-slate-400">
                                Aucune version texte n’est disponible pour ce document.
                            </p>
                        </div>

                        <div
                            v-if="htmlContent && currentChapter.content_type === 'text'"
                            class="rich-markdown mt-6 border border-white/10 bg-[#101d2d] p-5 text-slate-200 sm:p-7"
                            v-html="htmlContent"
                        />

                        <section
                            v-if="!currentChapterCompleted"
                            class="mt-8 flex flex-col gap-4 border border-white/10 bg-[#101d2d] p-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-[11px] font-semibold uppercase text-slate-500">Fin du chapitre</p>
                                <h3 class="mt-1 text-lg font-semibold text-white">Marquer ce chapitre comme
                                    terminé</h3>
                                <p class="mt-2 text-sm text-slate-400">
                                    Terminez tous les chapitres de la section pour débloquer son examen.
                                </p>
                            </div>
                            <button
                                class="inline-flex h-11 shrink-0 items-center justify-center gap-2 bg-[#a72f5d] px-5 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                                type="button"
                                @click="markComplete"
                            >
                                <LearningIcon class="size-4 brightness-0 invert" name="academic-cap"/>
                                Terminer le chapitre
                            </button>
                        </section>

                        <div class="mt-8 flex items-center justify-between gap-3 border-t border-white/10 pt-5">
                            <button
                                :disabled="currentChapterIndex === 0"
                                class="inline-flex h-11 items-center gap-2 border border-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-35"
                                type="button"
                                @click="goPrevious"
                            >
                                <LearningIcon class="size-4 brightness-0 invert" name="arrow-left"/>
                                Précédent
                            </button>

                            <Link
                                v-if="isLastChapter && currentChapterCompleted && canFinishFormation"
                                :href="route('dashboard')"
                                class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                            >
                                Terminer la formation
                                <LearningIcon class="size-4 brightness-0 invert" name="arrow-right"/>
                            </Link>
                            <button
                                v-else-if="isLastChapter && currentChapterCompleted && finalAssessment.needs_exam"
                                class="inline-flex h-11 items-center gap-2 bg-amber-500 px-4 text-sm font-semibold text-[#1a1205] transition hover:bg-amber-400"
                                type="button"
                                @click="takeFinalExam"
                            >
                                Passer l’examen final
                                <LearningIcon class="size-4" name="arrow-right"/>
                            </button>
                            <button
                                v-else
                                :disabled="!canGoNext"
                                :title="canGoNext ? 'Chapitre suivant' : 'Validez le chapitre avant de continuer'"
                                class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d] disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-slate-600"
                                type="button"
                                @click="goNext"
                            >
                                Suivant
                                <LearningIcon class="size-4 brightness-0 invert" name="arrow-right"/>
                            </button>
                        </div>
                    </div>

                    <div v-else class="border border-dashed border-white/15 p-8 text-center">
                        <h2 class="text-lg font-semibold text-white">Aucun chapitre disponible</h2>
                        <p class="mt-2 text-sm text-slate-400">Le contenu de cette formation sera bientôt
                            disponible.</p>
                    </div>
                </div>
            </main>

            <aside class="sticky top-0 hidden h-full w-80 shrink-0 self-start overflow-y-auto border-l border-white/10 bg-[#081524] md:block lg:w-96">
                <div class="sticky top-0 z-10 border-b border-white/10 bg-[#081524] p-4">
                    <h3 class="font-semibold text-white">Contenu de la formation</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ completedChapters.length }} / {{ allChapters.length }} chapitres complétés
                    </p>
                </div>
                <div class="p-4">
                    <CourseCurriculum
                        :completed-chapters="completedChapters"
                        :current-chapter-id="currentChapter?.id ?? null"
                        :locked-section-ids="lockedSectionIds"
                        :section-states="sections"
                        :sections="formation.sections"
                        @select="selectChapter"
                        @select-exam="(examId) => router.get(route('exam.take', examId))"
                    />
                    <button
                        v-if="finalAssessment.required"
                        :disabled="!finalAssessment.ready || finalAssessment.exam_missing || finalAssessment.passed"
                        :class="finalAssessment.passed
                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200'
                            : finalAssessment.needs_exam
                                ? 'border-amber-400/40 bg-amber-400/10 text-amber-100'
                                : 'border-white/10 text-slate-400'"
                        class="mt-5 flex w-full items-center gap-3 border px-3 py-3 text-left enabled:hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-55"
                        type="button"
                        @click="takeFinalExam"
                    >
                        <LearningIcon class="size-5 shrink-0 brightness-0 invert" name="academic-cap"/>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold">{{ finalAssessment.exam_title || 'Examen final de certification' }}</span>
                            <span class="mt-1 block text-[11px] opacity-70">{{ finalAssessment.passed ? 'Certification réussie' : finalAssessment.exam_missing ? 'Examen non configuré' : finalAssessment.ready ? 'Prêt à démarrer' : 'Disponible après toutes les sections' }}</span>
                        </span>
                    </button>
                </div>
            </aside>
        </div>

        <button
            aria-label="Afficher le programme"
            class="fixed bottom-4 right-4 z-40 grid size-12 place-items-center bg-[#a72f5d] shadow-xl md:hidden"
            type="button"
            @click="showCurriculum = true"
        >
            <LearningIcon class="size-5 brightness-0 invert" name="bars-3"/>
        </button>

        <Teleport to="body">
            <div v-if="showCurriculum" class="fixed inset-0 z-50 md:hidden">
                <button
                    aria-label="Fermer le programme"
                    class="absolute inset-0 bg-black/65"
                    type="button"
                    @click="showCurriculum = false"
                />
                <aside class="absolute right-0 top-0 h-full w-[min(88vw,360px)] overflow-y-auto bg-[#081524]">
                    <div
                        class="sticky top-0 z-10 flex items-center justify-between border-b border-white/10 bg-[#081524] p-4">
                        <div>
                            <h3 class="font-semibold text-white">Contenu de la formation</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ completedChapters.length }} / {{ allChapters.length }} chapitres
                            </p>
                        </div>
                        <button
                            aria-label="Fermer"
                            class="grid size-9 place-items-center border border-white/10"
                            type="button"
                            @click="showCurriculum = false"
                        >
                            <LearningIcon class="size-5 brightness-0 invert" name="x-mark"/>
                        </button>
                    </div>
                    <div class="p-4">
                        <CourseCurriculum
                            :completed-chapters="completedChapters"
                            :current-chapter-id="currentChapter?.id ?? null"
                            :locked-section-ids="lockedSectionIds"
                            :section-states="sections"
                            :sections="formation.sections"
                            @select="selectChapter"
                            @select-exam="(examId) => router.get(route('exam.take', examId))"
                        />
                    </div>
                </aside>
            </div>
        </Teleport>
    </div>
</template>
