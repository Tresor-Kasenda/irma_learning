<script lang="ts" setup>
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import {safeRoute} from '@/utilities/route';
import {Head, Link} from '@inertiajs/vue3';
import {computed} from 'vue';

interface QuestionOption {
    id: number;
    option_text: string;
    is_correct: boolean;
    image: string | null;
}

interface Question {
    id: number;
    question_text: string;
    question_type: string;
    points: number;
    explanation: string | null;
    options: QuestionOption[];
}

interface SelectedOption {
    id: number;
    option_text: string;
}

interface UserAnswer {
    id: number;
    question_id: number;
    is_correct: boolean | null;
    points_earned: number;
    selected_option_id: number | null;
    selected_options: number[] | null;
    answer_text: string | null;
    question: Question;
    selectedOption: SelectedOption | null;
}

interface Attempt {
    id: number;
    score: number;
    max_score: number;
    percentage: number;
    passed: boolean;
    completed_at: string | null;
    time_taken: number | null;
}

interface Exam {
    id: number;
    title: string;
    passing_score: number;
}

interface FormationContext {
    id: number;
    title: string;
    slug: string;
}

interface ExamContext {
    type: string;
    label: string;
    parent_title: string | null;
}

interface CourseCompletion {
    formation_id: number;
    chapter_id: number;
    formation_title: string;
    chapter_title: string;
}

interface NextStep {
    type: 'next_section' | 'final_exam' | 'final_exam_missing' | 'completed' | 'continue' | 'retry';
    formation_id?: number;
    chapter_id?: number;
    exam_id?: number;
}

interface CertificateInfo {
    id: number;
    certificate_number: string;
    final_score: number | string;
}

const props = defineProps<{
    attempt: Attempt;
    exam: Exam;
    formation: FormationContext | null;
    examContext: ExamContext;
    userAnswers: UserAnswer[];
    canRetry: boolean;
    courseCompletion: CourseCompletion | null;
    nextStep: NextStep | null;
    certificate: CertificateInfo | null;
}>();

const backHref = computed(() => props.formation ? safeRoute('course.player', props.formation.id) : safeRoute('dashboard'));
const correctCount = computed(() => props.userAnswers.filter(answer => answer.is_correct === true).length);
const incorrectCount = computed(() => props.userAnswers.filter(answer => answer.is_correct === false).length);
const resultTone = computed(() => props.attempt.passed ? 'success' : 'danger');
const resultLabel = computed(() => props.attempt.passed ? 'Évaluation réussie' : 'Évaluation non validée');

function isOptionSelected(answer: UserAnswer, optionId: number): boolean {
    if (answer.question.question_type === 'multiple_choice') {
        const selected = answer.selected_options;

        return Array.isArray(selected) && selected.includes(optionId);
    }

    return answer.selected_option_id === optionId;
}

function optionClasses(answer: UserAnswer, option: QuestionOption): string {
    const selected = isOptionSelected(answer, option.id);

    if (option.is_correct) {
        return 'border-emerald-400/35 bg-emerald-400/10 text-emerald-50';
    }

    if (selected) {
        return 'border-rose-400/35 bg-rose-400/10 text-rose-50';
    }

    return 'border-white/10 bg-[#0b1929] text-slate-300';
}

function optionMeta(answer: UserAnswer, option: QuestionOption): string {
    const selected = isOptionSelected(answer, option.id);

    if (option.is_correct && selected) {
        return 'Votre réponse — correcte';
    }

    if (option.is_correct) {
        return 'Réponse correcte';
    }

    if (selected) {
        return 'Votre réponse';
    }

    return '';
}

function questionStatusLabel(answer: UserAnswer): string {
    if (answer.is_correct === true) {
        return 'Correcte';
    }

    if (answer.is_correct === false) {
        return 'À revoir';
    }

    return 'Non corrigée';
}

function questionTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        single_choice: 'Choix unique',
        multiple_choice: 'Choix multiple',
        true_false: 'Vrai / Faux',
    };

    return labels[type] ?? 'Question';
}

function formatDate(date: string | null): string {
    if (! date) {
        return '';
    }

    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatMinutes(value: number | null): string {
    if (! value) {
        return '0 min';
    }

    return `${Number(value).toLocaleString('fr-FR', {
        maximumFractionDigits: 1,
    })} min`;
}
</script>

<template>
    <Head title="Résultats de l'examen"/>

    <div class="flex min-h-screen flex-col bg-[#071525] text-slate-100">
        <header class="sticky top-0 z-40 border-b border-white/10 bg-[#081524]">
            <div class="flex min-h-16 items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <Link
                        :href="backHref"
                        class="grid size-10 shrink-0 place-items-center border border-white/10 transition hover:bg-white/5"
                        title="Retour à la formation"
                    >
                        <LearningIcon class="size-5 brightness-0 invert" name="arrow-left"/>
                    </Link>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[#ff79a5]">
                            Résultats · {{ examContext.label }}
                        </p>
                        <h1 class="truncate text-sm font-semibold text-white sm:text-base">{{ exam.title }}</h1>
                        <p v-if="formation" class="mt-0.5 truncate text-xs text-slate-500">{{ formation.title }}</p>
                    </div>
                </div>
                <div
                    :class="attempt.passed ? 'border-emerald-400/30 text-emerald-200' : 'border-rose-400/30 text-rose-200'"
                    class="shrink-0 border px-3 py-1.5 text-xs font-semibold"
                >
                    {{ resultLabel }}
                </div>
            </div>
        </header>

        <main class="min-w-0 flex-1">
            <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
                <section
                    :class="resultTone === 'success' ? 'border-emerald-400/30 bg-emerald-400/10' : 'border-[#df3e75]/35 bg-[#3a1530]'"
                    class="border p-5 sm:p-6"
                >
                    <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#ff79a5]">
                                {{ resultLabel }}
                            </p>
                            <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">{{ exam.title }}</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-300">
                                Score minimum : {{ exam.passing_score }}%
                                <span v-if="attempt.completed_at"> · Terminé le {{ formatDate(attempt.completed_at) }}</span>
                            </p>
                        </div>
                        <div class="text-left lg:text-right">
                            <p class="text-5xl font-semibold text-white">{{ Math.round(attempt.percentage) }}%</p>
                            <p class="mt-1 text-sm text-slate-300">{{ attempt.score }} / {{ attempt.max_score }} points</p>
                        </div>
                    </div>

                    <div class="mt-5 h-2 bg-white/10">
                        <div
                            :class="attempt.passed ? 'bg-emerald-400' : 'bg-[#df3e75]'"
                            class="h-full transition-all duration-500"
                            :style="{width: `${Math.min(100, Math.max(0, attempt.percentage))}%`}"
                        />
                    </div>
                </section>

                <section class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="border border-white/10 bg-[#101d2d] p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center bg-emerald-400/15">
                                <LearningIcon class="size-5 brightness-0 invert" name="check"/>
                            </span>
                            <div>
                                <p class="text-2xl font-semibold text-white">{{ correctCount }}</p>
                                <p class="text-sm text-slate-400">Réponses correctes</p>
                            </div>
                        </div>
                    </div>
                    <div class="border border-white/10 bg-[#101d2d] p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center bg-[#df3e75]/15">
                                <LearningIcon class="size-5 brightness-0 invert" name="x-mark"/>
                            </span>
                            <div>
                                <p class="text-2xl font-semibold text-white">{{ incorrectCount }}</p>
                                <p class="text-sm text-slate-400">Réponses incorrectes</p>
                            </div>
                        </div>
                    </div>
                    <div class="border border-white/10 bg-[#101d2d] p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center bg-sky-400/15">
                                <LearningIcon class="size-5 brightness-0 invert" name="clock"/>
                            </span>
                            <div>
                                <p class="text-2xl font-semibold text-white">{{ formatMinutes(attempt.time_taken) }}</p>
                                <p class="text-sm text-slate-400">Temps écoulé</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#ff79a5]">Correction</p>
                            <h2 class="mt-1 text-xl font-semibold text-white">Détail des réponses</h2>
                        </div>
                        <p class="text-sm text-slate-500">{{ userAnswers.length }} question(s)</p>
                    </div>

                    <div class="grid gap-3">
                        <article
                            v-for="(answer, index) in userAnswers"
                            :key="answer.id"
                            :class="answer.is_correct === true ? 'border-l-emerald-400' : answer.is_correct === false ? 'border-l-[#df3e75]' : 'border-l-slate-500'"
                            class="border border-l-4 border-white/10 bg-[#101d2d]"
                        >
                            <div class="grid gap-4 p-4 sm:grid-cols-[1fr_auto]">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            :class="answer.is_correct === true ? 'bg-emerald-400/15 text-emerald-200' : answer.is_correct === false ? 'bg-[#df3e75]/15 text-rose-200' : 'bg-white/10 text-slate-300'"
                                            class="px-2.5 py-1 text-xs font-semibold"
                                        >
                                            Question {{ index + 1 }} · {{ questionStatusLabel(answer) }}
                                        </span>
                                        <span class="text-xs text-slate-500">{{ questionTypeLabel(answer.question.question_type) }}</span>
                                    </div>
                                    <h3 class="mt-3 text-base font-semibold leading-7 text-white">
                                        {{ answer.question.question_text }}
                                    </h3>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-xs uppercase tracking-[0.08em] text-slate-500">Points</p>
                                    <p class="mt-1 text-lg font-semibold text-white">
                                        {{ answer.points_earned }} / {{ answer.question.points }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-2 border-t border-white/10 p-4 pt-3">
                                <template v-if="['single_choice', 'multiple_choice', 'true_false'].includes(answer.question.question_type)">
                                    <div
                                        v-for="option in answer.question.options"
                                        :key="option.id"
                                        :class="optionClasses(answer, option)"
                                        class="flex min-w-0 items-start gap-3 border px-3 py-2.5 text-sm"
                                    >
                                        <LearningIcon
                                            v-if="option.is_correct"
                                            class="mt-0.5 size-4 shrink-0 brightness-0 invert"
                                            name="check"
                                        />
                                        <LearningIcon
                                            v-else-if="isOptionSelected(answer, option.id)"
                                            class="mt-0.5 size-4 shrink-0 brightness-0 invert"
                                            name="x-mark"
                                        />
                                        <span v-else class="mt-1.5 size-2.5 shrink-0 border border-white/20"/>
                                        <span class="min-w-0 flex-1 leading-6">{{ option.option_text }}</span>
                                        <span
                                            v-if="optionMeta(answer, option)"
                                            class="ml-auto shrink-0 pl-3 text-xs text-slate-400"
                                        >
                                            {{ optionMeta(answer, option) }}
                                        </span>
                                    </div>
                                </template>

                                <div v-else class="border border-white/10 bg-[#0b1929] px-3 py-2.5 text-sm text-slate-300">
                                    {{ answer.answer_text || 'Aucune réponse fournie' }}
                                </div>

                                <div
                                    v-if="answer.question.explanation"
                                    class="mt-2 border border-sky-400/25 bg-sky-400/10 px-3 py-3 text-sm leading-6 text-sky-100"
                                >
                                    <p class="font-semibold text-sky-200">Explication</p>
                                    <p class="mt-1 text-slate-300">{{ answer.question.explanation }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-white/10 pt-5">
                    <Link
                        :href="backHref"
                        class="inline-flex h-11 items-center gap-2 border border-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/5"
                    >
                        <LearningIcon class="size-4 brightness-0 invert" name="arrow-left"/>
                        Retour à la formation
                    </Link>

                    <Link
                        v-if="attempt.passed && courseCompletion"
                        :href="safeRoute('course.chapter.complete', {
                            formation: courseCompletion.formation_id,
                            chapter: courseCompletion.chapter_id,
                        })"
                        as="button"
                        class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                        method="post"
                    >
                        Valider et continuer
                        <LearningIcon class="size-4 brightness-0 invert" name="arrow-right"/>
                    </Link>

                    <Link
                        v-if="attempt.passed && nextStep?.type === 'next_section' && nextStep.chapter_id"
                        :href="safeRoute('course.player', {
                            formation: nextStep.formation_id,
                            chapterId: nextStep.chapter_id,
                        })"
                        class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                    >
                        Continuer
                        <LearningIcon class="size-4 brightness-0 invert" name="arrow-right"/>
                    </Link>

                    <Link
                        v-else-if="attempt.passed && nextStep?.type === 'final_exam' && nextStep.exam_id"
                        :href="safeRoute('exam.take', nextStep.exam_id)"
                        class="inline-flex h-11 items-center gap-2 bg-amber-400 px-4 text-sm font-semibold text-slate-950 transition hover:bg-amber-300"
                    >
                        Passer l’examen final
                        <LearningIcon class="size-4" name="arrow-right"/>
                    </Link>

                    <Link
                        v-else-if="attempt.passed && nextStep?.type === 'completed' && certificate"
                        :href="safeRoute('certificats.show', certificate.id)"
                        class="inline-flex h-11 items-center gap-2 bg-emerald-500 px-4 text-sm font-semibold text-white transition hover:bg-emerald-400"
                    >
                        Voir mon certificat
                        <LearningIcon class="size-4 brightness-0 invert" name="document-text"/>
                    </Link>

                    <Link
                        v-if="canRetry"
                        :href="safeRoute('exam.take', exam.id)"
                        class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-4 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                    >
                        Réessayer
                        <LearningIcon class="size-4 brightness-0 invert" name="arrow-right"/>
                    </Link>
                </div>

                <p
                    v-if="attempt.passed && nextStep?.type === 'final_exam_missing'"
                    class="mt-5 border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-100"
                >
                    L’examen final n’est pas encore configuré. Contactez l’administration.
                </p>

                <p
                    v-if="attempt.passed && nextStep?.type === 'completed' && certificate"
                    class="mt-5 border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100"
                >
                    Félicitations. Le certificat
                    <span class="font-semibold">{{ certificate.certificate_number }}</span>
                    a été délivré.
                </p>
            </div>
        </main>
    </div>
</template>
