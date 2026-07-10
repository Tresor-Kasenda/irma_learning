<script setup lang="ts">
import {Head, Link, router} from '@inertiajs/vue3';
import {computed, onMounted, onUnmounted, ref} from 'vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import {safeRoute} from '@/utilities/route';

interface Exam {
    id: number;
    title: string;
    description: string | null;
    instructions: string | null;
    duration_minutes: number | null;
    passing_score: number;
    examable_type: string | null;
    show_results_immediately: boolean | null;
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

interface QuestionOption {
    id: number;
    option_text: string;
    image: string | null;
}

interface Question {
    id: number;
    question_text: string;
    question_type: string;
    points: number;
    image: string | null;
    explanation: string | null;
    is_required: boolean | null;
    options: QuestionOption[];
}

const props = defineProps<{
    exam: Exam;
    formation: FormationContext | null;
    examContext: ExamContext;
    questions: Question[];
    attempt: { id: number; started_at: string; status: string };
    existingAnswers: Record<number, unknown>;
    timeRemaining: number | null;
}>();

const answers = ref<Record<number, any>>({ ...props.existingAnswers });
const currentQuestionIndex = ref(0);
const remaining = ref(props.timeRemaining);
const isSubmitting = ref(false);
const showSubmitConfirm = ref(false);
const notice = ref<{type: 'info' | 'warning'; message: string} | null>(null);

const currentQuestion = computed(() => props.questions[currentQuestionIndex.value] ?? null);
const backHref = computed(() => props.formation ? safeRoute('course.player', props.formation.id) : safeRoute('dashboard'));
const saveAnswerHref = computed(() => safeRoute('exam.save-answer', props.exam.id));

const progress = computed(() => {
    const answered = Object.values(answers.value).filter(v => {
        if (v === null || v === undefined) return false;
        if (Array.isArray(v)) return v.length > 0;
        if (typeof v === 'string') return v.trim() !== '';
        return true;
    }).length;
    return props.questions.length > 0 ? (answered / props.questions.length) * 100 : 0;
});

const answeredCount = computed(() => {
    return Object.values(answers.value).filter(v => {
        if (v === null || v === undefined) return false;
        if (Array.isArray(v)) return v.length > 0;
        if (typeof v === 'string') return v.trim() !== '';
        return true;
    }).length;
});

function isAnswered(questionId: number): boolean {
    const v = answers.value[questionId];
    if (v === null || v === undefined) return false;
    if (Array.isArray(v)) return v.length > 0;
    if (typeof v === 'string') return v.trim() !== '';
    return true;
}

const currentQuestionAnswered = computed(() => {
    return currentQuestion.value ? isAnswered(currentQuestion.value.id) : false;
});

const questionInstruction = computed(() => {
    if (! currentQuestion.value) {
        return '';
    }

    const instructions: Record<string, string> = {
        single_choice: 'Choisissez une seule réponse.',
        multiple_choice: 'Sélectionnez toutes les réponses correctes.',
        true_false: 'Choisissez Vrai ou Faux.',
    };

    return instructions[currentQuestion.value.question_type] ?? 'Répondez à la question.';
});

let timerInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (remaining.value !== null && remaining.value > 0) {
        timerInterval = setInterval(() => {
            if (remaining.value !== null) {
                remaining.value--;

                if (remaining.value <= 0) {
                    if (timerInterval) clearInterval(timerInterval);
                    notice.value = {type: 'warning', message: 'Temps écoulé. Soumission automatique en cours.'};
                    submitExam(true);
                } else if (remaining.value === 300) {
                    notice.value = {type: 'info', message: 'Il vous reste 5 minutes.'};
                } else if (remaining.value === 60) {
                    notice.value = {type: 'warning', message: 'Il vous reste 1 minute.'};
                }
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

function formatTime(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function csrfHeaders(): Record<string, string> {
    const metaToken = document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.content;

    if (metaToken) {
        return {'X-CSRF-TOKEN': metaToken};
    }

    const token = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];

    return token ? {'X-XSRF-TOKEN': decodeURIComponent(token)} : {};
}

async function saveCurrentAnswer(): Promise<boolean> {
    if (!currentQuestion.value) return true;
    const answer = answers.value[currentQuestion.value.id] ?? null;

    try {
        const response = await fetch(saveAnswerHref.value, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...csrfHeaders(),
            },
            body: JSON.stringify({
                question_id: currentQuestion.value.id,
                answer,
            }),
        });

        if (! response.ok) {
            notice.value = {
                type: 'warning',
                message: 'La réponse n’a pas été enregistrée. Vérifiez votre connexion puis réessayez.',
            };

            return false;
        }

        return true;
    } catch {
        notice.value = {
            type: 'warning',
            message: 'La réponse n’a pas été enregistrée. Vérifiez votre connexion puis réessayez.',
        };

        return false;
    }
}

async function nextQuestion(): Promise<void> {
    if (! await saveCurrentAnswer()) {
        return;
    }

    if (currentQuestionIndex.value < props.questions.length - 1) {
        currentQuestionIndex.value++;
    }
}

async function previousQuestion(): Promise<void> {
    if (! await saveCurrentAnswer()) {
        return;
    }

    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
}

async function goToQuestion(index: number): Promise<void> {
    if (! await saveCurrentAnswer()) {
        return;
    }

    currentQuestionIndex.value = index;
}

async function requestSubmit(): Promise<void> {
    if (! currentQuestionAnswered.value) {
        notice.value = {
            type: 'warning',
            message: 'Répondez d’abord à la question affichée avant de soumettre l’examen.',
        };

        return;
    }

    if (! await saveCurrentAnswer()) {
        return;
    }

    showSubmitConfirm.value = true;
}

async function submitExam(skipConfirmation = false): Promise<void> {
    if (isSubmitting.value) return;

    if (!skipConfirmation && !showSubmitConfirm.value) {
        await requestSubmit();
        return;
    }

    isSubmitting.value = true;
    showSubmitConfirm.value = false;

    if (timerInterval) clearInterval(timerInterval);

    if (! await saveCurrentAnswer()) {
        isSubmitting.value = false;
        return;
    }

    router.post(route('exam.submit', props.exam.id));
}

function toggleMultipleChoice(optionId: number) {
    if (!currentQuestion.value) return;
    const qid = currentQuestion.value.id;
    const current = answers.value[qid];

    if (!Array.isArray(current)) {
        answers.value[qid] = [optionId];
        return;
    }

    const idx = current.indexOf(optionId);
    if (idx >= 0) {
        current.splice(idx, 1);
    } else {
        current.push(optionId);
    }
}

function getMediaUrl(url: string | null): string {
    if (!url) return '';
    if (url.startsWith('http')) return url;
    return '/storage/' + url;
}

function isMultipleSelected(questionId: number, optionId: number): boolean {
    const selected = answers.value[questionId];

    return Array.isArray(selected) && selected.includes(optionId);
}

function questionTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        single_choice: 'Choix unique',
        multiple_choice: 'Choix multiple',
        true_false: 'Vrai / Faux',
    };

    return labels[type] ?? 'Question';
}
</script>

<template>
    <Head title="Passer l'examen"/>

    <div class="flex h-screen min-h-0 flex-col overflow-hidden bg-[#071525] text-slate-100">
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
                            {{ examContext.label }}
                        </p>
                        <h1 class="truncate text-sm font-semibold text-white sm:text-base">{{ exam.title }}</h1>
                        <p v-if="formation" class="mt-0.5 truncate text-xs text-slate-500">{{ formation.title }}</p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div
                        v-if="remaining !== null"
                        class="hidden items-center gap-2 border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-white sm:flex"
                    >
                        <LearningIcon class="size-4 brightness-0 invert" name="clock"/>
                        <span class="font-mono">{{ formatTime(remaining) }}</span>
                    </div>
                    <div class="hidden items-center gap-2 md:flex">
                        <span class="text-xs text-slate-400">{{ answeredCount }} / {{ questions.length }} répondues</span>
                        <div class="h-1.5 w-32 bg-white/10">
                            <div class="h-full bg-[#df3e75] transition-all duration-500" :style="{width: `${progress}%`}"/>
                        </div>
                        <span class="w-9 text-right text-xs font-semibold text-white">{{ Math.round(progress) }}%</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-0 flex-1">
            <main class="min-w-0 flex-1 overflow-y-auto">
                <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
                    <div
                        v-if="notice"
                        :class="notice.type === 'warning' ? 'border-amber-400/30 bg-amber-400/10 text-amber-100' : 'border-sky-400/30 bg-sky-400/10 text-sky-100'"
                        class="mb-5 flex items-start gap-3 border p-4 text-sm"
                    >
                        <LearningIcon class="mt-0.5 size-5 shrink-0 brightness-0 invert" name="clock"/>
                        <p class="leading-6">{{ notice.message }}</p>
                    </div>

                    <section v-if="exam.description || exam.instructions" class="mb-6 border border-white/10 bg-[#101d2d] p-5">
                        <p v-if="examContext.parent_title" class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
                            {{ examContext.parent_title }}
                        </p>
                        <p v-if="exam.description" class="mt-2 text-sm leading-6 text-slate-300">{{ exam.description }}</p>
                        <p v-if="exam.instructions" class="mt-3 border-t border-white/10 pt-3 text-sm leading-6 text-slate-400">
                            {{ exam.instructions }}
                        </p>
                    </section>

                    <section v-if="currentQuestion" class="border border-white/10 bg-[#101d2d]">
                        <div class="border-b border-white/10 p-5 sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#ff79a5]">
                                        Question {{ currentQuestionIndex + 1 }} sur {{ questions.length }}
                                    </p>
                                    <p class="mt-2 text-sm text-slate-500">{{ questionTypeLabel(currentQuestion.question_type) }}</p>
                                </div>
                                <span
                                    :class="currentQuestionAnswered ? 'border-emerald-400/30 text-emerald-200' : 'border-white/10 text-slate-400'"
                                    class="border px-3 py-1 text-xs font-semibold"
                                >
                                    {{ currentQuestionAnswered ? 'Répondue' : 'Non répondue' }}
                                </span>
                            </div>
                            <h2 class="mt-5 text-xl font-semibold leading-8 text-white">{{ currentQuestion.question_text }}</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-400">{{ questionInstruction }}</p>
                        </div>

                        <div class="grid gap-4 p-5 sm:p-6">
                            <img
                                v-if="currentQuestion.image"
                                :src="getMediaUrl(currentQuestion.image)"
                                alt="Illustration de la question"
                                class="max-h-80 w-full border border-white/10 object-contain"
                            />

                            <template v-if="currentQuestion.question_type === 'single_choice'">
                                <label
                                    v-for="option in currentQuestion.options"
                                    :key="option.id"
                                    :class="answers[currentQuestion.id] === option.id ? 'border-[#df3e75] bg-[#7d254a]/35' : 'border-white/10 bg-[#0b1929] hover:border-white/20 hover:bg-white/5'"
                                    class="flex cursor-pointer items-start gap-3 border p-4 transition"
                                >
                                    <input
                                        :checked="answers[currentQuestion.id] === option.id"
                                        :name="'q_' + currentQuestion.id"
                                        :value="option.id"
                                        class="mt-1 size-4 border-white/30 bg-[#071525] text-[#df3e75] focus:ring-[#df3e75]"
                                        type="radio"
                                        @change="answers[currentQuestion.id] = option.id"
                                    />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm leading-6 text-white">{{ option.option_text }}</span>
                                        <img
                                            v-if="option.image"
                                            :src="getMediaUrl(option.image)"
                                            alt="Illustration de l’option"
                                            class="mt-3 max-h-56 w-full border border-white/10 object-contain"
                                        />
                                    </span>
                                </label>
                            </template>

                            <template v-else-if="currentQuestion.question_type === 'multiple_choice'">
                                <label
                                    v-for="option in currentQuestion.options"
                                    :key="option.id"
                                    :class="isMultipleSelected(currentQuestion.id, option.id) ? 'border-[#df3e75] bg-[#7d254a]/35' : 'border-white/10 bg-[#0b1929] hover:border-white/20 hover:bg-white/5'"
                                    class="flex cursor-pointer items-start gap-3 border p-4 transition"
                                >
                                    <input
                                        :checked="isMultipleSelected(currentQuestion.id, option.id)"
                                        class="mt-1 size-4 border-white/30 bg-[#071525] text-[#df3e75] focus:ring-[#df3e75]"
                                        type="checkbox"
                                        @change="toggleMultipleChoice(option.id)"
                                    />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm leading-6 text-white">{{ option.option_text }}</span>
                                        <img
                                            v-if="option.image"
                                            :src="getMediaUrl(option.image)"
                                            alt="Illustration de l’option"
                                            class="mt-3 max-h-56 w-full border border-white/10 object-contain"
                                        />
                                    </span>
                                </label>
                            </template>

                            <template v-else-if="currentQuestion.question_type === 'true_false'">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label
                                        v-for="option in currentQuestion.options"
                                        :key="option.id"
                                        :class="answers[currentQuestion.id] === option.id ? 'border-[#df3e75] bg-[#7d254a]/35' : 'border-white/10 bg-[#0b1929] hover:border-white/20 hover:bg-white/5'"
                                        class="flex cursor-pointer items-center gap-3 border p-5 transition"
                                    >
                                        <input
                                            :checked="answers[currentQuestion.id] === option.id"
                                            :name="'q_' + currentQuestion.id"
                                            :value="option.id"
                                            class="size-4 border-white/30 bg-[#071525] text-[#df3e75] focus:ring-[#df3e75]"
                                            type="radio"
                                            @change="answers[currentQuestion.id] = option.id"
                                        />
                                        <span class="text-base font-semibold text-white">{{ option.option_text }}</span>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </section>

                    <div v-else class="border border-dashed border-white/15 p-8 text-center">
                        <h2 class="text-lg font-semibold text-white">Aucune question disponible</h2>
                        <p class="mt-2 text-sm text-slate-400">Contactez l’administration pour configurer cet examen.</p>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3 border-t border-white/10 pt-5">
                        <button
                            :disabled="currentQuestionIndex === 0"
                            class="inline-flex h-11 items-center gap-2 border border-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-35"
                            type="button"
                            @click="previousQuestion"
                        >
                            <LearningIcon class="size-4 brightness-0 invert" name="arrow-left"/>
                            Précédent
                        </button>

                        <button
                            v-if="currentQuestionIndex === questions.length - 1"
                            :disabled="isSubmitting || !currentQuestionAnswered"
                            class="inline-flex h-11 items-center gap-2 bg-emerald-500 px-5 text-sm font-semibold text-white transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:bg-slate-700 disabled:text-slate-400 disabled:opacity-80"
                            type="button"
                            @click="requestSubmit"
                        >
                            {{ isSubmitting ? 'Soumission…' : "Soumettre l'examen" }}
                            <LearningIcon class="size-4 brightness-0 invert" name="check"/>
                        </button>
                        <button
                            v-else
                            class="inline-flex h-11 items-center gap-2 bg-[#a72f5d] px-5 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
                            type="button"
                            @click="nextQuestion"
                        >
                            Suivant
                            <LearningIcon class="size-4 brightness-0 invert" name="arrow-right"/>
                        </button>
                    </div>
                </div>
            </main>

            <aside class="sticky top-0 hidden h-full w-80 shrink-0 self-start overflow-y-auto border-l border-white/10 bg-[#081524] md:block lg:w-96">
                <div class="sticky top-0 z-10 border-b border-white/10 bg-[#081524] p-4">
                    <h3 class="font-semibold text-white">Navigation de l’examen</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ answeredCount }} / {{ questions.length }} réponse(s)</p>
                </div>
                <div class="grid gap-5 p-4">
                    <div v-if="remaining !== null" class="border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Temps restant</p>
                        <p class="mt-2 flex items-center gap-2 text-2xl font-semibold text-white">
                            <LearningIcon class="size-5 brightness-0 invert" name="clock"/>
                            <span class="font-mono">{{ formatTime(remaining) }}</span>
                        </p>
                    </div>

                    <div class="border border-white/10 p-4">
                        <div class="mb-3 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Progression</span>
                            <span class="font-semibold text-[#ff79a5]">{{ Math.round(progress) }}%</span>
                        </div>
                        <div class="h-1.5 bg-white/10">
                            <div class="h-full bg-[#df3e75]" :style="{width: `${progress}%`}"/>
                        </div>
                    </div>

                    <div class="border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Question active</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ currentQuestionIndex + 1 }} / {{ questions.length }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            {{ questionInstruction }}
                        </p>
                    </div>
                </div>
            </aside>
        </div>

        <Teleport to="body">
            <div v-if="showSubmitConfirm" class="fixed inset-0 z-[90] grid place-items-center bg-black/70 px-4">
                <section class="w-full max-w-md border border-white/10 bg-[#101d2d] p-6 shadow-2xl shadow-black/40">
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#ff79a5]">Confirmation</p>
                    <h2 class="mt-2 text-xl font-semibold text-white">Soumettre l’examen ?</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Vos réponses seront verrouillées et vous ne pourrez plus les modifier après la soumission.
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            class="h-10 border border-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/5"
                            type="button"
                            @click="showSubmitConfirm = false"
                        >
                            Annuler
                        </button>
                        <button
                            class="h-10 bg-emerald-500 px-4 text-sm font-semibold text-white transition hover:bg-emerald-400"
                            type="button"
                            @click="submitExam()"
                        >
                            Confirmer
                        </button>
                    </div>
                </section>
            </div>
        </Teleport>
    </div>
</template>
