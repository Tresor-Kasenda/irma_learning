<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ChapterExamCard from '@/Components/Learning/ChapterExamCard.vue';
import CourseCurriculum from '@/Components/Learning/CourseCurriculum.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

interface Exam {
    id: number;
    title: string;
    description?: string | null;
    duration_minutes: number;
    passing_score: number;
    max_attempts?: number;
    is_active: boolean;
}

interface Chapter {
    id: number;
    title: string;
    description: string | null;
    content: string | null;
    content_type: string | null;
    media_url: string | null;
    video_url: string | null;
    audio_url: string | null;
    duration_minutes: number | null;
    order_position: number;
    is_active: boolean;
    is_free: boolean;
    exams?: Exam | null;
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
    sections: Section[];
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
    htmlContent: string;
    chapterExam: Exam | null;
    hasPassedExam: boolean;
}>();

const showCurriculum = ref(false);

const currentChapterCompleted = computed(
    () => Boolean(props.currentChapter && props.completedChapters.includes(props.currentChapter.id)),
);
const isLastChapter = computed(() => props.currentChapterIndex >= props.allChapters.length - 1);
const canGoNext = computed(() => currentChapterCompleted.value && !isLastChapter.value);
const progressPercentage = computed(() => Math.round(Number(props.enrollment?.progress_percentage ?? 0)));

function selectChapter(chapterId: number): void {
    router.get(
        route('course.player', { formation: props.formation.id, chapterId }),
        {},
        { preserveState: true, preserveScroll: true },
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

function takeExam(): void {
    if (props.chapterExam) {
        router.get(route('exam.take', props.chapterExam.id));
    }
}

function mediaUrl(url: string | null): string {
    if (!url) {
        return '';
    }

    return url.startsWith('http') ? url : `/storage/${url}`;
}

function contentTypeLabel(contentType: string | null): string {
    const labels: Record<string, string> = {
        video: 'Vidéo',
        pdf: 'Document PDF',
        text: 'Lecture',
        audio: 'Audio',
    };

    return labels[contentType ?? ''] ?? 'Chapitre';
}
</script>

<template>
    <Head :title="`${formation.title} - Formation`" />

    <div class="flex min-h-screen flex-col bg-[#071525] text-slate-100">
        <header class="sticky top-0 z-40 border-b border-white/10 bg-[#081524]">
            <div class="flex min-h-16 items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="grid size-10 shrink-0 place-items-center border border-white/10 transition hover:bg-white/5"
                        title="Retour au tableau de bord"
                    >
                        <LearningIcon name="arrow-left" class="size-5 brightness-0 invert" />
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
                        <div class="h-full bg-[#df3e75]" :style="{ width: `${progressPercentage}%` }" />
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-0 flex-1">
            <main class="min-w-0 flex-1 overflow-y-auto">
                <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
                    <div v-if="currentChapter">
                        <div class="border-b border-white/10 pb-6">
                            <p class="text-[11px] font-semibold uppercase text-[#ff79a5]">
                                {{ contentTypeLabel(currentChapter.content_type) }}
                            </p>
                            <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{ currentChapter.title }}</h2>
                            <p v-if="currentChapter.description" class="mt-3 max-w-3xl text-sm leading-6 text-slate-400">
                                {{ currentChapter.description }}
                            </p>
                        </div>

                        <div
                            v-if="currentChapter.content_type === 'video' && currentChapter.video_url"
                            class="mt-6 aspect-video overflow-hidden border border-white/10 bg-black"
                        >
                            <video controls class="size-full" controlslist="nodownload">
                                <source :src="mediaUrl(currentChapter.video_url)" type="video/mp4" />
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        </div>

                        <div
                            v-else-if="currentChapter.content_type === 'audio' && currentChapter.audio_url"
                            class="mt-6 border border-white/10 bg-[#101d2d] p-5"
                        >
                            <div class="flex items-center gap-4">
                                <span class="grid size-12 place-items-center bg-[#7d254a]">
                                    <LearningIcon name="play" class="size-5 brightness-0 invert" />
                                </span>
                                <div>
                                    <h3 class="font-semibold text-white">{{ currentChapter.title }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">{{ currentChapter.duration_minutes }} minutes</p>
                                </div>
                            </div>
                            <audio controls class="mt-5 w-full" controlslist="nodownload">
                                <source :src="mediaUrl(currentChapter.audio_url)" />
                                Votre navigateur ne supporte pas la lecture audio.
                            </audio>
                        </div>

                        <div
                            v-else-if="currentChapter.content_type === 'pdf' && currentChapter.media_url"
                            class="mt-6 overflow-hidden border border-white/10 bg-[#101d2d]"
                        >
                            <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
                                <div class="flex items-center gap-2 text-sm font-medium text-white">
                                    <LearningIcon name="document" class="size-4 brightness-0 invert" />
                                    Support PDF du chapitre
                                </div>
                                <a
                                    :href="mediaUrl(currentChapter.media_url)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs font-semibold text-[#ff79a5] hover:text-white"
                                >
                                    Ouvrir le document
                                </a>
                            </div>
                            <iframe
                                :src="mediaUrl(currentChapter.media_url)"
                                :title="currentChapter.title"
                                class="h-[68vh] min-h-[520px] w-full bg-white"
                            />
                        </div>

                        <div
                            v-if="htmlContent && currentChapter.content_type !== 'video'"
                            class="mt-6 border border-white/10 bg-[#101d2d] p-5 text-slate-200 sm:p-7"
                            v-html="htmlContent"
                        />

                        <ChapterExamCard
                            v-if="chapterExam"
                            :exam="chapterExam"
                            :passed="hasPassedExam"
                            :chapter-completed="currentChapterCompleted"
                            @take="takeExam"
                            @complete="markComplete"
                        />

                        <section
                            v-else-if="!currentChapterCompleted"
                            class="mt-8 flex flex-col gap-4 border border-white/10 bg-[#101d2d] p-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-[11px] font-semibold uppercase text-slate-500">Fin du chapitre</p>
                                <h3 class="mt-1 text-lg font-semibold text-white">Marquer cette étape comme terminée</h3>
                                <p class="mt-2 text-sm text-slate-400">
                                    Ce chapitre ne possède pas encore d'examen de validation.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex h-11 shrink-0 items-center justify-center gap-2 bg-[#a72f5d] px-5 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                                @click="markComplete"
                            >
                                <LearningIcon name="academic-cap" class="size-4 brightness-0 invert" />
                                Terminer le chapitre
                            </button>
                        </section>

                        <div class="mt-8 flex items-center justify-between gap-3 border-t border-white/10 pt-5">
                            <button
                                type="button"
                                :disabled="currentChapterIndex === 0"
                                class="inline-flex h-11 items-center gap-2 border border-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-35"
                                @click="goPrevious"
                            >
                                <LearningIcon name="arrow-left" class="size-4 brightness-0 invert" />
                                Précédent
                            </button>

                            <Link
                                v-if="isLastChapter && currentChapterCompleted"
                                :href="route('dashboard')"
                                class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                            >
                                Terminer la formation
                                <LearningIcon name="arrow-right" class="size-4 brightness-0 invert" />
                            </Link>
                            <button
                                v-else
                                type="button"
                                :disabled="!canGoNext"
                                class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d] disabled:cursor-not-allowed disabled:bg-white/5 disabled:text-slate-600"
                                :title="canGoNext ? 'Chapitre suivant' : 'Validez le chapitre avant de continuer'"
                                @click="goNext"
                            >
                                Suivant
                                <LearningIcon name="arrow-right" class="size-4 brightness-0 invert" />
                            </button>
                        </div>
                    </div>

                    <div v-else class="border border-dashed border-white/15 p-8 text-center">
                        <h2 class="text-lg font-semibold text-white">Aucun chapitre disponible</h2>
                        <p class="mt-2 text-sm text-slate-400">Le contenu de cette formation sera bientôt disponible.</p>
                    </div>
                </div>
            </main>

            <aside class="hidden w-80 shrink-0 overflow-y-auto border-l border-white/10 bg-[#081524] md:block lg:w-96">
                <div class="sticky top-0 z-10 border-b border-white/10 bg-[#081524] p-4">
                    <h3 class="font-semibold text-white">Contenu de la formation</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ completedChapters.length }} / {{ allChapters.length }} chapitres complétés
                    </p>
                </div>
                <div class="p-4">
                    <CourseCurriculum
                        :sections="formation.sections"
                        :current-chapter-id="currentChapter?.id ?? null"
                        :completed-chapters="completedChapters"
                        @select="selectChapter"
                    />
                </div>
            </aside>
        </div>

        <button
            type="button"
            class="fixed bottom-4 right-4 z-40 grid size-12 place-items-center bg-[#a72f5d] shadow-xl md:hidden"
            aria-label="Afficher le programme"
            @click="showCurriculum = true"
        >
            <LearningIcon name="bars-3" class="size-5 brightness-0 invert" />
        </button>

        <Teleport to="body">
            <div v-if="showCurriculum" class="fixed inset-0 z-50 md:hidden">
                <button
                    type="button"
                    class="absolute inset-0 bg-black/65"
                    aria-label="Fermer le programme"
                    @click="showCurriculum = false"
                />
                <aside class="absolute right-0 top-0 h-full w-[min(88vw,360px)] overflow-y-auto bg-[#081524]">
                    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-white/10 bg-[#081524] p-4">
                        <div>
                            <h3 class="font-semibold text-white">Contenu de la formation</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ completedChapters.length }} / {{ allChapters.length }} chapitres
                            </p>
                        </div>
                        <button
                            type="button"
                            class="grid size-9 place-items-center border border-white/10"
                            aria-label="Fermer"
                            @click="showCurriculum = false"
                        >
                            <LearningIcon name="x-mark" class="size-5 brightness-0 invert" />
                        </button>
                    </div>
                    <div class="p-4">
                        <CourseCurriculum
                            :sections="formation.sections"
                            :current-chapter-id="currentChapter?.id ?? null"
                            :completed-chapters="completedChapters"
                            @select="selectChapter"
                        />
                    </div>
                </aside>
            </div>
        </Teleport>
    </div>
</template>
