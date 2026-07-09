<script lang="ts" setup>
import {Head, router} from '@inertiajs/vue3';
import {CheckCircle, Play} from '@lucide/vue';
import {ref} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {notify} from '@/utilities/toast';

interface Option {value: string; label: string}
interface ProgressRow {id: number; user: {id: number; name: string} | null; trackable_type: string; trackable_title: string | null; formation_title: string | null; status: string; status_label: string; time_spent: number | null; started_at: string | null; completed_at: string | null}
interface PageData {data: ProgressRow[]; from: number | null; to: number | null; total: number; links: {url: string | null; label: string; active: boolean}[]}

const props = defineProps<{progress: PageData; filters: Record<string, string | undefined>; statusOptions: Option[]}>();
const indexRoute = safeRoute('admin.progress.index');
const selected = ref<Array<number | string>>([]);

const columns: Column[] = [
    {key: 'user', label: 'Utilisateur'},
    {key: 'trackable_type', label: 'Type'},
    {key: 'trackable_title', label: 'Élément'},
    {key: 'formation_title', label: 'Formation'},
    {key: 'status', label: 'Statut'},
    {key: 'time_spent', label: 'Temps (min)', align: 'right'},
];

const filterDefs: FilterDef[] = [
    {key: 'status', label: 'Statut', options: props.statusOptions},
    {key: 'trackable_type', label: 'Type', options: [{value: 'Chapter', label: 'Chapitre'}, {value: 'Section', label: 'Section'}]},
    {key: 'per_page', label: 'Lignes', options: [{value: '10', label: '10 lignes'}, {value: '25', label: '25 lignes'}, {value: '50', label: '50 lignes'}, {value: '100', label: '100 lignes'}], includeEmpty: false, defaultValue: '25'},
];

function statusClass(status: string): string {
    return {not_started: 'admin-panel-muted admin-text', in_progress: 'bg-amber-400/10 text-amber-300', completed: 'bg-emerald-400/10 text-emerald-300'}[status] ?? 'admin-panel-muted admin-text';
}

function bulkAction(routeName: string): void {
    if (selected.value.length === 0) {
        notify({type: 'error', message: 'Sélectionnez au moins une ligne.'});
        return;
    }
    router.post(safeRoute(routeName), {ids: selected.value}, {preserveScroll: true, onSuccess: () => { selected.value = []; }});
}
</script>

<template>
    <Head title="Progression des apprenants"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Progression</span></template>
        <div class="mx-auto min-w-0 max-w-7xl">
            <header class="mb-7"><p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Apprenants</p><h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Progression</h1><p class="admin-muted mt-2 text-sm">{{ progress.total }} entrée(s). Correction ponctuelle du statut ; la progression réelle reste pilotée côté étudiant.</p></header>
            <DataTable :columns="columns" :filters="filters" :index-route="indexRoute" :rows="progress" searchable selectable @update:selected="selected = $event">
                <template #filters><FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/></template>
                <template #bulk="{selected: sel}">
                    <div v-if="sel.length > 0" class="admin-divider flex flex-wrap items-center gap-2 border-b px-4 py-3">
                        <span class="admin-muted text-xs font-medium">{{ sel.length }} sélectionné(s)</span>
                        <button class="inline-flex h-8 items-center gap-1.5 border px-3 text-xs font-semibold text-sky-400 transition hover:bg-sky-400/10" type="button" @click="bulkAction('admin.progress.bulk-mark-started')"><Play class="size-3.5"/>Marquer commencé</button>
                        <button class="inline-flex h-8 items-center gap-1.5 border px-3 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-400/10" type="button" @click="bulkAction('admin.progress.bulk-mark-completed')"><CheckCircle class="size-3.5"/>Marquer complété</button>
                    </div>
                </template>
                <template #cell-user="{row}"><span class="admin-heading text-sm font-medium">{{ row.user?.name ?? '—' }}</span></template>
                <template #cell-trackable_type="{row}"><span class="admin-panel-muted admin-text inline-flex px-2 py-0.5 text-xs font-medium">{{ row.trackable_type === 'Chapter' ? 'Chapitre' : 'Section' }}</span></template>
                <template #cell-status="{row}"><span :class="[statusClass(row.status), 'inline-flex px-2 py-0.5 text-xs font-medium']">{{ row.status_label }}</span></template>
                <template #actions="{row}">
                    <div class="flex items-center justify-end gap-1">
                        <ConfirmAction v-if="row.status === 'not_started'" :href="safeRoute('admin.progress.mark-started', row.id)" class="admin-muted admin-hover p-2 transition" message="Marquer cette progression comme commencée ?" method="post" title="Marquer commencé">
                            <Play class="size-4"/>
                        </ConfirmAction>
                        <ConfirmAction v-if="row.status !== 'completed'" :href="safeRoute('admin.progress.mark-completed', row.id)" class="p-2 text-emerald-500 transition hover:bg-emerald-400/10" message="Marquer cette progression comme complétée ?" method="post" title="Marquer complété">
                            <CheckCircle class="size-4"/>
                        </ConfirmAction>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
