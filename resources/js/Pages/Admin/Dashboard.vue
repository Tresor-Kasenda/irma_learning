<script lang="ts" setup>
import {Head} from '@inertiajs/vue3';
import {Award, BookOpen, CircleDollarSign, ClipboardCheck, FileText, Layers3, PlaySquare, UserCheck, Users} from '@lucide/vue';
import {computed, ref} from 'vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import EnrollmentTrendChart from '@/Components/Admin/EnrollmentTrendChart.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {useCurrencyFormatter} from '@/composables/useCurrencyFormatter';

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
    enrollmentTrends: {months: {label: string; value: number}[]; weeks: {label: string; value: number}[]};
}>();

const trendPeriod = ref<'months' | 'weeks'>('months');
const {formatCurrency} = useCurrencyFormatter();
const activeTrend = computed(() => props.enrollmentTrends[trendPeriod.value]);

const formattedRevenue = computed(() =>
    formatCurrency(props.stats.revenue),
);

const cards = computed(() => [
    {label: 'Formations', value: props.stats.formations, icon: BookOpen, href: safeRoute('admin.formations.index')},
    {label: 'Sections', value: props.stats.sections, icon: Layers3, href: safeRoute('admin.sections.index')},
    {label: 'Examens', value: props.stats.exams, icon: ClipboardCheck, href: safeRoute('admin.exams.index')},
    {label: 'Apprenants', value: props.stats.students, icon: Users, href: safeRoute('admin.users.index', {role: 'student'})},
    {label: 'Inscriptions actives', value: props.stats.activeEnrollments, icon: UserCheck, href: safeRoute('admin.enrollments.index')},
    {label: 'Certificats délivrés', value: props.stats.certificates, icon: Award, href: safeRoute('admin.certificates.index')},
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
                    :href="card.href"
                />
            </section>

            <section class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard :value="catalogStats.formations" :icon="BookOpen" :href="safeRoute('admin.formations.index', {is_active: 1})" accent="bg-slate-400/10 text-slate-600 dark:text-slate-300" label="Formations actives"/>
                <StatCard :value="catalogStats.videos" :icon="PlaySquare" :href="safeRoute('admin.chapters.index', {content_type: 'video'})" accent="bg-blue-400/10 text-blue-300" label="Vidéos"/>
                <StatCard :value="catalogStats.pdfs" :icon="FileText" :href="safeRoute('admin.chapters.index', {content_type: 'pdf'})" accent="bg-amber-400/10 text-amber-300" label="PDF"/>
                <StatCard :value="catalogStats.texts" :icon="FileText" :href="safeRoute('admin.chapters.index', {content_type: 'text'})" accent="bg-emerald-400/10 text-emerald-300" label="Textes"/>
            </section>

            <section class="admin-panel mt-6 min-w-0 overflow-hidden border">
                <div class="admin-divider flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div><h2 class="admin-heading font-semibold">Évolution des inscriptions</h2><p class="admin-muted mt-1 text-xs">Nouveaux apprenants inscrits sur la période.</p></div>
                    <div class="admin-panel-muted inline-flex self-start border p-1 sm:self-auto"><button :class="trendPeriod === 'weeks' ? 'bg-[#a23362] text-white' : 'admin-muted'" class="h-8 px-3 text-xs font-semibold" type="button" @click="trendPeriod = 'weeks'">8 semaines</button><button :class="trendPeriod === 'months' ? 'bg-[#a23362] text-white' : 'admin-muted'" class="h-8 px-3 text-xs font-semibold" type="button" @click="trendPeriod = 'months'">12 mois</button></div>
                </div>
                <div class="min-w-0 p-5 sm:p-6"><EnrollmentTrendChart :values="activeTrend"/></div>
            </section>
        </div>
    </AdminLayout>
</template>
