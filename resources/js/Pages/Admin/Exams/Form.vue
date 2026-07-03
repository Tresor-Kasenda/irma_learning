<script lang="ts" setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {ArrowLeft, Copy, GripVertical, Plus, Save, Trash2, X} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Option {
    option_text: string;
    is_correct: boolean;
    order_position: number;
}

interface QuestionForm {
    question_text: string;
    question_type: string;
    points: number;
    is_required: boolean;
    explanation: string;
    options: Option[];
}

interface ParentOption {
    value: string;
    label: string;
    group: string;
}

const props = defineProps<{
    exam: {
        id?: number;
        title: string;
        description: string | null;
        instructions: string | null;
        duration_minutes: number;
        passing_score: number;
        max_attempts: number;
        randomize_questions: boolean;
        show_results_immediately: boolean;
        is_active: boolean;
        available_from: string | null;
        available_until: string | null;
        examable_type: string;
        examable_id: number;
        questions?: QuestionForm[];
    } | null;
    parentOptions: ParentOption[];
}>();

const isEditing = !!props.exam?.id;

const typeLabels: Record<string, string> = {
    single_choice: 'Choix unique',
    multiple_choice: 'Choix multiple',
    true_false: 'Vrai/Faux',
};

const examableTypeOptions = [
    {value: 'App\\Models\\Section', label: 'Section'},
    {value: 'App\\Models\\Formation', label: 'Formation'},
];

function parseParentValue(value: string): { type: string; id: number } {
    const [type, idStr] = value.split(':');
    return {type, id: parseInt(idStr, 10)};
}

const form = useForm({
    title: props.exam?.title ?? '',
    description: props.exam?.description ?? '',
    instructions: props.exam?.instructions ?? '',
    duration_minutes: props.exam?.duration_minutes ?? 60,
    passing_score: props.exam?.passing_score ?? 70,
    max_attempts: props.exam?.max_attempts ?? 3,
    randomize_questions: props.exam?.randomize_questions ?? false,
    show_results_immediately: props.exam?.show_results_immediately ?? true,
    is_active: props.exam?.is_active ?? true,
    available_from: props.exam?.available_from ?? '',
    available_until: props.exam?.available_until ?? '',
    examable_type: props.exam?.examable_type ?? '',
    examable_id: props.exam?.examable_id ?? 0 as number | '',
    questions: props.exam?.questions ?? [] as QuestionForm[],
});

const groupedParents = props.parentOptions.reduce<Record<string, ParentOption[]>>((acc, opt) => {
    if (!acc[opt.group]) acc[opt.group] = [];
    acc[opt.group].push(opt);
    return acc;
}, {});

function handleExamableTypeChange(value: string): void {
    form.examable_type = value;
    form.examable_id = '';
}

function getFilteredParents(): ParentOption[] {
    if (!form.examable_type) return [];
    const prefix = form.examable_type + ':';
    return props.parentOptions.filter(o => o.value.startsWith(prefix));
}

function initialParentValue(): string {
    if (props.exam?.examable_type && props.exam?.examable_id) {
        return `${props.exam.examable_type}:${props.exam.examable_id}`;
    }
    return '';
}

function addQuestion(): void {
    form.questions.push({
        question_text: '',
        question_type: 'single_choice',
        points: 1,
        is_required: true,
        explanation: '',
        options: [
            {option_text: '', is_correct: false, order_position: 1},
            {option_text: '', is_correct: false, order_position: 2},
            {option_text: '', is_correct: false, order_position: 3},
            {option_text: '', is_correct: false, order_position: 4},
        ],
    });
}

function removeQuestion(index: number): void {
    form.questions.splice(index, 1);
}

function duplicateQuestion(index: number): void {
    const q = form.questions[index];
    const clone: QuestionForm = {
        question_text: q.question_text + ' (Copie)',
        question_type: q.question_type,
        points: q.points,
        is_required: q.is_required,
        explanation: q.explanation,
        options: q.options.map(opt => ({...opt})),
    };
    form.questions.splice(index + 1, 0, clone);
}

function moveQuestion(from: number, to: number): void {
    const q = form.questions[from];
    form.questions.splice(from, 1);
    form.questions.splice(to, 0, q);
}

function addOption(questionIndex: number): void {
    const opts = form.questions[questionIndex].options;
    if (opts.length >= 5) return;
    opts.push({option_text: '', is_correct: false, order_position: opts.length + 1});
}

function removeOption(questionIndex: number, optionIndex: number): void {
    const opts = form.questions[questionIndex].options;
    if (opts.length <= 4) return;
    opts.splice(optionIndex, 1);
}

function cloneOption(questionIndex: number, optionIndex: number): void {
    const opt = form.questions[questionIndex].options[optionIndex];
    const clone = {...opt, option_text: opt.option_text + ' (Copie)'};
    form.questions[questionIndex].options.splice(optionIndex + 1, 0, clone);
}

function moveOption(questionIndex: number, from: number, to: number): void {
    const opts = form.questions[questionIndex].options;
    const opt = opts[from];
    opts.splice(from, 1);
    opts.splice(to, 0, opt);
}

function toggleCorrect(questionIndex: number, optionIndex: number): void {
    const q = form.questions[questionIndex];
    const opt = q.options[optionIndex];
    if (q.question_type === 'single_choice' || q.question_type === 'true_false') {
        q.options.forEach((o, i) => {
            o.is_correct = i === optionIndex;
        });
    } else {
        opt.is_correct = !opt.is_correct;
    }
}

function submit(): void {
    if (isEditing && props.exam?.id) {
        form.post(safeRoute('admin.exams.update', props.exam.id), {
            preserveScroll: true,
        });
    } else {
        form.post(safeRoute('admin.exams.store'), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Modifier l\'examen' : 'Nouvel examen'"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.exams.index')" class="admin-muted transition hover:text-[#a23362]">
                Examens
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text font-medium">{{ isEditing ? 'Modifier' : 'Nouvel examen' }}</span>
        </template>

        <div class="mx-auto max-w-4xl">
            <div class="mb-7 flex items-center gap-4">
                <Link
                    :href="safeRoute('admin.exams.index')"
                    aria-label="Retour"
                    class="admin-divider admin-muted admin-hover grid size-10 shrink-0 place-items-center border transition"
                >
                    <ArrowLeft class="size-5" :stroke-width="1.7"/>
                </Link>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Évaluations</p>
                    <h1 class="admin-heading mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">
                        {{ isEditing ? 'Modifier l\'examen' : 'Nouvel examen' }}
                    </h1>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6">
                <!-- Association -->
                <section class="admin-panel border">
                    <div class="admin-divider border-b px-5 py-4 sm:px-6">
                        <h2 class="admin-heading font-semibold">Association</h2>
                    </div>
                    <div class="grid gap-5 p-5 sm:p-6">
                        <div>
                            <label class="admin-heading mb-2 block text-sm font-medium">Type d'élément</label>
                            <div class="flex gap-2">
                                <button
                                    v-for="opt in examableTypeOptions"
                                    :key="opt.value"
                                    :class="form.examable_type === opt.value
                                        ? 'bg-[#a23362] text-white'
                                        : 'admin-divider admin-text admin-hover border'"
                                    class="h-10 px-5 text-sm font-medium transition"
                                    type="button"
                                    @click="handleExamableTypeChange(opt.value)"
                                >
                                    {{ opt.label }}
                                </button>
                            </div>
                            <p v-if="form.errors.examable_type" class="mt-1 text-xs text-rose-400">{{ form.errors.examable_type }}</p>
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="parent">Élément associé</label>
                            <select
                                id="parent"
                                :value="initialParentValue()"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                @change="form.examable_id = parseInt(($event.target as HTMLSelectElement).value)"
                            >
                                <option value="" disabled>Sélectionner...</option>
                                <optgroup v-for="(opts, group) in groupedParents" :key="group" :label="group">
                                    <option v-for="opt in opts" :key="opt.value" :value="parseParentValue(opt.value).id">
                                        {{ opt.label }}
                                    </option>
                                </optgroup>
                            </select>
                            <p v-if="form.errors.examable_id" class="mt-1 text-xs text-rose-400">L'entité associée est obligatoire.</p>
                            <p v-if="form.examable_type === 'App\\Models\\Section'" class="admin-faint mt-1 text-[10px]">
                                Pour une section, l'étudiant doit terminer tous ses chapitres avant de pouvoir passer l'examen.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Informations générales -->
                <section class="admin-panel border">
                    <div class="admin-divider border-b px-5 py-4 sm:px-6">
                        <h2 class="admin-heading font-semibold">Informations générales</h2>
                    </div>
                    <div class="grid gap-5 p-5 sm:p-6">
                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="title">Titre</label>
                            <input
                                id="title"
                                v-model="form.title"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                placeholder="Titre de l'examen"
                                required
                            />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-rose-400">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="description">Description</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                placeholder="Description optionnelle"
                                rows="3"
                            />
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="instructions">Instructions</label>
                            <textarea
                                id="instructions"
                                v-model="form.instructions"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                placeholder="Instructions pour les étudiants (optionnel)"
                                rows="3"
                            />
                        </div>
                    </div>
                </section>

                <!-- Configuration -->
                <section class="admin-panel border">
                    <div class="admin-divider border-b px-5 py-4 sm:px-6">
                        <h2 class="admin-heading font-semibold">Configuration de l'examen</h2>
                    </div>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="duration">Durée (minutes)</label>
                            <input
                                id="duration"
                                v-model="form.duration_minutes"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                min="1"
                                max="600"
                                required
                                type="number"
                            />
                            <p v-if="form.errors.duration_minutes" class="mt-1 text-xs text-rose-400">{{ form.errors.duration_minutes }}</p>
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="passing_score">Score minimum pour réussir (%)</label>
                            <input
                                id="passing_score"
                                v-model="form.passing_score"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                min="0"
                                max="100"
                                required
                                type="number"
                            />
                            <p v-if="form.errors.passing_score" class="mt-1 text-xs text-rose-400">{{ form.errors.passing_score }}</p>
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="max_attempts">Tentatives max (0 = illimité)</label>
                            <input
                                id="max_attempts"
                                v-model="form.max_attempts"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                min="0"
                                max="100"
                                required
                                type="number"
                            />
                            <p v-if="form.errors.max_attempts" class="mt-1 text-xs text-rose-400">{{ form.errors.max_attempts }}</p>
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="available_from">Disponible à partir du</label>
                            <input
                                id="available_from"
                                v-model="form.available_from"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                type="datetime-local"
                            />
                        </div>

                        <div>
                            <label class="admin-heading mb-1 block text-sm font-medium" for="available_until">Jusqu'au</label>
                            <input
                                id="available_until"
                                v-model="form.available_until"
                                class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                type="datetime-local"
                            />
                        </div>
                    </div>
                </section>

                <!-- Options avancées -->
                <section class="admin-panel border">
                    <div class="admin-divider border-b px-5 py-4 sm:px-6">
                        <h2 class="admin-heading font-semibold">Options avancées</h2>
                    </div>
                    <div class="grid gap-5 p-5 sm:grid-cols-3 sm:p-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="form.randomize_questions" type="checkbox" class="size-4 accent-[#a23362]" />
                            <div>
                                <span class="admin-heading block text-sm font-medium">Mélanger les questions</span>
                                <span class="admin-faint text-[10px]">Les questions seront présentées dans un ordre aléatoire</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="form.show_results_immediately" type="checkbox" class="size-4 accent-[#a23362]" />
                            <div>
                                <span class="admin-heading block text-sm font-medium">Résultats immédiats</span>
                                <span class="admin-faint text-[10px]">Afficher le score et les corrections dès la fin</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="size-4 accent-[#a23362]" />
                            <div>
                                <span class="admin-heading block text-sm font-medium">Actif</span>
                                <span class="admin-faint text-[10px]">L'examen est accessible aux étudiants</span>
                            </div>
                        </label>
                    </div>
                </section>

                <!-- Questions -->
                <section class="admin-panel border">
                    <div class="admin-divider flex items-center justify-between border-b px-5 py-4 sm:px-6">
                        <h2 class="admin-heading font-semibold">Questions</h2>
                        <button
                            class="inline-flex items-center gap-1 text-sm font-semibold text-[#ef477d] hover:text-rose-300 transition"
                            type="button"
                            @click="addQuestion"
                        >
                            <Plus class="size-4" :stroke-width="1.8"/> Ajouter
                        </button>
                    </div>

                    <div v-if="form.questions.length > 0" class="divide-y divide-[color:var(--admin-border)]">
                        <div v-for="(q, qi) in form.questions" :key="qi" class="p-5 sm:px-6">
                            <div class="mb-3 flex items-center gap-2">
                                <button
                                    v-if="qi > 0"
                                    class="admin-muted admin-hover p-1 transition"
                                    type="button"
                                    @click="moveQuestion(qi, qi - 1)"
                                >
                                    <GripVertical class="size-4" :stroke-width="1.7"/>
                                </button>
                                <span class="admin-heading text-sm font-semibold">Question {{ qi + 1 }}</span>
                                <div class="ml-auto flex items-center gap-1">
                                    <button
                                        class="text-sky-500 hover:text-sky-300 transition p-1"
                                        type="button"
                                        title="Dupliquer la question"
                                        @click="duplicateQuestion(qi)"
                                    >
                                        <Copy class="size-4" :stroke-width="1.7"/>
                                    </button>
                                    <button class="text-rose-500 hover:text-rose-300 transition p-1" type="button" @click="removeQuestion(qi)">
                                        <Trash2 class="size-4" :stroke-width="1.7"/>
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-4">
                                <div>
                                    <textarea
                                        v-model="q.question_text"
                                        class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                        placeholder="Texte de la question"
                                        rows="2"
                                        required
                                    />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="admin-muted mb-1 block text-xs font-medium">Type</label>
                                        <select v-model="q.question_type" class="admin-divider admin-text w-full border px-3 py-2 text-sm">
                                            <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="admin-muted mb-1 block text-xs font-medium">Points</label>
                                        <input v-model="q.points" class="admin-divider admin-text w-full border px-3 py-2 text-sm" min="1" max="100" type="number" />
                                    </div>
                                    <div class="flex items-end">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input v-model="q.is_required" type="checkbox" class="size-4 accent-[#a23362]" />
                                            <span class="admin-text text-xs font-medium">Obligatoire</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="admin-muted mb-1 block text-xs font-medium">Explication (affichée après réponse)</label>
                                    <textarea
                                        v-model="q.explanation"
                                        class="admin-divider admin-text w-full border px-3 py-2 text-sm"
                                        placeholder="Explication optionnelle"
                                        rows="2"
                                    />
                                </div>

                                <!-- Options -->
                                <div class="admin-panel-muted border p-4">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="admin-text text-xs font-semibold">Options ({{ q.options.length }}/5)</span>
                                        <button
                                            v-if="q.options.length < 5"
                                            class="text-xs font-medium text-[#ef477d] hover:text-rose-300 transition"
                                            type="button"
                                            @click="addOption(qi)"
                                        >
                                            + Ajouter une option
                                        </button>
                                    </div>
                                    <div class="grid gap-2">
                                        <div v-for="(opt, oi) in q.options" :key="oi" class="flex items-center gap-2">
                                            <button
                                                v-if="oi > 0"
                                                class="text-slate-500 hover:text-white/60 transition shrink-0"
                                                type="button"
                                                @click="moveOption(qi, oi, oi - 1)"
                                            >
                                                <GripVertical class="size-3.5" :stroke-width="1.7"/>
                                            </button>
                                            <button
                                                :class="opt.is_correct
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'admin-divider admin-text border'"
                                                class="size-6 shrink-0 text-[10px] font-bold transition"
                                                type="button"
                                                @click="toggleCorrect(qi, oi)"
                                                :title="q.question_type === 'single_choice' || q.question_type === 'true_false' ? 'Bonne réponse (unique)' : 'Bonne réponse'"
                                            >
                                                {{ opt.is_correct ? '✓' : '' }}
                                            </button>
                                            <input
                                                v-model="opt.option_text"
                                                class="admin-divider admin-text flex-1 border px-2 py-1 text-sm"
                                                placeholder="Texte de l'option"
                                            />
                                            <button
                                                class="text-sky-500 hover:text-sky-300 transition shrink-0"
                                                type="button"
                                                title="Dupliquer l'option"
                                                @click="cloneOption(qi, oi)"
                                            >
                                                <Copy class="size-3.5" :stroke-width="1.7"/>
                                            </button>
                                            <button
                                                v-if="q.options.length > 4"
                                                class="text-slate-500 hover:text-rose-400 transition shrink-0"
                                                type="button"
                                                @click="removeOption(qi, oi)"
                                            >
                                                <X class="size-4" :stroke-width="1.7"/>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="q.options.length < 4" class="admin-faint mt-1 text-[10px]">Ajoutez au moins {{ 4 - q.options.length }} option(s) supplémentaire(s).</p>
                                    <p v-if="q.question_type === 'single_choice' || q.question_type === 'true_false'" class="admin-faint mt-1 text-[10px]">
                                        Une seule bonne réponse autorisée (clic pour sélectionner)
                                    </p>
                                    <p v-else class="admin-faint mt-1 text-[10px]">
                                        Plusieurs bonnes réponses possibles (clic pour basculer)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-5 py-8 text-center sm:px-6">
                        <p class="admin-muted text-sm">Aucune question. Cliquez sur « Ajouter » pour créer la première question.</p>
                    </div>
                </section>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3 pb-10">
                    <Link
                        :href="safeRoute('admin.exams.index')"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center border px-5 text-sm font-medium transition"
                    >
                        Annuler
                    </Link>
                    <button
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e] disabled:opacity-50"
                        type="submit"
                    >
                        <Save class="size-4" :stroke-width="1.8"/>
                        {{ form.processing ? 'Enregistrement...' : (isEditing ? 'Enregistrer les modifications' : 'Créer l\'examen') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
