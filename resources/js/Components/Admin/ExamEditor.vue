<script lang="ts" setup>
import {ArrowDown, ArrowUp, Check, Circle, Copy, HelpCircle, Plus, Trash2, X,} from '@lucide/vue';
import NumberField from '@/Components/Admin/Fields/NumberField.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import TextField from '@/Components/Admin/Fields/TextField.vue';
import TextareaField from '@/Components/Admin/Fields/TextareaField.vue';
import ToggleField from '@/Components/Admin/Fields/ToggleField.vue';
import type {ExamEditorData, ExamQuestionForm} from '@/types/admin-exam';

const props = withDefaults(defineProps<{
    errors?: Record<string, string>;
    errorPrefix?: string;
    showAvailability?: boolean;
}>(), {
    errors: () => ({}),
    errorPrefix: '',
    showAvailability: false,
});

const exam = defineModel<ExamEditorData>({required: true});

const questionTypeOptions = [
    {value: 'single_choice', label: 'Choix unique'},
    {value: 'multiple_choice', label: 'Choix multiple'},
    {value: 'true_false', label: 'Vrai / Faux'},
];

const typeLabels: Record<string, string> = Object.fromEntries(
    questionTypeOptions.map((option) => [option.value, option.label]),
);

function fieldError(path: string): string | undefined {
    return props.errors[`${props.errorPrefix}${path}`];
}

function emptyQuestion(): ExamQuestionForm {
    return {
        question_text: '',
        question_type: 'single_choice',
        points: 1,
        is_required: true,
        explanation: '',
        options: Array.from({length: 4}, (_, index) => ({
            option_text: '',
            is_correct: index === 0,
            order_position: index + 1,
        })),
    };
}

function addQuestion(): void {
    exam.value.questions.push(emptyQuestion());
}

function removeQuestion(index: number): void {
    exam.value.questions.splice(index, 1);
}

function duplicateQuestion(index: number): void {
    const question = exam.value.questions[index];
    const duplicate: ExamQuestionForm = {
        ...question,
        id: null,
        question_text: `${question.question_text || `Question ${index + 1}`} (copie)`,
        options: question.options.map((option) => ({...option, id: null})),
    };
    exam.value.questions.splice(index + 1, 0, duplicate);
}

function moveQuestion(index: number, direction: -1 | 1): void {
    const target = index + direction;
    if (target < 0 || target >= exam.value.questions.length) {
        return;
    }

    const [question] = exam.value.questions.splice(index, 1);
    exam.value.questions.splice(target, 0, question);
}

function addOption(questionIndex: number): void {
    const options = exam.value.questions[questionIndex].options;
    if (options.length >= 5) {
        return;
    }

    options.push({
        option_text: '',
        is_correct: false,
        order_position: options.length + 1,
    });
}

function removeOption(questionIndex: number, optionIndex: number): void {
    const options = exam.value.questions[questionIndex].options;
    if (options.length <= 4) {
        return;
    }

    options.splice(optionIndex, 1);
}

function duplicateOption(questionIndex: number, optionIndex: number): void {
    const options = exam.value.questions[questionIndex].options;
    if (options.length >= 5) {
        return;
    }

    const option = options[optionIndex];
    options.splice(optionIndex + 1, 0, {
        ...option,
        id: null,
        option_text: `${option.option_text || `Option ${optionIndex + 1}`} (copie)`,
        is_correct: false,
    });
}

function toggleCorrect(questionIndex: number, optionIndex: number): void {
    const question = exam.value.questions[questionIndex];

    if (question.question_type === 'multiple_choice') {
        question.options[optionIndex].is_correct = !question.options[optionIndex].is_correct;
        return;
    }

    question.options.forEach((option, index) => {
        option.is_correct = index === optionIndex;
    });
}
</script>

<template>
    <div class="grid min-w-0 gap-6">
        <section class="admin-panel-muted min-w-0 border p-4 sm:p-5">
            <div class="mb-5 flex min-w-0 items-start gap-3">
                <span
                    class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/15 text-[#ef477d] dark:bg-[#7d254a]/40">
                    <HelpCircle :stroke-width="1.8" class="size-5"/>
                </span>
                <div class="min-w-0">
                    <h3 class="admin-heading font-semibold">Informations de l’examen</h3>
                    <p class="admin-muted mt-1 text-xs leading-5">Définissez les règles puis construisez les questions
                        dans un espace lisible.</p>
                </div>
            </div>

            <div class="grid min-w-0 gap-5">
                <TextField
                    v-model="exam.title"
                    :error="fieldError('title')"
                    label="Titre de l’examen"
                    placeholder="Ex. Quiz de validation de la section"
                    required
                />
                <div class="grid min-w-0 gap-5 lg:grid-cols-2">
                    <TextareaField
                        v-model="exam.description"
                        :error="fieldError('description')"
                        :rows="3"
                        label="Description"
                        placeholder="Présentez brièvement l’objectif de l’évaluation."
                    />
                    <TextareaField
                        v-model="exam.instructions"
                        :error="fieldError('instructions')"
                        :rows="3"
                        label="Instructions"
                        placeholder="Consignes affichées avant le démarrage."
                    />
                </div>

                <div class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <NumberField
                        id="exam-editor-duration"
                        v-model="exam.duration_minutes"
                        :error="fieldError('duration_minutes')"
                        :max="600"
                        :min="1"
                        label="Durée"
                        suffix="min"
                    />
                    <NumberField
                        id="exam-editor-score"
                        v-model="exam.passing_score"
                        :error="fieldError('passing_score')"
                        :max="100"
                        :min="0"
                        label="Seuil de réussite"
                        suffix="%"
                    />
                    <NumberField
                        id="exam-editor-attempts"
                        v-model="exam.max_attempts"
                        :error="fieldError('max_attempts')"
                        :max="100"
                        :min="0"
                        hint="0 signifie illimité."
                        label="Tentatives"
                    />
                </div>

                <div class="grid min-w-0 gap-3 md:grid-cols-3">
                    <ToggleField v-model="exam.randomize_questions" hint="Ordre différent pour chaque tentative."
                                 label="Mélanger les questions"/>
                    <ToggleField v-model="exam.show_results_immediately" hint="Afficher le résultat après l’envoi."
                                 label="Résultats immédiats"/>
                    <ToggleField v-model="exam.is_active" hint="Accessible aux apprenants autorisés."
                                 label="Examen actif"/>
                </div>
            </div>
        </section>

        <section class="admin-panel min-w-0 overflow-hidden border">
            <div
                class="admin-divider flex flex-col gap-3 border-b px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="min-w-0">
                    <h3 class="admin-heading font-semibold">Questions</h3>
                    <p class="admin-muted mt-1 text-xs">{{ exam.questions.length }} question(s) · 4 à 5 options par
                        question</p>
                </div>
                <button
                    class="inline-flex h-10 shrink-0 items-center justify-center gap-2 bg-[#a23362] px-4 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                    type="button" @click="addQuestion">
                    <Plus :stroke-width="2" class="size-4"/>
                    Ajouter une question
                </button>
            </div>

            <div v-if="exam.questions.length" class="grid min-w-0 gap-4 p-4 sm:p-5">
                <article v-for="(question, questionIndex) in exam.questions"
                         :key="question.id ?? `question-${questionIndex}`"
                         class="admin-panel-muted min-w-0 overflow-hidden border">
                    <div class="admin-divider flex min-w-0 flex-wrap items-center gap-2 border-b px-3 py-3 sm:px-4">
                        <span
                            class="grid size-8 shrink-0 place-items-center bg-[#7d254a]/15 text-xs font-bold text-[#a23362] dark:bg-[#7d254a]/45 dark:text-rose-200">Q{{
                                questionIndex + 1
                            }}</span>
                        <span class="admin-heading min-w-0 flex-1 truncate text-sm font-semibold">{{
                                question.question_text || `Nouvelle question ${questionIndex + 1}`
                            }}</span>
                        <span class="admin-panel px-2 py-1 text-[10px] font-semibold text-slate-500">{{
                                typeLabels[question.question_type]
                            }}</span>
                        <div class="ml-auto flex shrink-0 items-center gap-1">
                            <button :disabled="questionIndex === 0" aria-label="Monter la question"
                                    class="admin-muted admin-hover grid size-8 place-items-center transition disabled:opacity-30"
                                    type="button" @click="moveQuestion(questionIndex, -1)">
                                <ArrowUp class="size-4"/>
                            </button>
                            <button :disabled="questionIndex === exam.questions.length - 1"
                                    aria-label="Descendre la question"
                                    class="admin-muted admin-hover grid size-8 place-items-center transition disabled:opacity-30"
                                    type="button" @click="moveQuestion(questionIndex, 1)">
                                <ArrowDown class="size-4"/>
                            </button>
                            <button aria-label="Dupliquer la question"
                                    class="grid size-8 place-items-center text-sky-500 transition hover:bg-sky-400/10"
                                    type="button" @click="duplicateQuestion(questionIndex)">
                                <Copy class="size-4"/>
                            </button>
                            <button aria-label="Supprimer la question"
                                    class="grid size-8 place-items-center text-rose-500 transition hover:bg-rose-400/10"
                                    type="button" @click="removeQuestion(questionIndex)">
                                <Trash2 class="size-4"/>
                            </button>
                        </div>
                    </div>

                    <div class="grid min-w-0 gap-5 p-4 sm:p-5">
                        <TextareaField
                            v-model="question.question_text"
                            :error="fieldError(`questions.${questionIndex}.question_text`)"
                            :rows="2"
                            label="Question"
                            placeholder="Rédigez une question claire et sans ambiguïté."
                            required
                        />

                        <div class="grid min-w-0 gap-4 sm:grid-cols-[minmax(0,1fr)_140px]">
                            <SearchableSelect
                                v-model="question.question_type"
                                :clearable="false"
                                :error="fieldError(`questions.${questionIndex}.question_type`)"
                                :options="questionTypeOptions"
                                :searchable="false"
                                label="Type de réponse"
                            />
                            <NumberField
                                :id="`exam-question-${questionIndex}-points`"
                                v-model="question.points"
                                :error="fieldError(`questions.${questionIndex}.points`)"
                                :max="100"
                                :min="1"
                                label="Points"
                            />
                        </div>

                        <TextareaField v-model="question.explanation" :rows="2" label="Explication après réponse"
                                       placeholder="Expliquez pourquoi la réponse est correcte (optionnel)."/>

                        <div class="admin-panel min-w-0 border">
                            <div
                                class="admin-divider flex flex-wrap items-center justify-between gap-2 border-b px-3 py-3 sm:px-4">
                                <div>
                                    <p class="admin-heading text-xs font-semibold">Options de réponse</p>
                                    <p class="admin-muted mt-0.5 text-[10px]">Cliquez sur le cercle pour désigner la ou
                                        les bonnes réponses.</p>
                                </div>
                                <button v-if="question.options.length < 5"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-[#ef477d]"
                                        type="button" @click="addOption(questionIndex)">
                                    <Plus class="size-3.5"/>
                                    Ajouter une option
                                </button>
                            </div>

                            <div class="grid min-w-0 gap-2 p-3 sm:p-4">
                                <div v-for="(option, optionIndex) in question.options"
                                     :key="option.id ?? `option-${optionIndex}`"
                                     class="grid min-w-0 grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2">
                                    <button
                                        :aria-label="option.is_correct ? 'Bonne réponse' : 'Définir comme bonne réponse'"
                                        :class="option.is_correct ? 'border-emerald-500 bg-emerald-500 text-white' : 'admin-divider admin-text border'"
                                        class="grid size-8 shrink-0 place-items-center transition" type="button"
                                        @click="toggleCorrect(questionIndex, optionIndex)">
                                        <Check v-if="option.is_correct" :stroke-width="2.5" class="size-4"/>
                                        <Circle v-else :stroke-width="1.7" class="size-3.5"/>
                                    </button>
                                    <input v-model="option.option_text" :aria-label="`Option ${optionIndex + 1}`"
                                           :placeholder="`Option ${optionIndex + 1}`"
                                           class="admin-field h-10 min-w-0 w-full border px-3 text-sm outline-none"/>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <button v-if="question.options.length < 5"
                                                :aria-label="`Dupliquer l’option ${optionIndex + 1}`"
                                                class="grid size-8 place-items-center text-sky-500 transition hover:bg-sky-400/10"
                                                type="button" @click="duplicateOption(questionIndex, optionIndex)">
                                            <Copy class="size-3.5"/>
                                        </button>
                                        <button v-if="question.options.length > 4"
                                                :aria-label="`Supprimer l’option ${optionIndex + 1}`"
                                                class="grid size-8 place-items-center text-rose-500 transition hover:bg-rose-400/10"
                                                type="button" @click="removeOption(questionIndex, optionIndex)">
                                            <X class="size-4"/>
                                        </button>
                                    </div>
                                    <p v-if="fieldError(`questions.${questionIndex}.options.${optionIndex}.option_text`)"
                                       class="col-start-2 text-xs text-rose-400">{{
                                            fieldError(`questions.${questionIndex}.options.${optionIndex}.option_text`)
                                        }}</p>
                                </div>
                            </div>
                        </div>

                        <ToggleField v-model="question.is_required" compact label="Réponse obligatoire"/>
                    </div>
                </article>
            </div>

            <div v-else class="px-5 py-12 text-center">
                <HelpCircle :stroke-width="1.4" class="mx-auto size-9 text-slate-500"/>
                <h4 class="admin-heading mt-3 text-sm font-semibold">Aucune question</h4>
                <p class="admin-muted mx-auto mt-1 max-w-sm text-xs leading-5">Ajoutez la première question pour
                    construire l’évaluation de cette section.</p>
                <button
                    class="mt-4 inline-flex h-10 items-center gap-2 border border-[#a23362] px-4 text-sm font-semibold text-[#a23362]"
                    type="button" @click="addQuestion">
                    <Plus class="size-4"/>
                    Ajouter une question
                </button>
            </div>
        </section>
    </div>
</template>
