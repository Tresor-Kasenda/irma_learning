<script lang="ts" setup>
import {Head} from '@inertiajs/vue3';
import {BookOpen} from '@lucide/vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface EnrollmentRow {
    id: number;
    user: {name: string; email: string; avatar_url: string} | null;
    formation: {title: string} | null;
    status: string;
    status_label: string;
    payment_status: string;
    payment_label: string;
    progress_percentage: number;
    enrollment_date: string | null;
}

defineProps<{enrollments: {data: EnrollmentRow[]; from: number | null; to: number | null; total: number; links: {url: string | null; label: string; active: boolean}[]}; filters: Record<string, string | undefined>} >();

const columns: Column[] = [
    {key: 'user', label: 'Apprenant'},
    {key: 'formation', label: 'Formation'},
    {key: 'status', label: 'Inscription'},
    {key: 'payment_status', label: 'Paiement'},
    {key: 'progress_percentage', label: 'Progression'},
    {key: 'enrollment_date', label: 'Date', align: 'right'},
];

const filterDefs: FilterDef[] = [
    {key: 'status', label: 'Statut', options: [{value: 'active', label: 'Actif'}, {value: 'completed', label: 'Terminé'}, {value: 'suspended', label: 'Suspendu'}, {value: 'cancelled', label: 'Annulé'}]},
    {key: 'payment_status', label: 'Paiement', options: [{value: 'paid', label: 'Payé'}, {value: 'free', label: 'Gratuit'}, {value: 'pending', label: 'En attente'}, {value: 'refunded', label: 'Remboursé'}]},
    {key: 'per_page', label: 'Lignes', options: [{value: '10', label: '10 lignes'}, {value: '25', label: '25 lignes'}, {value: '50', label: '50 lignes'}], includeEmpty: false, defaultValue: '10'},
];

function formatDate(value: unknown): string {
    return value ? new Date(String(value)).toLocaleDateString('fr-FR') : '—';
}

function userValue(value: unknown): EnrollmentRow['user'] {
    return value as EnrollmentRow['user'];
}

function formationValue(value: unknown): EnrollmentRow['formation'] {
    return value as EnrollmentRow['formation'];
}
</script>

<template>
    <Head title="Inscriptions"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Inscriptions</span></template>
        <div class="mx-auto min-w-0 max-w-7xl">
            <header class="mb-7">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Apprenants</p>
                <h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Inscriptions</h1>
                <p class="admin-muted mt-2 text-sm">{{ enrollments.total }} inscription(s) aux formations.</p>
            </header>
            <DataTable :columns="columns" :filters="filters" :index-route="safeRoute('admin.enrollments.index')" :rows="enrollments" searchable>
                <template #filters><FilterBar :definitions="filterDefs" :filters="filters" :index-route="safeRoute('admin.enrollments.index')"/></template>
                <template #cell-user="{value}"><div class="flex min-w-0 items-center gap-3"><img v-if="userValue(value)" :src="userValue(value)?.avatar_url" alt="" class="size-9 shrink-0 rounded-full object-cover"/><div class="min-w-0"><p class="admin-heading truncate text-sm font-medium">{{ userValue(value)?.name ?? '—' }}</p><p class="admin-muted truncate text-xs">{{ userValue(value)?.email }}</p></div></div></template>
                <template #cell-formation="{value}"><span class="admin-text inline-flex min-w-0 items-center gap-2 text-sm"><BookOpen class="size-4 shrink-0 text-[#ef477d]"/><span class="truncate">{{ formationValue(value)?.title ?? '—' }}</span></span></template>
                <template #cell-status="{row}"><span class="bg-emerald-400/10 px-2 py-1 text-xs font-medium text-emerald-400">{{ row.status_label }}</span></template>
                <template #cell-payment_status="{row}"><span class="admin-panel-muted px-2 py-1 text-xs">{{ row.payment_label }}</span></template>
                <template #cell-progress_percentage="{value}"><div class="flex min-w-28 items-center gap-2"><div class="h-1.5 flex-1 overflow-hidden bg-slate-500/15"><div class="h-full bg-[#a23362]" :style="{width: `${Math.min(100, Number(value))}%`}"/></div><span class="admin-muted w-10 text-right text-xs">{{ Math.round(Number(value)) }}%</span></div></template>
                <template #cell-enrollment_date="{value}"><span class="admin-faint text-sm">{{ formatDate(value) }}</span></template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
