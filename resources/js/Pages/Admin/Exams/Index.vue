<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {CheckCircle, ClipboardCheck, Copy, Eye, GraduationCap, Pencil, Plus, Power, Trash2, XCircle} from '@lucide/vue';
import {ref} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {notify} from '@/utilities/toast';

interface ExamRow {
    id: number;
    title: string;
    examable_type: string;
    examable: { id: number; title: string } | null;
    duration_minutes: number;
    passing_score: number;
    questions_count: number;
    is_active: boolean;
}

interface ExamsPage {
    data: ExamRow[];
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    exams: ExamsPage;
    filters: Record<string, string | undefined>;
}>();

const indexRoute = safeRoute('admin.exams.index');

const typeLabels: Record<string, string> = {
    'App\\Models\\Section': 'Section',
    'App\\Models\\Formation': 'Formation',
};

const columns: Column[] = [
    {key: 'title', label: 'Titre', sortable: true},
    {key: 'examable_type', label: 'Type'},
    {key: 'duration_minutes', label: 'Durée', sortable: true, align: 'right'},
    {key: 'passing_score', label: 'Seuil', sortable: true, align: 'right'},
    {key: 'questions_count', label: 'Questions', align: 'center'},
    {key: 'is_active', label: 'Actif', type: 'boolean', align: 'center'},
];

const filterDefs: FilterDef[] = [
    {
        key: 'examable_type',
        label: 'Type',
        options: [
            {value: 'Section', label: 'Section'},
            {value: 'Formation', label: 'Formation'},
        ],
    },
    {
        key: 'is_active',
        label: 'Statut',
        options: [{value: '1', label: 'Actifs'}, {value: '0', label: 'Inactifs'}],
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

const selected = ref<Array<number | string>>([]);

function typeLabel(value: string | unknown): string {
    return typeLabels[value as string] ?? (value as string);
}

function questionsBadgeClass(count: number): string {
    if (count === 0) return 'bg-rose-400/10 text-rose-400';
    if (count < 5) return 'bg-amber-400/10 text-amber-300';
    if (count >= 10) return 'bg-emerald-400/10 text-emerald-300';
    return 'admin-panel-muted admin-text';
}

function bulkAction(routeName: string): void {
    if (selected.value.length === 0) {
        notify({type: 'error', message: 'Sélectionnez au moins un examen.'});
        return;
    }
    router.post(safeRoute(routeName), {ids: selected.value}, {
        preserveScroll: true,
        onSuccess: () => { selected.value = []; },
    });
}
</script>

<template>
    <Head title="Examens"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="admin-text font-medium">Examens</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Évaluations</p>
                    <h1 class="admin-heading mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Examens</h1>
                    <p class="admin-muted mt-2 text-sm">{{ exams.total }} examen(s).</p>
                </div>
                <Link
                    :href="safeRoute('admin.exams.create')"
                    class="inline-flex h-11 items-center justify-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                >
                    <Plus class="size-4" :stroke-width="2"/>
                    Nouvel examen
                </Link>
            </div>

            <DataTable
                :columns="columns"
                :filters="filters"
                :index-route="indexRoute"
                :rows="exams"
                searchable
                selectable
                @update:selected="selected = $event"
            >
                <template #bulk="{selected}">
                    <div v-if="selected.length > 0"
                         class="admin-divider flex flex-wrap items-center gap-2 border-b px-4 py-3">
                        <span class="admin-muted text-xs font-medium">{{ selected.length }} sélectionné(s)</span>
                        <button
                            class="inline-flex h-8 items-center gap-1.5 border px-3 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-400/10"
                            type="button"
                            @click="bulkAction('admin.exams.bulk.activate')"
                        >
                            <CheckCircle class="size-3.5"/> Activer
                        </button>
                        <button
                            class="inline-flex h-8 items-center gap-1.5 border px-3 text-xs font-semibold text-amber-400 transition hover:bg-amber-400/10"
                            type="button"
                            @click="bulkAction('admin.exams.bulk.deactivate')"
                        >
                            <XCircle class="size-3.5"/> Désactiver
                        </button>
                        <button
                            class="inline-flex h-8 items-center gap-1.5 border px-3 text-xs font-semibold text-sky-400 transition hover:bg-sky-400/10"
                            type="button"
                            @click="bulkAction('admin.exams.bulk.duplicate')"
                        >
                            <Copy class="size-3.5"/> Dupliquer
                        </button>
                    </div>
                </template>

                <template #filters>
                    <FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/>
                </template>

                <template #cell-examable_type="{value}">
                    <span class="admin-panel-muted admin-text inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium">
                        <GraduationCap class="size-3.5" :stroke-width="1.8"/>
                        {{ typeLabel(value) }}
                    </span>
                </template>

                <template #cell-duration_minutes="{value}">
                    <span class="admin-text font-medium">{{ value }} min</span>
                </template>

                <template #cell-passing_score="{value}">
                    <span class="admin-text font-medium">{{ value }}%</span>
                </template>

                <template #cell-questions_count="{value}">
                    <span :class="[questionsBadgeClass(Number(value)), 'inline-flex px-2 py-0.5 text-xs font-medium']">
                        {{ value }}
                    </span>
                </template>

                <template #actions="{row}">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="safeRoute('admin.exams.show', row.id)"
                            :aria-label="`Voir ${row.title}`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Voir le détail"
                        >
                            <Eye class="size-4" :stroke-width="1.7"/>
                        </Link>
                        <Link
                            :href="safeRoute('admin.exams.edit', row.id)"
                            :aria-label="`Modifier ${row.title}`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Modifier"
                        >
                            <Pencil class="size-4" :stroke-width="1.7"/>
                        </Link>
                        <ConfirmAction
                            :href="safeRoute('admin.exams.toggle-active', row.id)"
                            :message="row.is_active ? 'Désactiver cet examen ?' : 'Activer cet examen ?'"
                            :title="row.is_active ? 'Désactiver' : 'Activer'"
                            class="admin-muted admin-hover p-2 transition"
                            confirm-label="Confirmer"
                            method="patch"
                        >
                            <Power class="size-4" :stroke-width="1.7"/>
                        </ConfirmAction>
                        <ConfirmAction
                            :href="safeRoute('admin.exams.destroy', row.id)"
                            class="p-2 text-rose-500 transition hover:bg-rose-400/10 hover:text-rose-300"
                            danger
                            message="Les questions, options et tentatives seront supprimées."
                            method="delete"
                            title="Supprimer l'examen"
                        >
                            <Trash2 class="size-4" :stroke-width="1.7"/>
                        </ConfirmAction>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
