<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ContinueLearningBanner from '@/Components/Learning/ContinueLearningBanner.vue';
import FormationCard from '@/Components/Learning/FormationCard.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import LearningShell from '@/Components/Learning/LearningShell.vue';
import StatCard from '@/Components/Learning/StatCard.vue';
import type { LearningCatalogStats, LearningFormation } from '@/types/learning';

interface EnrollmentData {
    id: number;
    progress_percentage: number | string | null;
    formation: LearningFormation | null;
}

interface ChapterData {
    id: number;
    title: string;
    section: { formation: LearningFormation | null } | null;
}

interface UserProgressData {
    id: number;
    progress_percentage: number | string | null;
    trackable: ChapterData | null;
}

interface Stats {
    totalEnrollments: number;
    activeEnrollments: number;
    completedEnrollments: number;
    averageProgress: number;
    totalTimeSpent: number;
    certificatesEarned: number;
}

type TabKey = 'recent' | 'discover' | 'started' | 'completed';

const props = defineProps<{
    myEnrollments: EnrollmentData[];
    continueWatching: UserProgressData | null;
    recommendedFormations: LearningFormation[];
    stats: Stats;
    catalogStats: LearningCatalogStats;
    search: string | null;
}>();

const activeTab = ref<TabKey>('recent');
const searchValue = ref(props.search ?? '');
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

const continueFormation = computed(
    () => props.continueWatching?.trackable?.section?.formation ?? null,
);
const continueEnrollment = computed(() => {
    if (!continueFormation.value) {
        return null;
    }

    return props.myEnrollments.find(
        (enrollment) => enrollment.formation?.id === continueFormation.value?.id,
    ) ?? null;
});
const continueProgress = computed(
    () => Math.round(Number(continueEnrollment.value?.progress_percentage ?? 0)),
);

const visibleEnrollments = computed(
    () => props.myEnrollments.filter(
        (e): e is EnrollmentData & { formation: LearningFormation } => Boolean(e.formation),
    ),
);

// Tab data
const recentEnrollments = computed(() => visibleEnrollments.value);
const startedEnrollments = computed(() =>
    visibleEnrollments.value.filter((e) => {
        const p = Number(e.progress_percentage ?? 0);
        return p > 0 && p < 100;
    }),
);
const completedEnrollments = computed(() =>
    visibleEnrollments.value.filter((e) => Number(e.progress_percentage ?? 0) >= 100),
);

const tabs = computed(() => [
    { key: 'recent' as TabKey, label: 'Récent', count: recentEnrollments.value.length },
    { key: 'discover' as TabKey, label: 'Découvrir', count: props.recommendedFormations.length },
    { key: 'started' as TabKey, label: 'En cours', count: startedEnrollments.value.length },
    { key: 'completed' as TabKey, label: 'Terminé', count: completedEnrollments.value.length },
]);

const currentEnrollments = computed(() => {
    switch (activeTab.value) {
        case 'started': return startedEnrollments.value;
        case 'completed': return completedEnrollments.value;
        default: return recentEnrollments.value;
    }
});

const recommendedHighlight = computed(
    () => props.recommendedFormations.find((f) => f.is_featured) ?? props.recommendedFormations[0] ?? null,
);

watch(
    () => props.search,
    (search) => { searchValue.value = search ?? ''; },
);

function onSearchInput(event: Event): void {
    searchValue.value = (event.target as HTMLInputElement).value;
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        router.get(
            route('dashboard'),
            { q: searchValue.value || undefined },
            { preserveState: true, replace: true },
        );
    }, 350);
}

function progressValue(enrollment: EnrollmentData): number {
    return Math.round(Number(enrollment.progress_percentage ?? 0));
}
</script>

<template>
    <Head title="Tableau de bord" />

    <LearningShell active-item="dashboard" :catalog-stats="catalogStats">
        <template #breadcrumb>
            <span class="text-slate-300">Tableau de bord</span>
        </template>

        <div class="mx-auto max-w-[1540px] px-4 py-7 sm:px-6 lg:px-8 lg:py-9">

            <!-- Header -->
            <section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-white sm:text-4xl">
                        Votre Tableau de bord
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-slate-400 sm:text-base">
                        Reprenez votre progression ou découvrez votre prochaine formation.
                    </p>
                </div>

                <label class="relative block w-full sm:w-80">
                    <span class="sr-only">Rechercher une formation</span>
                    <LearningIcon
                        name="magnifying-glass"
                        class="absolute left-3 top-1/2 size-5 -translate-y-1/2 brightness-0 invert opacity-50"
                    />
                    <input
                        type="search"
                        :value="searchValue"
                        placeholder="Rechercher une formation..."
                        class="h-11 w-full border border-white/15 bg-[#0b1d31] pl-10 pr-3 text-sm text-white outline-none placeholder:text-slate-500 focus:border-[#d24376]"
                        @input="onSearchInput"
                    />
                </label>
            </section>

            <!-- Stats -->
            <section class="mt-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
                <StatCard label="Formations en cours" :value="stats.activeEnrollments" icon="book-open" tone="rose" />
                <StatCard label="Formations complétées" :value="stats.completedEnrollments" icon="academic-cap" tone="emerald" />
                <StatCard label="Certificats obtenus" :value="stats.certificatesEarned" icon="document-text" tone="amber" />
                <StatCard label="Progression moyenne" :value="`${stats.averageProgress}%`" icon="chart-bar" tone="blue" />
            </section>

            <!-- Continue watching -->
            <ContinueLearningBanner
                v-if="continueFormation"
                class="mt-6"
                :formation="continueFormation"
                :progress="continueProgress"
                :subtitle="continueWatching?.trackable?.title"
                :href="route('course.player', continueFormation.id)"
                action-label="Continuer"
                context-label="Reprenez votre apprentissage"
            />
            <ContinueLearningBanner
                v-else-if="recommendedHighlight"
                class="mt-6"
                :formation="recommendedHighlight"
                :href="route('formation.show', recommendedHighlight.slug)"
                action-label="Découvrir"
                context-label="Formation recommandée"
            />

            <!-- Tabs -->
            <section class="mt-8">
                <nav class="flex items-center gap-1 border-b border-white/10">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="-mb-px border-b-2 px-4 py-3 text-sm font-medium transition"
                        :class="
                            activeTab === tab.key
                                ? 'border-[#df3e75] text-white'
                                : 'border-transparent text-slate-400 hover:text-white'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                        <span
                            class="ml-1.5 rounded px-1.5 py-0.5 text-[11px] font-semibold"
                            :class="
                                activeTab === tab.key
                                    ? 'bg-[#df3e75]/20 text-[#ff79a5]'
                                    : 'bg-white/5 text-slate-500'
                            "
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>

                <!-- Recent / Started / Completed tabs -->
                <div v-if="activeTab !== 'discover'" class="mt-5">
                    <div
                        v-if="currentEnrollments.length > 0"
                        class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                    >
                        <FormationCard
                            v-for="enrollment in currentEnrollments"
                            :key="enrollment.id"
                            :formation="enrollment.formation"
                            :href="route('course.player', enrollment.formation.id)"
                            :progress="progressValue(enrollment)"
                            badge="En cours"
                        />
                    </div>

                    <div
                        v-else
                        class="flex min-h-56 flex-col items-center justify-center border border-dashed border-white/15 bg-[#0c1b2c] px-5 text-center"
                    >
                        <LearningIcon name="book-open" class="size-9 brightness-0 invert opacity-50" />
                        <h3 class="mt-4 text-base font-semibold text-white">
                            {{ activeTab === 'started' ? 'Aucune formation en cours' : activeTab === 'completed' ? 'Aucune formation terminée' : 'Aucune formation récente' }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-400">Commencez un parcours depuis le catalogue IRMA Learning.</p>
                        <button
                            type="button"
                            class="mt-5 inline-flex h-10 items-center bg-[#a72f5d] px-4 text-sm font-semibold text-white hover:bg-[#c43b6d]"
                            @click="activeTab = 'discover'"
                        >
                            Explorer les formations
                        </button>
                    </div>
                </div>

                <!-- Discover tab -->
                <div v-if="activeTab === 'discover'" class="mt-5">
                    <div
                        v-if="recommendedFormations.length > 0"
                        class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                    >
                        <FormationCard
                            v-for="formation in recommendedFormations"
                            :key="formation.id"
                            :formation="formation"
                            :href="route('formation.show', formation.slug)"
                        />
                    </div>

                    <div
                        v-else
                        class="flex min-h-48 items-center justify-between gap-5 border border-white/10 bg-[#101d2d] p-5"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-white">Aucune recommandation pour cette recherche</h3>
                            <p class="mt-2 text-sm text-slate-400">Consultez le catalogue complet pour élargir les résultats.</p>
                        </div>
                        <Link
                            :href="route('certifications')"
                            class="inline-flex h-10 shrink-0 items-center bg-[#a72f5d] px-4 text-sm font-semibold text-white hover:bg-[#c43b6d]"
                        >
                            Voir le catalogue
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </LearningShell>
</template>
