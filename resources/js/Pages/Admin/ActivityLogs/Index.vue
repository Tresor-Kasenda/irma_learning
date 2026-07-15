<script lang="ts" setup>
import {Head, router} from '@inertiajs/vue3';
import {Eye, History} from '@lucide/vue';
import {ref} from 'vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import DatePicker from '@/Components/Admin/Fields/DatePicker.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import ResourceFormModal from '@/Components/Admin/ResourceFormModal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Option {value: string; label: string}

interface ActivityRow {
    id: number;
    log_name: string | null;
    event: string | null;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    causer: {id: number; name: string} | null;
    properties: Record<string, unknown>;
    created_at: string | null;
}

interface PageData {
    data: ActivityRow[];
    from: number | null;
    to: number | null;
    total: number;
    links: {url: string | null; label: string; active: boolean}[];
}

const props = defineProps<{
    activities: PageData;
    filters: Record<string, string | undefined>;
    logNameOptions: Option[];
    eventOptions: Option[];
}>();

const indexRoute = safeRoute('admin.activity-logs.index');
const showDetails = ref(false);
const selected = ref<ActivityRow | null>(null);

const columns: Column[] = [
    {key: 'created_at', label: 'Date', type: 'date'},
    {key: 'log_name', label: 'Modèle', type: 'badge'},
    {key: 'event', label: 'Événement', type: 'badge'},
    {key: 'causer', label: 'Auteur'},
    {key: 'description', label: 'Description'},
];

const filterDefs: FilterDef[] = [
    {key: 'log_name', label: 'Modèle', options: props.logNameOptions},
    {key: 'event', label: 'Événement', options: props.eventOptions},
    {key: 'per_page', label: 'Lignes', options: [{value: '25', label: '25 lignes'}, {value: '50', label: '50 lignes'}, {value: '100', label: '100 lignes'}, {value: '200', label: '200 lignes'}], includeEmpty: false, defaultValue: '50'},
];

function formatDateTime(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function openDetails(row: ActivityRow): void {
    selected.value = row;
    showDetails.value = true;
}

function updateDateFilter(key: 'date_from' | 'date_to', value: string | null): void {
    router.get(indexRoute, {...props.filters, [key]: value ?? undefined, page: undefined}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Journal d’activité"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Journal d’activité</span></template>
        <div class="mx-auto min-w-0 max-w-7xl">
            <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Administration</p>
                    <h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Journal d’activité</h1>
                    <p class="admin-muted mt-2 text-sm">{{ activities.total }} événement(s). Qui a fait quoi, quand, et depuis où.</p>
                </div>
            </header>

            <DataTable :columns="columns" :filters="filters" :index-route="indexRoute" :rows="activities" searchable>
                <template #filters>
                    <FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/>
                    <DatePicker :model-value="filters.date_from ?? null" class="min-w-40"
                                label="Du" @update:model-value="value => updateDateFilter('date_from', value)"/>
                    <DatePicker :model-value="filters.date_to ?? null" class="min-w-40"
                                label="Au" @update:model-value="value => updateDateFilter('date_to', value)"/>
                </template>
                <template #cell-log_name="{row}"><History class="mr-1 inline size-3.5 text-[#ef477d]"/>{{ row.log_name ?? '—' }}</template>
                <template #cell-causer="{row}"><span class="admin-text text-sm">{{ row.causer?.name ?? 'Système' }}</span></template>
                <template #cell-description="{row}">
                    <span class="admin-text text-sm">{{ row.description }}</span>
                    <span v-if="row.subject_type" class="admin-muted ml-1 text-xs">({{ row.subject_type }} #{{ row.subject_id }})</span>
                </template>
                <template #actions="{row}">
                    <button class="admin-panel-muted admin-text admin-hover p-2 transition" title="Voir les détails" type="button" @click="openDetails(row)">
                        <Eye class="size-4"/>
                    </button>
                </template>
            </DataTable>
        </div>

        <ResourceFormModal :processing="false" :show="showDetails" size="lg" title="Détails de l’activité" @close="showDetails = false" @submit="showDetails = false">
            <div v-if="selected" class="grid min-w-0 gap-4 text-sm">
                <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                    <div class="min-w-0"><p class="admin-muted text-xs uppercase tracking-wide">Date</p><p class="admin-text mt-1 break-words">{{ formatDateTime(selected.created_at) }}</p></div>
                    <div class="min-w-0"><p class="admin-muted text-xs uppercase tracking-wide">Auteur</p><p class="admin-text mt-1 break-words">{{ selected.causer?.name ?? 'Système' }}</p></div>
                    <div class="min-w-0"><p class="admin-muted text-xs uppercase tracking-wide">Modèle</p><p class="admin-text mt-1 break-words">{{ selected.log_name ?? '—' }}</p></div>
                    <div class="min-w-0"><p class="admin-muted text-xs uppercase tracking-wide">Événement</p><p class="admin-text mt-1 break-words">{{ selected.event ?? '—' }}</p></div>
                </div>
                <div class="min-w-0"><p class="admin-muted text-xs uppercase tracking-wide">Description</p><p class="admin-text mt-1 break-words">{{ selected.description }}</p></div>
                <div class="min-w-0">
                    <p class="admin-muted text-xs uppercase tracking-wide">Propriétés</p>
                    <pre class="admin-panel-muted admin-text mt-1 max-h-96 max-w-full overflow-x-hidden overflow-y-auto whitespace-pre-wrap break-all p-3 text-xs leading-5">{{ JSON.stringify(selected.properties, null, 2) }}</pre>
                </div>
            </div>
        </ResourceFormModal>
    </AdminLayout>
</template>
