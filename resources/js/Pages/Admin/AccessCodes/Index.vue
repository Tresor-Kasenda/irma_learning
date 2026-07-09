<script lang="ts" setup>
import {Head, useForm} from '@inertiajs/vue3';
import {Download, KeyRound, Plus, Trash2} from '@lucide/vue';
import {ref} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import DatePicker from '@/Components/Admin/Fields/DatePicker.vue';
import NumberField from '@/Components/Admin/Fields/NumberField.vue';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import ResourceFormModal from '@/Components/Admin/ResourceFormModal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface Option {value: string; label: string}
interface CodeRow {id: number; code: string; formation: {id: number; title: string} | null; is_used: boolean; user: {id: number; name: string} | null; used_at: string | null; expires_at: string | null; created_at: string}
interface PageData {data: CodeRow[]; from: number | null; to: number | null; total: number; links: {url: string | null; label: string; active: boolean}[]}

const props = defineProps<{codes: PageData; filters: Record<string, string | undefined>; formations: Option[]}>();
const indexRoute = safeRoute('admin.access-codes.index');
const showGenerate = ref(false);
const generateForm = useForm({formation_id: '', quantity: 20, expires_at: null as string | null});

const columns: Column[] = [
    {key: 'code', label: 'Code'},
    {key: 'formation', label: 'Formation'},
    {key: 'is_used', label: 'Utilisé', type: 'boolean', align: 'center'},
    {key: 'user', label: 'Utilisé par'},
    {key: 'expires_at', label: 'Expiration', type: 'date'},
];

const filterDefs: FilterDef[] = [
    {key: 'formation_id', label: 'Formation', options: props.formations},
    {key: 'is_used', label: 'Statut', options: [{value: '1', label: 'Utilisés'}, {value: '0', label: 'Disponibles'}]},
    {key: 'per_page', label: 'Lignes', options: [{value: '10', label: '10 lignes'}, {value: '25', label: '25 lignes'}, {value: '50', label: '50 lignes'}, {value: '100', label: '100 lignes'}], includeEmpty: false, defaultValue: '25'},
];

function openGenerate(): void {
    generateForm.reset();
    generateForm.clearErrors();
    showGenerate.value = true;
}

function generate(): void {
    generateForm.post(safeRoute('admin.access-codes.generate'), {preserveScroll: true, onSuccess: () => showGenerate.value = false});
}

function exportUrl(): string {
    const params = new URLSearchParams();
    if (props.filters.formation_id) params.set('formation_id', props.filters.formation_id);
    if (props.filters.is_used) params.set('is_used', props.filters.is_used);
    const query = params.toString();

    return safeRoute('admin.access-codes.export') + (query ? `?${query}` : '');
}
</script>

<template>
    <Head title="Codes d’accès"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Codes d’accès</span></template>
        <div class="mx-auto min-w-0 max-w-7xl">
            <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Inscriptions</p><h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Codes d’accès</h1><p class="admin-muted mt-2 text-sm">{{ codes.total }} code(s). Permettent une inscription gratuite à une formation.</p></div>
                <div class="flex items-center gap-3">
                    <a :href="exportUrl()" class="admin-panel-muted admin-text admin-hover inline-flex h-11 items-center gap-2 border px-5 text-sm font-semibold"><Download class="size-4"/>Exporter CSV</a>
                    <button class="inline-flex h-11 shrink-0 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white" type="button" @click="openGenerate"><Plus class="size-4"/>Générer des codes</button>
                </div>
            </header>
            <DataTable :columns="columns" :filters="filters" :index-route="indexRoute" :rows="codes">
                <template #filters><FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/></template>
                <template #cell-code="{row}"><span class="admin-heading font-mono text-sm font-semibold tracking-wide"><KeyRound class="mr-1.5 inline size-3.5 text-[#ef477d]"/>{{ row.code }}</span></template>
                <template #cell-formation="{row}"><span class="admin-text text-sm">{{ row.formation?.title ?? '—' }}</span></template>
                <template #cell-user="{row}"><span class="admin-muted text-sm">{{ row.user?.name ?? '—' }}</span></template>
                <template #actions="{row}">
                    <ConfirmAction v-if="!row.is_used" :href="safeRoute('admin.access-codes.destroy', row.id)" class="p-2 text-rose-500 transition hover:bg-rose-400/10 hover:text-rose-300" danger message="Ce code d’accès sera supprimé définitivement." method="delete" title="Supprimer le code">
                        <Trash2 class="size-4"/>
                    </ConfirmAction>
                </template>
            </DataTable>
        </div>

        <ResourceFormModal :show="showGenerate" :processing="generateForm.processing" title="Générer des codes d’accès" submit-label="Générer" @close="showGenerate = false" @submit="generate">
            <div class="grid gap-5">
                <SearchableSelect v-model="generateForm.formation_id" :clearable="false" :error="generateForm.errors.formation_id" :options="formations" label="Formation" required/>
                <NumberField id="quantity" v-model="generateForm.quantity" :error="generateForm.errors.quantity" :max="200" :min="1" label="Quantité" required/>
                <DatePicker v-model="generateForm.expires_at" :error="generateForm.errors.expires_at" hint="Laissez vide pour des codes sans expiration." label="Date d’expiration"/>
            </div>
        </ResourceFormModal>
    </AdminLayout>
</template>
