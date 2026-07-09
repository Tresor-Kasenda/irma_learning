<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';

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
    userAnswers: UserAnswer[];
    canRetry: boolean;
    courseCompletion: CourseCompletion | null;
    nextStep: NextStep | null;
    certificate: CertificateInfo | null;
}>();

function isOptionSelected(answer: UserAnswer, optionId: number): boolean {
    if (answer.question.question_type === 'multiple_choice') {
        const selected = answer.selected_options;
        return Array.isArray(selected) && selected.includes(optionId);
    }
    return answer.selected_option_id === optionId;
}

function getCorrectCount(): number {
    return props.userAnswers.filter(ua => ua.is_correct === true).length;
}

function getIncorrectCount(): number {
    return props.userAnswers.filter(ua => ua.is_correct === false).length;
}

function formatDate(date: string | null): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Résultats de l'examen"/>

    <div class="min-h-screen bg-gray-900 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div :class="attempt.passed ? 'from-green-600 to-green-700' : 'from-red-600 to-red-700'"
                 class="bg-linear-to-r rounded-lg p-8 text-white mb-8"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">
                            <template v-if="attempt.passed">Félicitations !</template>
                            <template v-else>Résultats de l'examen</template>
                        </h1>
                        <p class="text-xl opacity-90">{{ exam.title }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-5xl font-bold mb-1">{{ Math.round(attempt.percentage) }}%</div>
                        <div class="text-sm opacity-90">{{ attempt.score }} / {{ attempt.max_score }} points</div>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between text-sm opacity-90">
                    <div>Note de passage : {{ exam.passing_score }}%</div>
                    <div v-if="attempt.completed_at">Complété le {{ formatDate(attempt.completed_at) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ getCorrectCount() }}</div>
                            <div class="text-sm text-gray-400">Réponses correctes</div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ getIncorrectCount() }}</div>
                            <div class="text-sm text-gray-400">Réponses incorrectes</div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ attempt.time_taken ?? 0 }}</div>
                            <div class="text-sm text-gray-400">Minutes écoulées</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-white">Détail des réponses</h2>

                <div v-for="(answer, index) in userAnswers" :key="answer.id"
                     :class="answer.is_correct === true ? 'border-green-500' : answer.is_correct === false ? 'border-red-500' : 'border-gray-500'"
                     class="bg-gray-800 rounded-lg p-6 border-l-4"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-3">
                            <div :class="answer.is_correct === true ? 'bg-green-600 text-white' : answer.is_correct === false ? 'bg-red-600 text-white' : 'bg-gray-600 text-white'"
                                 class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                            >
                                <svg v-if="answer.is_correct === true" class="w-5 h-5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"/>
                                </svg>
                                <svg v-else-if="answer.is_correct === false" class="w-5 h-5" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-400 mb-1">Question {{ index + 1 }}</div>
                                <h3 class="text-white font-medium">{{ answer.question.question_text }}</h3>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-400">Points</div>
                            <div class="text-white font-semibold">{{ answer.points_earned }} / {{
                                    answer.question.points
                                }}
                            </div>
                        </div>
                    </div>

                    <template
                        v-if="['single_choice', 'multiple_choice', 'true_false'].includes(answer.question.question_type)">
                        <div class="space-y-2 mb-4">
                            <div v-for="option in answer.question.options" :key="option.id"
                                 :class="{
                                     'bg-green-900/30 border border-green-600': option.is_correct && isOptionSelected(answer, option.id),
                                     'bg-red-900/30 border border-red-600': !option.is_correct && isOptionSelected(answer, option.id),
                                     'bg-green-900/20 border border-green-600/50': option.is_correct && !isOptionSelected(answer, option.id),
                                     'bg-gray-700': !option.is_correct && !isOptionSelected(answer, option.id),
                                 }"
                                 class="p-3 rounded-lg"
                            >
                                <div class="flex items-center gap-2">
                                    <svg v-if="isOptionSelected(answer, option.id) && option.is_correct"
                                         class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"/>
                                    </svg>
                                    <svg v-else-if="isOptionSelected(answer, option.id) && !option.is_correct"
                                         class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"/>
                                    </svg>
                                    <svg v-else-if="option.is_correct"
                                         class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"/>
                                    </svg>
                                    <span :class="{ 'text-gray-300': !isOptionSelected(answer, option.id) && !option.is_correct }"
                                          class="text-white">
                                        {{ option.option_text }}
                                    </span>
                                    <span v-if="isOptionSelected(answer, option.id)"
                                          class="ml-auto text-xs text-gray-400">(Votre réponse)</span>
                                    <span v-else-if="option.is_correct" class="ml-auto text-xs text-green-400">(Réponse correcte)</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div class="mb-4">
                            <div class="text-sm text-gray-400 mb-2">Votre réponse:</div>
                            <div class="p-3 bg-gray-700 rounded-lg text-white">
                                {{ answer.answer_text || 'Aucune réponse fournie' }}
                            </div>
                        </div>
                    </template>

                    <div v-if="answer.question.explanation"
                         class="mt-4 p-4 bg-blue-900/20 border border-blue-600/50 rounded-lg"
                    >
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"/>
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-blue-400 mb-1">Explication</div>
                                <div class="text-sm text-gray-300">{{ answer.question.explanation }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-center gap-4">
                <Link :href="route('dashboard')"
                      class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
                >
                    Retour au tableau de bord
                </Link>
                <Link v-if="attempt.passed && courseCompletion"
                      :href="route('course.chapter.complete', {
                          formation: courseCompletion.formation_id,
                          chapter: courseCompletion.chapter_id,
                      })"
                      as="button"
                      class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                      method="post"
                >
                    Valider le chapitre et continuer
                </Link>
                <Link v-if="attempt.passed && nextStep?.type === 'next_section' && nextStep.chapter_id"
                      :href="route('course.player', {
                          formation: nextStep.formation_id,
                          chapterId: nextStep.chapter_id,
                      })"
                      class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Continuer vers la section suivante
                </Link>
                <Link v-else-if="attempt.passed && nextStep?.type === 'final_exam' && nextStep.exam_id"
                      :href="route('exam.take', nextStep.exam_id)"
                      class="px-6 py-3 bg-amber-500 text-slate-950 rounded-lg hover:bg-amber-400 transition-colors font-semibold"
                >
                    Passer l’examen final
                </Link>
                <p v-else-if="attempt.passed && nextStep?.type === 'final_exam_missing'"
                   class="rounded-lg border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200"
                >
                    L’examen final n’est pas encore configuré. Contactez l’administration.
                </p>
                <Link v-else-if="attempt.passed && nextStep?.type === 'completed' && certificate"
                      :href="route('certificats.show', certificate.id)"
                      class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-500 transition-colors"
                >
                    Voir mon certificat
                </Link>
                <Link v-if="canRetry"
                      :href="route('exam.take', exam.id)"
                      class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Réessayer l'examen
                </Link>
            </div>

            <div
                v-if="attempt.passed && nextStep?.type === 'completed' && certificate"
                class="mt-6 flex items-center justify-center"
            >
                <p class="rounded-lg bg-emerald-500/10 px-4 py-3 text-center text-sm text-emerald-300">
                    🎓 Félicitations ! Vous avez terminé la formation. Certificat
                    <span class="font-semibold">{{ certificate.certificate_number }}</span> délivré.
                </p>
            </div>
        </div>
    </div>
</template>
