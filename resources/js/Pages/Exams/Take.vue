<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

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
    questions: Question[];
    attempt: { id: number; started_at: string; status: string };
    existingAnswers: Record<number, unknown>;
    timeRemaining: number | null;
}>();

const answers = ref<Record<number, any>>({ ...props.existingAnswers });
const currentQuestionIndex = ref(0);
const remaining = ref(props.timeRemaining);
const isSubmitting = ref(false);

const currentQuestion = computed(() => props.questions[currentQuestionIndex.value] ?? null);

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

let timerInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (remaining.value !== null && remaining.value > 0) {
        timerInterval = setInterval(() => {
            if (remaining.value !== null) {
                remaining.value--;

                if (remaining.value <= 0) {
                    if (timerInterval) clearInterval(timerInterval);
                    submitExam();
                } else if (remaining.value === 300) {
                    alert('Il vous reste 5 minutes !');
                } else if (remaining.value === 60) {
                    alert('Il vous reste 1 minute !');
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

function saveCurrentAnswer() {
    if (!currentQuestion.value) return;
    const answer = answers.value[currentQuestion.value.id] ?? null;

    router.post(route('exam.save-answer', props.exam.id), {
        question_id: currentQuestion.value.id,
        answer,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function nextQuestion() {
    saveCurrentAnswer();
    if (currentQuestionIndex.value < props.questions.length - 1) {
        currentQuestionIndex.value++;
    }
}

function previousQuestion() {
    saveCurrentAnswer();
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
}

function goToQuestion(index: number) {
    saveCurrentAnswer();
    currentQuestionIndex.value = index;
}

function submitExam() {
    if (isSubmitting.value) return;
    if (!confirm('Êtes-vous sûr de vouloir soumettre votre examen ? Vous ne pourrez plus modifier vos réponses.')) return;

    isSubmitting.value = true;
    if (timerInterval) clearInterval(timerInterval);

    saveCurrentAnswer();

    setTimeout(() => {
        router.post(route('exam.submit', props.exam.id));
    }, 300);
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
</script>

<template>
    <Head title="Passer l'examen" />

    <div class="min-h-screen bg-gray-900">
        <header class="bg-gray-950 border-b border-gray-800 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold text-white">{{ exam.title }}</h1>
                        <p v-if="exam.description" class="text-sm text-gray-400">{{ exam.description }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div v-if="remaining !== null"
                             class="flex items-center gap-2 px-4 py-2 bg-gray-800 rounded-lg"
                        >
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-white font-mono">{{ formatTime(remaining) }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-400">Progression:</span>
                            <div class="w-32 bg-gray-800 rounded-full h-2">
                                <div class="bg-primary-600 h-full rounded-full transition-all duration-500"
                                     :style="{ width: progress + '%' }"
                                />
                            </div>
                            <span class="text-sm text-white">{{ Math.round(progress) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-gray-800 rounded-lg p-4 sticky top-24">
                        <h3 class="text-sm font-semibold text-white mb-4">Navigation</h3>
                        <div class="grid grid-cols-5 lg:grid-cols-4 gap-2">
                            <button v-for="(question, index) in questions" :key="question.id"
                                    @click="goToQuestion(index)"
                                    class="w-10 h-10 rounded-lg text-sm font-medium transition-all"
                                    :class="{
                                        'bg-primary-600 text-white': index === currentQuestionIndex,
                                        'bg-green-600 text-white': index !== currentQuestionIndex && isAnswered(question.id),
                                        'bg-gray-700 text-gray-300 hover:bg-gray-600': index !== currentQuestionIndex && !isAnswered(question.id),
                                    }"
                            >
                                {{ index + 1 }}
                            </button>
                        </div>
                        <div class="mt-4 space-y-2 text-xs text-gray-400">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-primary-600 rounded" />
                                <span>Question actuelle</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-600 rounded" />
                                <span>Répondue</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-gray-700 rounded" />
                                <span>Non répondue</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div v-if="currentQuestion" class="bg-gray-800 rounded-lg p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-primary-400">
                                Question {{ currentQuestionIndex + 1 }} sur {{ questions.length }}
                            </span>
                            <span class="px-3 py-1 bg-gray-700 rounded-full text-xs text-gray-300">
                                {{ currentQuestion.points }} {{ currentQuestion.points > 1 ? 'points' : 'point' }}
                            </span>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-white text-lg font-medium">{{ currentQuestion.question_text }}</h3>
                        </div>

                        <div v-if="currentQuestion.image" class="mb-6">
                            <img :src="getMediaUrl(currentQuestion.image)"
                                 alt="Question image"
                                 class="rounded-lg max-w-full h-auto"
                            >
                        </div>

                        <div v-if="exam.instructions" class="mb-6 p-4 bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-300">{{ exam.instructions }}</p>
                        </div>

                        <div class="space-y-3">
                            <template v-if="currentQuestion.question_type === 'single_choice'">
                                <label v-for="option in currentQuestion.options" :key="option.id"
                                       class="flex items-start gap-3 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors"
                                >
                                    <input type="radio"
                                           :name="'q_' + currentQuestion.id"
                                           :value="option.id"
                                           :checked="answers[currentQuestion.id] === option.id"
                                           @change="answers[currentQuestion.id] = option.id"
                                           class="mt-1 w-4 h-4 text-primary-600 bg-gray-900 border-gray-600 focus:ring-primary-500"
                                    >
                                    <div class="flex-1">
                                        <span class="text-white">{{ option.option_text }}</span>
                                        <img v-if="option.image" :src="getMediaUrl(option.image)" alt="Option image" class="mt-2 rounded max-w-sm h-auto">
                                    </div>
                                </label>
                            </template>

                            <template v-else-if="currentQuestion.question_type === 'true_false'">
                                <label v-for="option in currentQuestion.options" :key="option.id"
                                       class="flex items-start gap-3 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors"
                                >
                                    <input type="radio"
                                           :name="'q_' + currentQuestion.id"
                                           :value="option.id"
                                           :checked="answers[currentQuestion.id] === option.id"
                                           @change="answers[currentQuestion.id] = option.id"
                                           class="mt-1 w-4 h-4 text-primary-600 bg-gray-900 border-gray-600 focus:ring-primary-500"
                                    >
                                    <span class="text-white">{{ option.option_text }}</span>
                                </label>
                            </template>

                            <template v-else-if="currentQuestion.question_type === 'multiple_choice'">
                                <p class="text-sm text-gray-400 mb-2">Sélectionnez toutes les réponses correctes</p>
                                <label v-for="option in currentQuestion.options" :key="option.id"
                                       class="flex items-start gap-3 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors"
                                >
                                    <input type="checkbox"
                                           :checked="Array.isArray(answers[currentQuestion.id]) && (answers[currentQuestion.id] as number[]).includes(option.id)"
                                           @change="toggleMultipleChoice(option.id)"
                                           class="mt-1 w-4 h-4 text-primary-600 bg-gray-900 border-gray-600 rounded focus:ring-primary-500"
                                    >
                                    <div class="flex-1">
                                        <span class="text-white">{{ option.option_text }}</span>
                                        <img v-if="option.image" :src="getMediaUrl(option.image)" alt="Option image" class="mt-2 rounded max-w-sm h-auto">
                                    </div>
                                </label>
                            </template>

                            <template v-else-if="currentQuestion.question_type === 'text'">
                                <input type="text"
                                       v-model="answers[currentQuestion.id]"
                                       placeholder="Votre réponse..."
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </template>

                            <template v-else-if="currentQuestion.question_type === 'essay'">
                                <textarea v-model="answers[currentQuestion.id]"
                                          placeholder="Votre réponse détaillée..." rows="8"
                                          class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                />
                            </template>
                        </div>
                    </div>

                    <div v-else class="bg-gray-800 rounded-lg p-6 text-center">
                        <p class="text-gray-400">Aucune question disponible</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <button @click="previousQuestion"
                                :disabled="currentQuestionIndex === 0"
                                class="px-6 py-3 rounded-lg font-medium transition-colors"
                                :class="currentQuestionIndex > 0
                                    ? 'bg-gray-700 text-white hover:bg-gray-600'
                                    : 'bg-gray-800 text-gray-500 cursor-not-allowed'"
                        >
                            ← Précédent
                        </button>

                        <template v-if="currentQuestionIndex === questions.length - 1">
                            <button @click="submitExam"
                                    :disabled="isSubmitting"
                                    class="px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors disabled:opacity-50"
                            >
                                {{ isSubmitting ? 'Soumission...' : "Soumettre l'examen" }}
                            </button>
                        </template>
                        <button v-else @click="nextQuestion"
                                class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
                        >
                            Suivant →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
