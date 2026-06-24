<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import FormationCard from '@/Components/Learning/FormationCard.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import type {LearningCatalogStats, LearningFormation} from '@/types/learning';

type TabKey = 'recent' | 'discover' | 'started' | 'completed';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedFormations {
    data: LearningFormation[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface TabCounts {
    recent: number;
    discover: number;
    started: number;
    completed: number;
}

interface CatalogFilters {
    tab: TabKey;
    search: string;
    level: string;
    content: string;
    sort: string;
}

const props = defineProps<{
    formations: PaginatedFormations;
    tabCounts: TabCounts;
    catalogStats: LearningCatalogStats;
    filters: CatalogFilters;
}>();

const filtersOpen = ref(false);
const searchValue = ref(props.filters.search);
const selectedLevel = ref(props.filters.level);
const selectedContent = ref(props.filters.content);
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

const tabs = computed(() => [
    {key: 'recent' as TabKey, label: 'Récent', count: props.tabCounts.recent},
    {key: 'discover' as TabKey, label: 'Découvrir', count: props.tabCounts.discover},
    {key: 'started' as TabKey, label: 'En cours', count: props.tabCounts.started},
    {key: 'completed' as TabKey, label: 'Terminé', count: props.tabCounts.completed},
]);

const sortOptions = computed(() => {
    const commonOptions = [
        {label: 'Plus récentes', value: 'recent'},
        {label: 'Titre A–Z', value: 'title'},
        {label: 'Durée croissante', value: 'duration-asc'},
        {label: 'Durée décroissante', value: 'duration-desc'},
    ];

    return props.filters.tab === 'discover'
        ? [{label: 'Plus populaires', value: 'popular'}, ...commonOptions]
        : [{label: 'Dernière interaction', value: 'last-interacted'}, ...commonOptions];
});

const activeFilterCount = computed(
    () => Number(Boolean(props.filters.level)) + Number(Boolean(props.filters.content)),
);

const emptyState = computed(() => {
    if (props.filters.search || props.filters.level || props.filters.content) {
        return {
            title: 'Aucune formation ne correspond',
            description: 'Modifiez votre recherche ou réinitialisez les filtres pour afficher plus de résultats.',
        };
    }

    const states: Record<TabKey, {title: string; description: string}> = {
        recent: {
            title: 'Aucune formation récente',
            description: 'Vos formations accessibles apparaîtront ici dès votre première inscription.',
        },
        discover: {
            title: 'Tout le catalogue est déjà à vous',
            description: 'Aucune nouvelle formation n’est disponible pour le moment.',
        },
        started: {
            title: 'Aucune formation en cours',
            description: 'Commencez une formation pour suivre votre progression depuis cet onglet.',
        },
        completed: {
            title: 'Aucune formation terminée',
            description: 'Les formations achevées seront regroupées ici.',
        },
    };

    return states[props.filters.tab];
});

watch(
    () => props.filters,
    (filters) => {
        searchValue.value = filters.search;
        selectedLevel.value = filters.level;
        selectedContent.value = filters.content;
    },
    {deep: true},
);

function cleanParams(params: Record<string, string>): Record<string, string> {
    return Object.fromEntries(Object.entries(params).filter(([, value]) => value !== ''));
}

function navigate(overrides: Partial<CatalogFilters>, preserveScroll = true): void {
    router.get(
        route('student.learnings'),
        cleanParams({
            tab: props.filters.tab,
            search: props.filters.search,
            level: props.filters.level,
            content: props.filters.content,
            sort: props.filters.sort,
            ...overrides,
        }),
        {
            preserveState: true,
            preserveScroll,
            replace: true,
        },
    );
}

function selectTab(tab: TabKey): void {
    const sort = tab === 'discover' ? 'popular' : 'last-interacted';
    navigate({tab, sort}, false);
}

function onSearchInput(event: Event): void {
    searchValue.value = (event.target as HTMLInputElement).value;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => navigate({search: searchValue.value}), 350);
}

function onSortChange(event: Event): void {
    navigate({sort: (event.target as HTMLSelectElement).value});
}

function applyFilters(): void {
    filtersOpen.value = false;
    navigate({level: selectedLevel.value, content: selectedContent.value});
}

function resetFilters(): void {
    selectedLevel.value = '';
    selectedContent.value = '';
    filtersOpen.value = false;
    navigate({level: '', content: ''});
}

function resetCatalog(): void {
    searchValue.value = '';
    selectedLevel.value = '';
    selectedContent.value = '';
    filtersOpen.value = false;
    navigate({search: '', level: '', content: ''});
}

function enrollmentProgress(formation: LearningFormation): number | null {
    const enrollment = formation.enrollments?.[0];

    if (!enrollment) {
        return null;
    }

    return Math.round(Number(enrollment.progress_percentage ?? 0));
}

function formationHref(formation: LearningFormation): string {
    return formation.enrollments?.length
        ? route('course.player', formation.id)
        : route('formation.show', formation.slug);
}
</script>

<template>
    <Head title="Mes formations"/>

    <LearningLayout active-item="formations" :catalog-stats="catalogStats">
        <template #breadcrumb>
            <span class="text-slate-300">Formations</span>
        </template>

        <div class="mx-auto max-w-[1540px] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <section class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-400">
                    IRMA Learning
                </p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Vos formations
                </h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-400 sm:text-base">
                    Reprenez votre apprentissage, explorez le catalogue et retrouvez toutes vos formations au même endroit.
                </p>
            </section>

            <section class="mt-9">
                <nav
                    aria-label="Catégories de formations"
                    class="flex gap-1 overflow-x-auto border-b border-white/10"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        :aria-current="filters.tab === tab.key ? 'page' : undefined"
                        :class="filters.tab === tab.key
                            ? 'border-sky-400 text-white'
                            : 'border-transparent text-slate-400 hover:text-white'"
                        class="-mb-px flex shrink-0 items-center gap-2 border-b-2 px-3 py-3 text-sm font-medium transition sm:px-4"
                        type="button"
                        @click="selectTab(tab.key)"
                    >
                        {{ tab.label }}
                        <span
                            :class="filters.tab === tab.key
                                ? 'bg-sky-400/15 text-sky-300'
                                : 'bg-white/5 text-slate-500'"
                            class="rounded px-1.5 py-0.5 text-[10px] font-bold"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>

                <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <button
                            :aria-expanded="filtersOpen"
                            class="relative grid size-11 shrink-0 place-items-center border border-white/10 bg-[#16253a] text-sky-300 transition hover:border-sky-400/50 hover:bg-[#1b2d45]"
                            type="button"
                            @click="filtersOpen = !filtersOpen"
                        >
                            <LearningIcon class="size-5 brightness-0 invert opacity-80" name="adjustments-horizontal"/>
                            <span
                                v-if="activeFilterCount > 0"
                                class="absolute -right-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-[#df3e75] text-[10px] font-bold text-white"
                            >
                                {{ activeFilterCount }}
                            </span>
                        </button>

                        <label class="relative block min-w-0 max-w-xl flex-1">
                            <span class="sr-only">Rechercher une formation</span>
                            <LearningIcon
                                class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 brightness-0 invert opacity-40"
                                name="magnifying-glass"
                            />
                            <input
                                :value="searchValue"
                                class="h-11 w-full border border-white/10 bg-[#0c1a2a] pl-10 pr-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-sky-400/60"
                                placeholder="Filtrer les formations…"
                                type="search"
                                @input="onSearchInput"
                            />
                        </label>
                    </div>

                    <div class="flex items-center justify-between gap-3 lg:justify-end">
                        <p class="text-xs text-slate-500">
                            <span class="font-semibold text-slate-300">{{ formations.total }}</span>
                            {{ formations.total > 1 ? 'résultats' : 'résultat' }}
                        </p>
                        <label class="relative">
                            <span class="sr-only">Trier les formations</span>
                            <select
                                :value="filters.sort"
                                class="h-11 appearance-none border border-white/10 bg-[#0c1a2a] pl-3 pr-10 text-sm text-slate-300 outline-none focus:border-sky-400/60"
                                @change="onSortChange"
                            >
                                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <LearningIcon
                                class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 brightness-0 invert opacity-50"
                                name="chevron-down"
                            />
                        </label>
                    </div>
                </div>

                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="-translate-y-1 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="-translate-y-1 opacity-0"
                >
                    <div
                        v-if="filtersOpen"
                        class="mt-3 border border-white/10 bg-[#0d1d30] p-4 sm:p-5"
                    >
                        <div class="grid gap-5 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                            <fieldset>
                                <legend class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Niveau
                                </legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button
                                        v-for="option in [
                                            {label: 'Débutant', value: 'beginner'},
                                            {label: 'Intermédiaire', value: 'intermediate'},
                                            {label: 'Avancé', value: 'advanced'},
                                        ]"
                                        :key="option.value"
                                        :class="selectedLevel === option.value
                                            ? 'border-sky-400/60 bg-sky-400/10 text-sky-300'
                                            : 'border-white/10 text-slate-400 hover:border-white/20 hover:text-white'"
                                        class="h-9 border px-3 text-xs transition"
                                        type="button"
                                        @click="selectedLevel = selectedLevel === option.value ? '' : option.value"
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Format
                                </legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button
                                        v-for="option in [
                                            {label: 'Vidéo', value: 'video'},
                                            {label: 'PDF', value: 'pdf'},
                                            {label: 'Texte', value: 'text'},
                                        ]"
                                        :key="option.value"
                                        :class="selectedContent === option.value
                                            ? 'border-sky-400/60 bg-sky-400/10 text-sky-300'
                                            : 'border-white/10 text-slate-400 hover:border-white/20 hover:text-white'"
                                        class="h-9 border px-3 text-xs transition"
                                        type="button"
                                        @click="selectedContent = selectedContent === option.value ? '' : option.value"
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </fieldset>

                            <div class="flex gap-2">
                                <button
                                    class="h-10 border border-white/10 px-4 text-xs font-semibold text-slate-400 transition hover:text-white"
                                    type="button"
                                    @click="resetFilters"
                                >
                                    Réinitialiser
                                </button>
                                <button
                                    class="h-10 bg-sky-500 px-5 text-xs font-semibold text-white transition hover:bg-sky-400"
                                    type="button"
                                    @click="applyFilters"
                                >
                                    Appliquer
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </section>

            <section class="mt-5">
                <div
                    v-if="formations.data.length > 0"
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <FormationCard
                        v-for="formation in formations.data"
                        :key="formation.id"
                        :formation="formation"
                        :href="formationHref(formation)"
                        :progress="enrollmentProgress(formation)"
                        variant="scrimba"
                    />
                </div>

                <div
                    v-else
                    class="flex min-h-72 flex-col items-center justify-center border border-dashed border-white/15 bg-[#0c1b2c] px-5 text-center"
                >
                    <LearningIcon class="size-10 brightness-0 invert opacity-30" name="book-open"/>
                    <h2 class="mt-5 text-lg font-semibold text-white">{{ emptyState.title }}</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-400">
                        {{ emptyState.description }}
                    </p>
                    <button
                        v-if="filters.search || filters.level || filters.content"
                        class="mt-5 h-10 bg-sky-500 px-4 text-sm font-semibold text-white transition hover:bg-sky-400"
                        type="button"
                        @click="resetCatalog"
                    >
                        Réinitialiser la recherche
                    </button>
                    <button
                        v-else-if="filters.tab !== 'discover'"
                        class="mt-5 h-10 bg-sky-500 px-4 text-sm font-semibold text-white transition hover:bg-sky-400"
                        type="button"
                        @click="selectTab('discover')"
                    >
                        Découvrir les formations
                    </button>
                </div>

                <div
                    v-if="formations.last_page > 1"
                    class="mt-7 flex flex-col gap-4 border-t border-white/10 pt-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-xs text-slate-500">
                        Affichage de {{ formations.from }} à {{ formations.to }} sur {{ formations.total }}
                    </p>
                    <nav aria-label="Pagination" class="flex flex-wrap gap-2">
                        <template v-for="(link, index) in formations.links" :key="index">
                            <Link
                                v-if="link.url"
                                :class="link.active
                                    ? 'border-sky-400 bg-sky-400/10 text-sky-300'
                                    : 'border-white/10 text-slate-400 hover:border-white/20 hover:text-white'"
                                :href="link.url"
                                class="grid min-h-9 min-w-9 place-items-center border px-3 text-xs transition"
                                preserve-scroll
                                preserve-state
                                replace
                            >
                                <span v-html="link.label"/>
                            </Link>
                            <span
                                v-else
                                class="grid min-h-9 min-w-9 place-items-center border border-white/5 px-3 text-xs text-slate-700"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </section>
        </div>
    </LearningLayout>
</template>
