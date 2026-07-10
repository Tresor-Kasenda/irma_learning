<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {ArrowLeft, CheckCircle2, Clock3, RotateCcw, Timer, User as UserIcon, XCircle} from '@lucide/vue';
import {computed} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Answer {
    id: number;
    question_text: string;
    question_type: string;
    selected_option_text: string;
    answer_text: string | null;
    is_correct: boolean;
    points_earned: number;
    max_points: number;
    feedback: string | null;
}

interface AttemptDetail {
    id: number;
    user: { id: number; name: string; email: string } | null;
    exam: { id: number; title: string; passing_score: number } | null;
    attempt_number: number;
    status: string;
    score: number;
    max_score: number;
    percentage: number | null;
    time_taken: number;
    started_at: string | null;
    completed_at: string | null;
    expires_at: string | null;
    reopened_at: string | null;
    reopen_count: number;
    can_reopen: boolean;
    answers: Answer[];
}

const props = defineProps<{
    attempt: AttemptDetail;
}>();

const statusMeta: Record<string, { label: string; class: string }> = {
    in_progress: {label: 'En cours', class: 'bg-amber-400/10 text-amber-300'},
    completed: {label: 'Réussi', class: 'bg-emerald-400/10 text-emerald-300'},
    failed: {label: 'Échoué', class: 'bg-rose-400/10 text-rose-300'},
    cancelled: {label: 'Annulé', class: 'bg-slate-500/10 text-slate-400'},
    expired: {label: 'Expiré', class: 'bg-orange-400/10 text-orange-300'},
};

const typeLabels: Record<string, string> = {
    single_choice: 'Choix unique',
    multiple_choice: 'Choix multiple',
    true_false: 'Vrai/Faux',
};

const correctCount = computed(() => props.attempt.answers.filter(a => a.is_correct).length);

const isPassed = computed(() => {
    if (props.attempt.percentage === null) return false;
    return props.attempt.percentage >= (props.attempt.exam?.passing_score ?? 70);
});

function completeAttempt(): void {
    router.post(safeRoute('admin.attempts.complete', props.attempt.id), {}, {
        preserveScroll: true,
    });
}

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'});
}

function formatTimeTaken(seconds: number): string {
    if (!seconds) return '—';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    const h = Math.floor(m / 60);
    if (h > 0) return `${h}h ${m % 60}min ${s}s`;
    if (m > 0) return `${m} min ${s}s`;
    return `${s}s`;
}
</script>

<template>
    <Head :title="`Tentative #${attempt.id}`"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.attempts.index')" class="admin-muted transition hover:text-[#a23362]">
                Tentatives
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text font-medium">Tentative #{{ attempt.id }}</span>
        </template>

        <div class="mx-auto min-w-0 max-w-5xl">
            <!-- En-tête -->
            <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <Link
                        :href="safeRoute('admin.attempts.index')"
                        aria-label="Retour"
                        class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                    >
                        <ArrowLeft class="size-5" :stroke-width="1.7"/>
                    </Link>
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold"
                                :class="statusMeta[attempt.status]?.class ?? ''"
                            >
                                {{ statusMeta[attempt.status]?.label ?? attempt.status }}
                            </span>
                            <span class="admin-panel-muted admin-text inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold">
                                <UserIcon class="size-3.5" :stroke-width="1.8"/> {{ attempt.user?.name ?? '—' }}
                            </span>
                        </div>
                        <h1 class="admin-heading break-words text-2xl font-semibold tracking-tight [overflow-wrap:anywhere] sm:text-3xl">
                            {{ attempt.exam?.title ?? 'Examen' }} — Tentative #{{ attempt.attempt_number }}
                        </h1>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <ConfirmAction
                        v-if="attempt.can_reopen"
                        :href="safeRoute('admin.attempts.reopen', attempt.id)"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-5 text-sm font-semibold transition"
                        confirm-label="Réouvrir"
                        message="Réouvrir cette tentative avec un nouveau délai complet ? Les réponses déjà enregistrées seront conservées."
                        method="post"
                        title="Réouvrir la tentative"
                    >
                        <RotateCcw class="size-4" :stroke-width="1.8"/>
                        Réouvrir
                    </ConfirmAction>
                    <ConfirmAction
                        v-if="attempt.status === 'in_progress'"
                        :href="safeRoute('admin.attempts.complete', attempt.id)"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                        confirm-label="Compléter"
                        message="Compléter manuellement cette tentative ?"
                        method="post"
                        title="Compléter la tentative"
                    >
                        <CheckCircle2 class="size-4" :stroke-width="1.8"/>
                        Compléter la tentative
                    </ConfirmAction>
                </div>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="admin-panel flex items-center gap-4 border p-5">
                    <span class="grid size-11 shrink-0 place-items-center text-[#ef477d] bg-[#7d254a]/35">
                        <UserIcon class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <p class="admin-heading text-2xl font-semibold tracking-tight">{{ attempt.user?.name ?? '—' }}</p>
                        <p class="admin-muted text-xs">{{ attempt.user?.email ?? '' }}</p>
                    </div>
                </div>
                <div class="admin-panel flex items-center gap-4 border p-5">
                    <span :class="isPassed ? 'text-emerald-300 bg-emerald-400/10' : 'text-rose-300 bg-rose-400/10'" class="grid size-11 shrink-0 place-items-center">
                        <component :is="isPassed ? CheckCircle2 : XCircle" class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <p class="admin-heading text-2xl font-semibold tracking-tight">{{ attempt.percentage !== null ? `${Math.round(attempt.percentage)}%` : '—' }}</p>
                        <p class="admin-muted text-xs">{{ isPassed ? 'Réussi' : 'Non réussi' }}</p>
                    </div>
                </div>
                <div class="admin-panel flex items-center gap-4 border p-5">
                    <span class="grid size-11 shrink-0 place-items-center text-amber-300 bg-amber-400/10">
                        <CheckCircle2 class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <p class="admin-heading text-2xl font-semibold tracking-tight">{{ attempt.score }}/{{ attempt.max_score }}</p>
                        <p class="admin-muted text-xs">{{ correctCount }}/{{ attempt.answers.length }} bonnes réponses</p>
                    </div>
                </div>
                <div class="admin-panel flex items-center gap-4 border p-5">
                    <span class="grid size-11 shrink-0 place-items-center text-sky-300 bg-sky-400/10">
                        <Timer class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <p class="admin-heading text-2xl font-semibold tracking-tight">{{ formatTimeTaken(attempt.time_taken) }}</p>
                        <p class="admin-muted text-xs">Temps passé</p>
                    </div>
                </div>
            </div>

            <!-- Réponses -->
            <section class="admin-panel border">
                <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                    <span class="grid size-10 shrink-0 place-items-center bg-violet-400/10 text-violet-300">
                        <CheckCircle2 class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <h2 class="admin-heading font-semibold">Réponses</h2>
                        <p class="admin-muted mt-0.5 text-xs">{{ attempt.answers.length }} question(s)</p>
                    </div>
                </div>

                <ul v-if="attempt.answers.length > 0" class="divide-y divide-[color:var(--admin-border)]">
                    <li v-for="(answer, index) in attempt.answers" :key="answer.id" class="px-5 py-4 sm:px-6">
                        <div class="mb-2 flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2 min-w-0">
                                <span class="admin-faint mt-0.5 w-5 shrink-0 text-xs font-semibold">{{ index + 1 }}.</span>
                                <div class="min-w-0">
                                    <p class="admin-text text-sm font-medium">{{ answer.question_text }}</p>
                                    <span class="admin-faint text-[10px]">{{ typeLabels[answer.question_type] ?? answer.question_type }}</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-xs font-medium">
                                    <span :class="answer.is_correct ? 'text-emerald-400' : 'text-rose-400'">
                                        {{ answer.points_earned }}/{{ answer.max_points }}
                                    </span>
                                    pt
                                </span>
                                <component
                                    :is="answer.is_correct ? CheckCircle2 : XCircle"
                                    :class="answer.is_correct ? 'text-emerald-400' : 'text-rose-400'"
                                    class="size-4 shrink-0"
                                    :stroke-width="1.7"
                                />
                            </div>
                        </div>
                        <div class="ml-7">
                            <p class="admin-muted text-xs">
                                <span class="font-medium">Réponse :</span> {{ answer.selected_option_text || answer.answer_text || '—' }}
                            </p>
                            <p v-if="answer.feedback" class="mt-1 text-xs text-sky-300">Feedback : {{ answer.feedback }}</p>
                        </div>
                    </li>
                </ul>
                <div v-else class="m-5 border border-dashed px-5 py-10 text-center sm:m-6">
                    <p class="admin-muted text-sm italic">Aucune réponse enregistrée.</p>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
