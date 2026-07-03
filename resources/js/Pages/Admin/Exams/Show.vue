<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock3,
    Copy,
    FileQuestion,
    GraduationCap,
    Pencil,
    Power,
    Sparkles,
    Timer,
    Users,
    XCircle,
} from '@lucide/vue';
import {computed, ref} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Option {
    id?: number;
    option_text: string;
    is_correct: boolean;
    order_position: number;
}

interface Question {
    id: number;
    question_text: string;
    question_type: string;
    points: number;
    is_required: boolean;
    order_position: number;
    options_count: number;
    explanation: string | null;
    options?: Option[];
}

interface AttemptRow {
    id: number;
    user: { id: number; name: string; email: string } | null;
    attempt_number: number;
    status: string;
    score: number;
    max_score: number;
    percentage: number | null;
    time_taken: number;
    started_at: string | null;
    completed_at: string | null;
}

interface ExamDetail {
    id: number;
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
    created_at: string;
    questions_count: number;
    questions: Question[];
    attempts: AttemptRow[];
    examable_label: string;
    total_points: number;
}

const props = defineProps<{
    exam: ExamDetail;
}>();

const questionFormOpen = ref(false);
const editingQuestion = ref<Question | null>(null);
const newQuestion = ref({
    question_text: '',
    question_type: 'single_choice',
    points: 1,
    is_required: true,
    explanation: '',
    options: [
        {option_text: '', is_correct: false, order_position: 1},
        {option_text: '', is_correct: false, order_position: 2},
    ] as Option[],
});

const typeLabels: Record<string, string> = {
    single_choice: 'Choix unique',
    multiple_choice: 'Choix multiple',
    true_false: 'Vrai/Faux',
};

const statusMeta: Record<string, { label: string; class: string }> = {
    in_progress: {label: 'En cours', class: 'bg-amber-400/10 text-amber-300'},
    completed: {label: 'Réussi', class: 'bg-emerald-400/10 text-emerald-300'},
    failed: {label: 'Échoué', class: 'bg-rose-400/10 text-rose-300'},
    cancelled: {label: 'Annulé', class: 'bg-slate-500/10 text-slate-400'},
};

const stats = computed(() => [
    {label: 'Questions', value: props.exam.questions_count, icon: FileQuestion, tint: 'text-sky-300 bg-sky-400/10'},
    {label: 'Points totaux', value: props.exam.total_points, icon: Sparkles, tint: 'text-amber-300 bg-amber-400/10'},
    {label: 'Durée', value: `${props.exam.duration_minutes} min`, icon: Timer, tint: 'text-[#ef477d] bg-[#7d254a]/35'},
    {label: 'Seuil', value: `${props.exam.passing_score}%`, icon: CheckCircle2, tint: 'text-emerald-300 bg-emerald-400/10'},
]);

function openQuestionForm(question?: Question): void {
    if (question) {
        editingQuestion.value = question;
        router.get(safeRoute('admin.exams.edit', props.exam.id), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                questionFormOpen.value = true;
            },
        });
    } else {
        editingQuestion.value = null;
        newQuestion.value = {
            question_text: '',
            question_type: 'single_choice',
            points: 1,
            is_required: true,
            explanation: '',
            options: [
                {option_text: '', is_correct: false, order_position: 1},
                {option_text: '', is_correct: false, order_position: 2},
            ],
        };
        questionFormOpen.value = true;
    }
}

function addOption(): void {
    newQuestion.value.options.push({
        option_text: '',
        is_correct: false,
        order_position: newQuestion.value.options.length + 1,
    });
}

function removeOption(index: number): void {
    if (newQuestion.value.options.length <= 2) return;
    newQuestion.value.options.splice(index, 1);
}

function submitQuestion(): void {
    const q = newQuestion.value;
    const payload = {
        question_text: q.question_text,
        question_type: q.question_type,
        points: q.points,
        is_required: q.is_required,
        explanation: q.explanation,
        options: q.options.map((opt, i) => ({
            option_text: opt.option_text,
            is_correct: opt.is_correct,
            order_position: i + 1,
        })),
    };

    if (editingQuestion.value) {
        router.post(
            safeRoute('admin.exams.questions.update', [props.exam.id, editingQuestion.value.id]),
            payload,
            {preserveScroll: true, onSuccess: () => (questionFormOpen.value = false)},
        );
    } else {
        router.post(
            safeRoute('admin.exams.questions.store', props.exam.id),
            payload,
            {preserveScroll: true, onSuccess: () => (questionFormOpen.value = false)},
        );
    }
}

function deleteQuestion(questionId: number): void {
    router.delete(
        safeRoute('admin.exams.questions.destroy', [props.exam.id, questionId]),
        {preserveScroll: true},
    );
}

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'});
}

function formatTimeTaken(seconds: number): string {
    if (!seconds) return '—';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m} min ${s}s`;
}
</script>

<template>
    <Head :title="exam.title"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.exams.index')" class="admin-muted transition hover:text-[#a23362]">
                Examens
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text max-w-[40ch] truncate font-medium">{{ exam.title }}</span>
        </template>

        <div class="mx-auto min-w-0 max-w-7xl">
            <!-- En-tête -->
            <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <Link
                        :href="safeRoute('admin.exams.index')"
                        aria-label="Retour aux examens"
                        class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                    >
                        <ArrowLeft class="size-5" :stroke-width="1.7"/>
                    </Link>
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="admin-panel-muted admin-text inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold">
                                <GraduationCap class="size-3.5" :stroke-width="1.8"/> {{ exam.examable_label }}
                            </span>
                            <span
                                :class="exam.is_active ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-500/10 text-slate-400'"
                                class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold"
                            >
                                <span class="size-1.5 rounded-full" :class="exam.is_active ? 'bg-emerald-400' : 'bg-slate-500'"/>
                                {{ exam.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        <h1 class="admin-heading break-words text-2xl font-semibold tracking-tight [overflow-wrap:anywhere] sm:text-3xl">
                            {{ exam.title }}
                        </h1>
                        <p v-if="exam.description" class="admin-muted mt-2 max-w-2xl text-sm leading-6">
                            {{ exam.description }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <ConfirmAction
                        :href="safeRoute('admin.exams.duplicate', exam.id)"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                        confirm-label="Dupliquer"
                        message="Dupliquer cet examen avec ses questions et options ?"
                        method="post"
                        title="Dupliquer"
                    >
                        <Copy class="size-4" :stroke-width="1.7"/>
                        Dupliquer
                    </ConfirmAction>
                    <ConfirmAction
                        :href="safeRoute('admin.exams.toggle-active', exam.id)"
                        :message="exam.is_active ? 'Désactiver cet examen ?' : 'Activer cet examen ?'"
                        :title="exam.is_active ? 'Désactiver' : 'Activer'"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                        confirm-label="Confirmer"
                        method="patch"
                    >
                        <Power class="size-4" :stroke-width="1.7"/>
                        {{ exam.is_active ? 'Désactiver' : 'Activer' }}
                    </ConfirmAction>
                    <Link
                        :href="safeRoute('admin.exams.edit', exam.id)"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                    >
                        <Pencil class="size-4" :stroke-width="1.8"/>
                        Modifier
                    </Link>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="admin-panel flex items-center gap-4 border p-5">
                    <span class="grid size-11 shrink-0 place-items-center" :class="stat.tint">
                        <component :is="stat.icon" class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <p class="admin-heading text-2xl font-semibold tracking-tight">{{ stat.value }}</p>
                        <p class="admin-muted text-xs">{{ stat.label }}</p>
                    </div>
                </div>
            </div>

            <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="grid min-w-0 gap-6">
                    <!-- Questions -->
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center justify-between gap-3 border-b px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 shrink-0 place-items-center bg-sky-400/10 text-sky-300">
                                    <FileQuestion class="size-5" :stroke-width="1.7"/>
                                </span>
                                <div>
                                    <h2 class="admin-heading font-semibold">Questions</h2>
                                    <p class="admin-muted mt-0.5 text-xs">{{ exam.questions_count }} question(s)</p>
                                </div>
                            </div>
                        </div>

                        <ul v-if="exam.questions.length > 0" class="divide-y divide-[color:var(--admin-border)]">
                            <li v-for="(q, index) in exam.questions" :key="q.id" class="flex items-start gap-3 px-5 py-3 sm:px-6">
                                <span class="admin-faint mt-1 w-6 shrink-0 text-xs font-semibold">{{ index + 1 }}.</span>
                                <div class="min-w-0 flex-1">
                                    <p class="admin-text text-sm font-medium">{{ q.question_text }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="admin-panel-muted admin-faint inline-flex px-1.5 py-0.5 text-[10px] font-medium">
                                            {{ typeLabels[q.question_type] ?? q.question_type }}
                                        </span>
                                        <span class="text-[10px] font-medium text-amber-300/70">{{ q.points }} pt(s)</span>
                                        <span class="text-[10px] font-medium text-slate-500">{{ q.options_count }} option(s)</span>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button
                                        class="admin-muted hover:text-rose-400 p-1 transition"
                                        title="Supprimer"
                                        @click="deleteQuestion(q.id)"
                                    >
                                        <XCircle class="size-4" :stroke-width="1.7"/>
                                    </button>
                                </div>
                            </li>
                        </ul>
                        <div v-else class="m-5 border border-dashed px-5 py-10 text-center sm:m-6">
                            <FileQuestion class="mx-auto size-8 text-slate-600" :stroke-width="1.5"/>
                            <p class="admin-heading mt-3 text-sm font-medium">Aucune question</p>
                            <p class="admin-muted mt-1 text-xs">Cet examen n'a pas encore de questions.</p>
                            <Link :href="safeRoute('admin.exams.edit', exam.id)" class="mt-4 inline-block text-sm font-semibold text-[#ef477d] hover:text-rose-300">
                                Ajouter des questions
                            </Link>
                        </div>
                    </section>

                    <!-- Tentatives -->
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-violet-400/10 text-violet-300">
                                <Users class="size-5" :stroke-width="1.7"/>
                            </span>
                            <div>
                                <h2 class="admin-heading font-semibold">Tentatives</h2>
                                <p class="admin-muted mt-0.5 text-xs">{{ exam.attempts.length }} tentative(s)</p>
                            </div>
                        </div>

                        <table v-if="exam.attempts.length > 0" class="w-full text-left text-sm">
                            <thead>
                                <tr class="admin-divider border-b text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                                    <th class="px-5 py-3 sm:px-6">Étudiant</th>
                                    <th class="px-3 py-3">N°</th>
                                    <th class="px-3 py-3">Statut</th>
                                    <th class="px-3 py-3">Score</th>
                                    <th class="px-3 py-3">Temps</th>
                                    <th class="px-3 py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--admin-border)]">
                                <tr v-for="a in exam.attempts" :key="a.id" class="admin-hover transition">
                                    <td class="px-5 py-3 sm:px-6">
                                        <Link :href="safeRoute('admin.attempts.show', a.id)" class="text-sm font-medium transition hover:text-[#a23362]">
                                            {{ a.user?.name ?? '—' }}
                                        </Link>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-slate-400">#{{ a.attempt_number }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold" :class="statusMeta[a.status]?.class ?? ''">
                                            {{ statusMeta[a.status]?.label ?? a.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-sm">
                                        <span v-if="a.percentage !== null" class="font-medium" :class="a.percentage >= exam.passing_score ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ a.score }}/{{ a.max_score }} ({{ Math.round(a.percentage) }}%)
                                        </span>
                                        <span v-else class="text-slate-500">—</span>
                                    </td>
                                    <td class="px-3 py-3 text-sm text-slate-400">{{ formatTimeTaken(a.time_taken) }}</td>
                                    <td class="px-3 py-3 text-sm text-slate-400">{{ formatDate(a.started_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="m-5 border border-dashed px-5 py-10 text-center sm:m-6">
                            <Users class="mx-auto size-8 text-slate-600" :stroke-width="1.5"/>
                            <p class="admin-heading mt-3 text-sm font-medium">Aucune tentative</p>
                            <p class="admin-muted mt-1 text-xs">Aucun étudiant n'a encore passé cet examen.</p>
                        </div>
                    </section>
                </div>

                <!-- Aside -->
                <aside class="grid min-w-0 gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Sparkles class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <h2 class="admin-heading font-semibold">Détails</h2>
                        </div>
                        <dl class="grid gap-4 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><GraduationCap class="size-4" :stroke-width="1.7"/> Rattaché à</dt>
                                <dd class="admin-heading max-w-[60%] truncate text-right text-sm font-medium">{{ exam.examable_label }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Timer class="size-4" :stroke-width="1.7"/> Durée</dt>
                                <dd class="admin-heading text-sm font-medium">{{ exam.duration_minutes }} min</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><CheckCircle2 class="size-4" :stroke-width="1.7"/> Seuil de réussite</dt>
                                <dd class="admin-heading text-sm font-medium">{{ exam.passing_score }}%</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Clock3 class="size-4" :stroke-width="1.7"/> Tentatives max</dt>
                                <dd class="admin-heading text-sm font-medium">{{ exam.max_attempts === 0 ? 'Illimité' : exam.max_attempts }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><BookOpen class="size-4" :stroke-width="1.7"/> Questions aléatoires</dt>
                                <dd class="admin-heading text-sm font-medium">{{ exam.randomize_questions ? 'Oui' : 'Non' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><CheckCircle2 class="size-4" :stroke-width="1.7"/> Résultats immédiats</dt>
                                <dd class="admin-heading text-sm font-medium">{{ exam.show_results_immediately ? 'Oui' : 'Non' }}</dd>
                            </div>
                            <div v-if="exam.available_from" class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Calendar class="size-4" :stroke-width="1.7"/> Disponible du</dt>
                                <dd class="admin-heading text-right text-sm font-medium">{{ formatDate(exam.available_from) }}</dd>
                            </div>
                            <div v-if="exam.available_until" class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Calendar class="size-4" :stroke-width="1.7"/> Jusqu'au</dt>
                                <dd class="admin-heading text-right text-sm font-medium">{{ formatDate(exam.available_until) }}</dd>
                            </div>
                        </dl>
                    </section>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>
