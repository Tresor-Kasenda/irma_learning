<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {CheckCircle2, Eye, XCircle} from '@lucide/vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface AttemptRow {
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
}

interface AttemptsPage {
    data: AttemptRow[];
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    attempts: AttemptsPage;
    filters: Record<string, string | undefined>;
}>();

const indexRoute = safeRoute('admin.attempts.index');

const statusMeta: Record<string, { label: string; class: string }> = {
    in_progress: {label: 'En cours', class: 'bg-amber-400/10 text-amber-300'},
    completed: {label: 'Réussi', class: 'bg-emerald-400/10 text-emerald-300'},
    failed: {label: 'Échoué', class: 'bg-rose-400/10 text-rose-300'},
    cancelled: {label: 'Annulé', class: 'bg-slate-500/10 text-slate-400'},
};

const columns: Column[] = [
    {key: 'user', label: 'Étudiant'},
    {key: 'exam', label: 'Examen'},
    {key: 'attempt_number', label: 'N°'},
    {key: 'status', label: 'Statut'},
    {key: 'percentage', label: 'Score', align: 'right'},
    {key: 'started_at', label: 'Date', sortable: true, align: 'right'},
];

const filterDefs: FilterDef[] = [
    {
        key: 'status',
        label: 'Statut',
        options: [
            {value: 'in_progress', label: 'En cours'},
            {value: 'completed', label: 'Réussi'},
            {value: 'failed', label: 'Échoué'},
            {value: 'cancelled', label: 'Annulé'},
        ],
    },
    {
        key: 'is_passed',
        label: 'Résultat',
        options: [{value: '1', label: 'Réussi'}, {value: '0', label: 'Échoué'}],
    },
    {
        key: 'per_page',
        label: 'Lignes par page',
        options: [
            {value: '10', label: '10 lignes'},
            {value: '25', label: '25 lignes'},
            {value: '50', label: '50 lignes'},
            {value: '100', label: '100 lignes'},
        ],
        includeEmpty: false,
        defaultValue: '10',
    },
];

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});
}

function userName(value: unknown): string {
    const user = value as { name?: string } | null;
    return user?.name ?? '—';
}

function examTitle(value: unknown): string {
    const exam = value as { title?: string } | null;
    return exam?.title ?? '—';
}

function statusLabel(value: unknown): string {
    return statusMeta[value as string]?.label ?? String(value ?? '');
}

function statusClass(value: unknown): string {
    return statusMeta[value as string]?.class ?? '';
}

function safeString(value: unknown): string | null {
    if (value === null || value === undefined) return null;
    return String(value);
}
</script>

<template>
    <Head title="Tentatives"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="admin-text font-medium">Tentatives</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Évaluations</p>
                    <h1 class="admin-heading mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Tentatives</h1>
                    <p class="admin-muted mt-2 text-sm">{{ attempts.total }} tentative(s).</p>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :filters="filters"
                :index-route="indexRoute"
                :rows="attempts"
                searchable
            >
                <template #filters>
                    <FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/>
                </template>

                <template #cell-user="{value}">
                    <div class="text-sm">
                        <span class="admin-heading font-medium">{{ userName(value) }}</span>
                    </div>
                </template>

                <template #cell-exam="{value}">
                    <span class="admin-text text-sm">{{ examTitle(value) }}</span>
                </template>

                <template #cell-attempt_number="{value}">
                    <span class="admin-faint text-sm">#{{ value }}</span>
                </template>

                <template #cell-status="{value}">
                    <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold" :class="statusClass(value)">
                        {{ statusLabel(value) }}
                    </span>
                </template>

                <template #cell-percentage="{row}">
                    <span v-if="row.percentage !== null" class="flex items-center gap-1 text-sm">
                        <CheckCircle2 v-if="row.percentage >= ((row as any).exam?.passing_score ?? 70)" class="size-3.5 text-emerald-400" :stroke-width="1.8"/>
                        <XCircle v-else class="size-3.5 text-rose-400" :stroke-width="1.8"/>
                        {{ Math.round(row.percentage) }}%
                    </span>
                    <span v-else class="admin-faint text-sm">—</span>
                </template>

                <template #cell-started_at="{value}">
                    <span class="admin-faint text-sm">{{ formatDate(safeString(value)) }}</span>
                </template>

                <template #actions="{row}">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="safeRoute('admin.attempts.show', row.id)"
                            :aria-label="`Voir la tentative`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Voir le détail"
                        >
                            <Eye class="size-4" :stroke-width="1.7"/>
                        </Link>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
