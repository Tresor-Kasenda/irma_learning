<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {Award, Eye} from '@lucide/vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface StudentRow {id: number; name: string; email: string; avatar_url: string; certificates_count: number; enrollments_count: number; latest_certificate: {formation: string; issue_date: string} | null}
defineProps<{students: {data: StudentRow[]; from: number | null; to: number | null; total: number; links: {url: string | null; label: string; active: boolean}[]}; filters: Record<string, string | undefined>} >();
const columns: Column[] = [{key: 'name', label: 'Apprenant'}, {key: 'certificates_count', label: 'Certificats'}, {key: 'enrollments_count', label: 'Formations'}, {key: 'latest_certificate', label: 'Dernier certificat'}];
const filterDefs: FilterDef[] = [{key: 'per_page', label: 'Lignes', options: [{value: '10', label: '10 lignes'}, {value: '25', label: '25 lignes'}, {value: '50', label: '50 lignes'}], includeEmpty: false, defaultValue: '10'}];

function latestCertificate(value: unknown): StudentRow['latest_certificate'] {
    return value as StudentRow['latest_certificate'];
}
</script>

<template>
    <Head title="Certificats"/>
    <AdminLayout>
        <template #breadcrumb><span class="admin-text font-medium">Certificats</span></template>
        <div class="mx-auto min-w-0 max-w-7xl">
            <header class="mb-7"><p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Apprenants</p><h1 class="admin-heading mt-2 text-2xl font-semibold sm:text-3xl">Étudiants certifiés</h1><p class="admin-muted mt-2 text-sm">{{ students.total }} étudiant(s) avec au moins un certificat.</p></header>
            <DataTable :columns="columns" :filters="filters" :index-route="safeRoute('admin.certificates.index')" :rows="students" searchable>
                <template #filters><FilterBar :definitions="filterDefs" :filters="filters" :index-route="safeRoute('admin.certificates.index')"/></template>
                <template #cell-name="{row}"><div class="flex min-w-0 items-center gap-3"><img :src="row.avatar_url" alt="" class="size-10 shrink-0 rounded-full object-cover"/><div class="min-w-0"><p class="admin-heading truncate text-sm font-medium">{{ row.name }}</p><p class="admin-muted truncate text-xs">{{ row.email }}</p></div></div></template>
                <template #cell-certificates_count="{value}"><span class="inline-flex items-center gap-2 text-sm font-semibold text-[#ef477d]"><Award class="size-4"/>{{ value }}</span></template>
                <template #cell-latest_certificate="{value}"><div v-if="latestCertificate(value)" class="text-sm"><p class="admin-text">{{ latestCertificate(value)?.formation }}</p><p class="admin-faint text-xs">{{ new Date(latestCertificate(value)!.issue_date).toLocaleDateString('fr-FR') }}</p></div><span v-else>—</span></template>
                <template #actions="{row}"><Link :href="safeRoute('admin.certificates.show', row.id)" class="admin-muted admin-hover grid size-9 place-items-center" title="Voir le profil"><Eye class="size-4"/></Link></template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
