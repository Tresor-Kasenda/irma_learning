<script lang="ts" setup>
import {Head} from '@inertiajs/vue3';
import {Award, BookOpen, CircleDollarSign, ClipboardCheck, FileText, Layers3, PlaySquare, UserCheck, Users} from '@lucide/vue';
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
    {label: 'Formations', value: props.stats.formations, icon: BookOpen},
    {label: 'Sections', value: props.stats.sections, icon: Layers3},
    {label: 'Examens', value: props.stats.exams, icon: ClipboardCheck},
    {label: 'Apprenants', value: props.stats.students, icon: Users},
    {label: 'Inscriptions actives', value: props.stats.activeEnrollments, icon: UserCheck},
    {label: 'Certificats délivrés', value: props.stats.certificates, icon: Award},
    {label: 'Revenus', value: formattedRevenue.value, icon: CircleDollarSign},
]);
</script>

<template>
    <Head title="Administration"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="admin-text font-medium">Tableau de bord</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Pilotage</p>
                    <h1 class="admin-heading mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Tableau de bord</h1>
                    <p class="admin-muted mt-2 text-sm">Vue d’ensemble de l’activité de la plateforme.</p>
                </div>
                <p class="border-l-2 border-[#a23362] pl-3 text-xs leading-5 text-slate-500">
                    Indicateurs mis à jour<br/>avec les données actuelles
                </p>
            </div>

            <section class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <StatCard
                    v-for="card in cards"
                    :key="card.label"
                    :icon="card.icon"
                    :label="card.label"
                    :value="card.value"
                />
            </section>

            <section class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard :value="catalogStats.formations" :icon="BookOpen" accent="bg-slate-400/10 text-slate-600 dark:text-slate-300" label="Formations actives"/>
                <StatCard :value="catalogStats.videos" :icon="PlaySquare" accent="bg-blue-400/10 text-blue-300" label="Vidéos"/>
                <StatCard :value="catalogStats.pdfs" :icon="FileText" accent="bg-amber-400/10 text-amber-300" label="PDF"/>
                <StatCard :value="catalogStats.texts" :icon="FileText" accent="bg-emerald-400/10 text-emerald-300" label="Textes"/>
            </section>
        </div>
    </AdminLayout>
</template>
