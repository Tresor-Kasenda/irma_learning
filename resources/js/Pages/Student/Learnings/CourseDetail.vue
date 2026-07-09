<script lang="ts" setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {computed} from 'vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import ContentFormatBadges from '@/Components/Learning/ContentFormatBadges.vue';
import type {LearningFormation} from '@/types/learning';
import {safeRoute} from '@/utilities/route';

interface ChapterData {
    id: number;
    title: string;
    content_type?: string | null;
    duration_minutes?: number | null;
}

interface SectionData {
    id: number;
    title: string;
    chapters: ChapterData[];
    exam?: {
        id: number;
        title: string;
        is_active: boolean;
    } | null;
}

interface FormationDetail extends LearningFormation {
    sections?: SectionData[];
}

interface EnrollmentData {
    id: number;
    status?: string | null;
    progress_percentage?: number | string | null;
    completion_date?: string | null;
}

interface CertificateData {
    id: number;
    certificate_number: string;
    final_score?: number | string | null;
    issue_date?: string | null;
}

const props = defineProps<{
    formation: FormationDetail;
    chapterCount: number;
    enrollment: EnrollmentData | null;
    completedChapterIds: number[];
    continueChapterId: number | null;
    learningProgress: number | string;
    certificate: CertificateData | null;
}>();

const isEnrolled = computed(() => Boolean(props.enrollment));
const progress = computed(() => Math.round(Number(props.learningProgress ?? props.enrollment?.progress_percentage ?? 0)));
const isCompleted = computed(
    () => props.enrollment?.status === 'completed' || progress.value >= 100,
);

const sections = computed(() => props.formation.sections ?? []);
const continueHref = computed(() => {
    const params: Record<string, number> = {formation: props.formation.id};

    if (props.continueChapterId) {
        params.chapterId = props.continueChapterId;
    }

    return safeRoute('course.player', params);
});

const enrollForm = useForm({});

function startLearning(): void {
    enrollForm.post(safeRoute('formation.enroll', props.formation.id));
}

const fallbackImages = [
    '/images/image1.webp',
    '/images/image2.webp',
    '/images/course-2.jpg',
    '/images/home_bg.jpg',
];

function imageUrl(): string {
    const image = props.formation.image;

    if (!image) {
        return fallbackImages[props.formation.id % fallbackImages.length];
    }

    if (/^https?:\/\//.test(image) || image.startsWith('/')) {
        return image;
    }

    return `/storage/${image}`;
}

function replaceBrokenImage(event: Event): void {
    (event.target as HTMLImageElement).src = fallbackImages[props.formation.id % fallbackImages.length];
}

function difficultyLabel(): string {
    const labels: Record<string, string> = {
        beginner: 'Débutant',
        intermediate: 'Intermédiaire',
        advanced: 'Avancé',
    };

    return labels[props.formation.difficulty_level] ?? props.formation.difficulty_level;
}

function formatDuration(): string {
    const hours = props.formation.duration_hours;

    if (!hours) {
        return 'À votre rythme';
    }

    const wholeHours = Math.floor(hours);
    const minutes = Math.round((hours - wholeHours) * 60);

    return minutes > 0 ? `${wholeHours} h ${minutes} min` : `${wholeHours} h`;
}

function chapterIcon(type?: string | null): string {
    if (type === 'video') {
        return 'video-camera';
    }

    if (type === 'pdf') {
        return 'document';
    }

    return 'document-text';
}

function isChapterDone(id: number): boolean {
    return props.completedChapterIds.includes(id);
}
</script>

<template>
    <Head :title="formation.title"/>

    <LearningLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('student.learnings')" class="text-slate-500 transition hover:text-slate-300">
                Formations
            </Link>
            <span class="text-slate-600">/</span>
            <span class="line-clamp-1 text-slate-300">{{ formation.title }}</span>
        </template>

        <div class="mx-auto max-w-6xl px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
            <!-- État terminé / certifié -->
            <div
                v-if="isCompleted"
                class="mb-6 flex flex-col gap-4 border border-emerald-500/30 bg-emerald-500/10 p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-emerald-500/20">
                        <LearningIcon class="size-5 brightness-0 invert" name="check"/>
                    </span>
                    <div>
                        <p class="text-base font-semibold text-white">Formation terminée</p>
                        <p class="mt-0.5 text-sm text-emerald-200/80">
                            {{
                                certificate
                                    ? 'Félicitations, cette formation est certifiée.'
                                    : 'Vous avez terminé tous les chapitres de cette formation.'
                            }}
                        </p>
                    </div>
                </div>
                <Link
                    v-if="certificate"
                    :href="safeRoute('certificats.show', certificate.id)"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 bg-emerald-500 px-5 text-sm font-semibold text-white transition hover:bg-emerald-400"
                >
                    <LearningIcon class="size-4 brightness-0 invert" name="academic-cap"/>
                    Voir la certification
                </Link>
            </div>

            <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
                <!-- Contenu principal -->
                <div>
                    <div class="relative aspect-[16/7] w-full overflow-hidden border border-white/10">
                        <img
                            :alt="formation.title"
                            :src="imageUrl()"
                            class="size-full object-cover"
                            @error="replaceBrokenImage"
                        />
                    </div>

                    <h1 class="mt-6 text-3xl font-semibold leading-tight text-white sm:text-4xl">
                        {{ formation.title }}
                    </h1>
                    <p class="mt-3 text-base leading-7 text-slate-400">
                        {{ formation.short_description ?? formation.description }}
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-400">
                        <span class="inline-flex items-center gap-1.5">
                            <LearningIcon class="size-4 brightness-0 invert opacity-60" name="academic-cap"/>
                            {{ difficultyLabel() }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <LearningIcon class="size-4 brightness-0 invert opacity-60" name="clock"/>
                            {{ formatDuration() }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <LearningIcon class="size-4 brightness-0 invert opacity-60" name="document-text"/>
                            {{ chapterCount }} ressources
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <LearningIcon class="size-4 brightness-0 invert opacity-60" name="user"/>
                            {{ formation.students_count ?? 0 }} apprenants
                        </span>
                    </div>

                    <!-- Sommaire -->
                    <div class="mt-8 border-t border-white/10 pt-6">
                        <h2 class="text-lg font-semibold text-white">Programme</h2>

                        <div v-if="sections.length > 0" class="mt-4 space-y-4">
                            <div
                                v-for="(section, index) in sections"
                                :key="section.id"
                                class="border border-white/10 bg-[#101d2d]"
                            >
                                <div class="flex items-center gap-3 border-b border-white/10 px-4 py-3">
                                    <span class="grid size-7 shrink-0 place-items-center bg-white/5 text-xs font-semibold text-slate-300">
                                        {{ index + 1 }}
                                    </span>
                                    <h3 class="text-sm font-semibold text-white">{{ section.title }}</h3>
                                    <span class="ml-auto text-xs text-slate-500">
                                        {{ section.chapters.length }}
                                        {{ section.chapters.length > 1 ? 'chapitres' : 'chapitre' }}
                                    </span>
                                </div>
                                <ul class="divide-y divide-white/5">
                                    <li
                                        v-for="chapter in section.chapters"
                                        :key="chapter.id"
                                        class="flex items-center gap-3 px-4 py-3"
                                    >
                                        <LearningIcon
                                            :class="isChapterDone(chapter.id) ? 'opacity-90' : 'opacity-50'"
                                            :name="isChapterDone(chapter.id) ? 'check' : chapterIcon(chapter.content_type)"
                                            class="size-4 shrink-0 brightness-0 invert"
                                        />
                                        <span
                                            :class="isChapterDone(chapter.id) ? 'text-slate-300' : 'text-slate-400'"
                                            class="line-clamp-1 text-sm"
                                        >
                                            {{ chapter.title }}
                                        </span>
                                        <span
                                            v-if="chapter.duration_minutes"
                                            class="ml-auto shrink-0 text-xs text-slate-500"
                                        >
                                            {{ chapter.duration_minutes }} min
                                        </span>
                                    </li>
                                    <li
                                        class="flex items-center gap-3 bg-amber-400/5 px-4 py-3 text-amber-100"
                                    >
                                        <span class="grid size-6 shrink-0 place-items-center bg-amber-400/15">
                                            <LearningIcon
                                                class="size-3.5 brightness-0 invert"
                                                name="academic-cap"
                                            />
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold">
                                                {{ section.exam?.title ?? 'Évaluation de la section' }}
                                            </span>
                                            <span class="mt-1 block text-[11px] text-amber-100/70">
                                                Obligatoire pour débloquer la section suivante.
                                            </span>
                                        </span>
                                        <span
                                            :class="section.exam?.is_active ? 'border-amber-300/30 text-amber-100' : 'border-rose-300/30 text-rose-200'"
                                            class="shrink-0 border px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em]"
                                        >
                                            {{ section.exam?.is_active ? 'Prévue' : 'À configurer' }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <p v-else class="mt-3 text-sm text-slate-500">Le programme sera bientôt disponible.</p>
                    </div>
                </div>

                <!-- Carte d'action -->
                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <div class="border border-white/10 bg-[#101d2d] p-5">
                        <ContentFormatBadges
                            :pdfs="formation.pdf_count"
                            :texts="formation.text_count"
                            :videos="formation.video_count"
                        />

                        <div v-if="isEnrolled && !isCompleted" class="mt-5">
                            <div class="mb-2 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Progression</span>
                                <span class="font-semibold text-[#ff79a5]">{{ progress }}%</span>
                            </div>
                            <div class="h-1.5 bg-white/10">
                                <div :style="{ width: `${progress}%` }" class="h-full bg-[#df3e75]"/>
                            </div>
                        </div>

                        <!-- Non inscrit -->
                        <button
                            v-if="!isEnrolled"
                            :disabled="enrollForm.processing"
                            class="mt-5 inline-flex h-11 w-full items-center justify-center gap-2 bg-[#a72f5d] px-5 text-sm font-semibold text-white transition hover:bg-[#c43b6d] disabled:opacity-60"
                            type="button"
                            @click="startLearning"
                        >
                            <LearningIcon class="size-4 brightness-0 invert" name="play"/>
                            {{ enrollForm.processing ? 'Traitement…' : 'Commencer la formation' }}
                        </button>

                        <!-- En cours -->
                        <Link
                            v-else-if="!isCompleted"
                            :href="continueHref"
                            class="mt-5 inline-flex h-11 w-full items-center justify-center gap-2 bg-[#a72f5d] px-5 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                        >
                            <LearningIcon class="size-4 brightness-0 invert" name="play"/>
                            Continuer la formation
                        </Link>

                        <!-- Terminé -->
                        <template v-else>
                            <Link
                                v-if="certificate"
                                :href="safeRoute('certificats.show', certificate.id)"
                                class="mt-5 inline-flex h-11 w-full items-center justify-center gap-2 bg-emerald-500 px-5 text-sm font-semibold text-white transition hover:bg-emerald-400"
                            >
                                <LearningIcon class="size-4 brightness-0 invert" name="academic-cap"/>
                                Voir la certification
                            </Link>
                            <Link
                                :href="safeRoute('course.player', formation.id)"
                                class="mt-3 inline-flex h-11 w-full items-center justify-center gap-2 border border-white/15 px-5 text-sm font-semibold text-white transition hover:bg-white/5"
                            >
                                Revoir la formation
                            </Link>
                        </template>
                    </div>
                </aside>
            </div>
        </div>
    </LearningLayout>
</template>
