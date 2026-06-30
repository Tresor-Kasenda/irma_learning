<script lang="ts" setup>
import {Head} from '@inertiajs/vue3';
import {computed} from 'vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Stats {
    formations: number;
    sections: number;
    exams: number;
    enrollments: number;
    activeEnrollments: number;
    certificates: number;
    students: number;
    revenue: number;
}

interface CatalogStats {
    formations: number;
    videos: number;
    pdfs: number;
    texts: number;
}

const props = defineProps<{
    stats: Stats;
    catalogStats: CatalogStats;
}>();

const formattedRevenue = computed(() =>
    new Intl.NumberFormat('fr-FR', {style: 'currency', currency: 'USD', maximumFractionDigits: 0}).format(props.stats.revenue),
);

const cards = computed(() => [
    {label: 'Formations', value: props.stats.formations, icon: 'M12 6.2L3 11l9 4.8L21 11 12 6.2z'},
    {label: 'Sections', value: props.stats.sections, icon: 'M4 6h16M4 12h16M4 18h10'},
    {label: 'Examens', value: props.stats.exams, icon: 'M9 12l2 2 4-4M5 4h14v16H5z'},
    {label: 'Apprenants', value: props.stats.students, icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-1a7 7 0 0114 0v1'},
    {label: 'Inscriptions actives', value: props.stats.activeEnrollments, icon: 'M9 12l2 2 4-4'},
    {label: 'Certificats délivrés', value: props.stats.certificates, icon: 'M12 15a4 4 0 100-8 4 4 0 000 8z'},
    {label: 'Revenus', value: formattedRevenue.value, icon: 'M12 8c-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2m0-8V6m0 12v-2'},
]);
</script>

<template>
    <Head title="Administration"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="font-medium text-slate-700">Tableau de bord</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <h1 class="text-2xl font-semibold text-slate-900">Tableau de bord</h1>
            <p class="mt-1 text-sm text-slate-500">Vue d'ensemble de la plateforme.</p>

            <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <StatCard
                    v-for="card in cards"
                    :key="card.label"
                    :icon="card.icon"
                    :label="card.label"
                    :value="card.value"
                />
            </section>

            <section class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard :value="catalogStats.formations" accent="bg-slate-100 text-slate-600" icon="M12 6.2L3 11l9 4.8L21 11z" label="Formations actives"/>
                <StatCard :value="catalogStats.videos" accent="bg-blue-100 text-blue-600" icon="M15 10l4.5-2.5v9L15 14M4 6h11v12H4z" label="Vidéos"/>
                <StatCard :value="catalogStats.pdfs" accent="bg-amber-100 text-amber-600" icon="M7 3h7l5 5v13H7zM14 3v5h5" label="PDF"/>
                <StatCard :value="catalogStats.texts" accent="bg-emerald-100 text-emerald-600" icon="M4 6h16M4 12h16M4 18h10" label="Textes"/>
            </section>
        </div>
    </AdminLayout>
</template>
