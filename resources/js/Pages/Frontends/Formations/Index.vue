<script lang="ts" setup>
import PublicLayout from "@/Layouts/PublicLayout.vue";
import type {LearningFormation} from "@/types/learning";
import {computed, ref, watch} from "vue";
import {Head, Link, router} from "@inertiajs/vue3";
import {useCurrencyFormatter} from '@/composables/useCurrencyFormatter';

interface LearningCatalogStats {
    videos: number;
    pdfs: number;
    texts: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: LearningFormation[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface CatalogFilters {
    search: string;
    category: string;
    level: string;
    content: string;
    sort: string;
}

const {formatCurrency} = useCurrencyFormatter();

interface ContinueLearning {
    id: number;
    progress_percentage: number | string | null;
    formation: LearningFormation | null;
}

const props = defineProps<{
    formations: PaginatedData;
    catalogStats: LearningCatalogStats;
    continueLearning: ContinueLearning | null;
    filters: CatalogFilters;
}>();

const filtersOpen = ref(false);
const searchValue = ref(props.filters.search);
const selectedLevel = ref(props.filters.level);
const selectedContent = ref(props.filters.content);
const openAccordion = ref<number | null>(null);
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

const sortOptions = [
    {label: 'Populaires', value: 'popular'},
    {label: 'Plus récentes', value: 'recent'},
    {label: 'Durée croissante', value: 'duration-asc'},
    {label: 'Durée décroissante', value: 'duration-desc'},
    {label: 'Prix croissant', value: 'price-asc'},
];

const activeFilterCount = computed(
    () => Number(Boolean(props.filters.level)) + Number(Boolean(props.filters.content)),
);
const heroFormations = computed(() => props.formations.data.slice(0, 4));

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
    return Object.fromEntries(
        Object.entries(params).filter(([, value]) => value !== '' && value !== 'all'),
    );
}

function navigate(overrides: Partial<CatalogFilters>): void {
    router.get(
        route('certifications'),
        cleanParams({
            search: props.filters.search,
            category: props.filters.category,
            level: props.filters.level,
            content: props.filters.content,
            sort: props.filters.sort,
            ...overrides,
        }),
        {preserveState: true, preserveScroll: true, replace: true},
    );
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
    navigate({search: '', category: 'all', level: '', content: ''});
}

function toggleAccordion(index: number): void {
    openAccordion.value = openAccordion.value === index ? null : index;
}

function parseTags(tags: unknown): string[] {
    if (!tags) return [];
    if (Array.isArray(tags)) return (tags as unknown[]).map(String).filter(Boolean);
    if (typeof tags === 'string') {
        try {
            const parsed: unknown = JSON.parse(tags);
            return Array.isArray(parsed) ? (parsed as unknown[]).map(String).filter(Boolean) : [tags];
        } catch {
            return tags.split(',').map((t) => t.trim()).filter(Boolean);
        }
    }
    return [];
}

function formatPrice(price: unknown): string {
    const num = Number(price ?? 0);
    if (!num) return 'Gratuit';
    return formatCurrency(num, 2);
}

function imageUrl(image: unknown): string | null {
    if (!image || typeof image !== 'string') return null;
    return `/storage/${image}`;
}
</script>

<template>
    <Head title="Nos formations"/>
    <PublicLayout
        title="Nos formations"
        meta-description="Découvrez toutes nos formations et certifications professionnelles en BTP, artisanat et gestion de projet."
    >
        <section class="pt-32">
            <div class="mx-auto flex w-full max-w-7xl flex-col items-start gap-16 px-5 py-20 sm:px-10 lg:flex-row">
                <div
                    class="flex w-full flex-col items-center space-y-7 text-center lg:w-1/2 lg:items-start lg:py-12 lg:text-left xl:py-20">
                    <div
                        class="flex items-center divide-x divide-gray-300 overflow-hidden text-sm text-gray-500 *:px-4 first:*:pl-0 last:*:pr-0">
                        <Link aria-label="Lien vers la page principale" href="/">
                            <svg aria-hidden="true" class="size-5 text-gray-600" fill="none" stroke="currentColor"
                                 stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </Link>
                        <div class="text-irma-primary">Certification</div>
                    </div>

                    <h1 class="text-3xl font-medium text-gray-900 md:text-4xl">
                        Certification Professionnelle
                    </h1>
                    <p class="mx-auto max-w-lg text-4xl/tight font-medium italic text-gray-700">
                        Un gage de crédibilité qui ouvre à de nouvelles opportunités
                    </p>

                    <a
                        class="mt-8 inline-flex items-center gap-2 rounded-lg bg-irma-primary px-6 py-3 font-medium text-white transition-opacity hover:opacity-90"
                        href="#certifications"
                    >
                        Decouvrir plus
                        <svg class="size-4 transition duration-500 group-hover:rotate-[360deg]" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
                <div
                    class="flex max-w-4xl w-full lg:flex-1 relative before:absolute before:inset-y-3 md:before:inset-y-10 before:border-y before:border-gray-200 after:border-gray-200 before:w-full after:absolute after:inset-x-3 md:after:inset-x-10 after:border-x after:h-full">
                    <div class="relative flex w-full items-center justify-center px-4 py-6">
                        <div v-if="heroFormations.length > 0"
                             class="relative z-10 flex w-full flex-col bg-white/80 backdrop-blur-lg border border-gray-200/30 rounded-lg shadow-sm p-6">
                            <span class="mb-4 font-semibold text-gray-900">Formation en cours</span>
                            <ul class="flex flex-col gap-4 divide-y divide-gray-100 *:py-2 first:*:pt-0 last:*:pb-0 mb-5">
                                <li
                                    v-for="formation in heroFormations"
                                    :key="formation.id"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex shrink-0 rounded bg-primary-50 p-2 text-primary-600">
                                        <svg class="size-7" fill="currentColor" height="32"
                                             viewBox="0 0 256 256" width="32" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M128,136a8,8,0,0,1-8,8H72a8,8,0,0,1,0-16h48A8,8,0,0,1,128,136Zm-8-40H72a8,8,0,0,0,0,16h48a8,8,0,0,0,0-16Zm112,65.47V224A8,8,0,0,1,220,231l-24-13.74L172,231A8,8,0,0,1,160,224V200H40a16,16,0,0,1-16-16V56A16,16,0,0,1,40,40H216a16,16,0,0,1,16,16V86.53a51.88,51.88,0,0,1,0,74.94ZM160,184V161.47A52,52,0,0,1,216,76V56H40V184Zm56-12a51.88,51.88,0,0,1-40,0v38.22l16-9.16a8,8,0,0,1,7.94,0l16,9.16Zm16-48a36,36,0,1,0-36,36A36,36,0,0,0,232,124Z"/>
                                        </svg>
                                    </div>

                                    <div class="flex flex-1 flex-col gap-1">
                                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                                            <div class="mb-2 sm:mb-0 sm:mr-4">
                                                <h3 class="line-clamp-2 font-semibold text-gray-900">{{
                                                        formation.title
                                                    }}</h3>
                                                <div class="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
                                                    <svg class="size-3.5" fill="currentColor" height="32"
                                                         viewBox="0 0 256 256" width="32" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M232,136.66A104.12,104.12,0,1,1,119.34,24,8,8,0,0,1,120.66,40,88.12,88.12,0,1,0,216,135.34,8,8,0,0,1,232,136.66ZM120,72v56a8,8,0,0,0,8,8h56a8,8,0,0,0,0-16H136V72a8,8,0,0,0-16,0Zm40-24a12,12,0,1,0-12-12A12,12,0,0,0,160,48Zm36,24a12,12,0,1,0-12-12A12,12,0,0,0,196,72Zm24,36a12,12,0,1,0-12-12A12,12,0,0,0,220,108Z"/>
                                                    </svg>
                                                    <p class="capitalize">{{ formation.difficulty_level }}</p>
                                                </div>
                                            </div>
                                            <div class="flex shrink-0">
                                                <Link
                                                    :href="route('formation.show', formation.slug)"
                                                    class="inline-flex items-center justify-center rounded-lg bg-irma-primary px-3 py-1.5 text-sm text-white transition-opacity hover:opacity-90"
                                                >
                                                    Details
                                                </Link>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                            <div class="flex items-center gap-1">
                                                <svg class="size-3.5" fill="currentColor" height="32"
                                                     viewBox="0 0 256 256" width="32" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34Z"/>
                                                </svg>
                                                {{ formation.duration_hours }} H
                                            </div>
                                            <div class="font-medium text-irma-primary">
                                                {{ formatPrice(formation.price) }}
                                            </div>
                                        </div>

                                        <div v-if="parseTags(formation.tags).length > 0" class="flex flex-wrap gap-1.5">
                                            <span
                                                v-for="tag in parseTags(formation.tags).slice(0, 3)"
                                                :key="tag"
                                                class="rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700"
                                            >
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Empty state -->
                        <div v-else
                             class="relative z-10 flex w-full flex-col items-center bg-white/80 backdrop-blur-lg border border-gray-200/30 rounded-lg shadow-sm px-6 py-12">
                            <h2 class="pb-2 text-center text-xl font-semibold text-gray-900">
                                Aucune certification trouvée
                            </h2>
                            <p class="mx-auto max-w-sm pb-4 text-center text-base text-gray-600">
                                Aucune certification disponible pour le moment. Veuillez vérifier plus tard.
                            </p>
                            <Link
                                :href="route('home-page')"
                                class="inline-flex items-center justify-center rounded-lg bg-irma-primary px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90"
                            >
                                Revenir a l'Accueil
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="px-5 py-20 sm:px-10">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-16">
                <h2 class="relative mx-auto max-w-xl text-center text-3xl font-medium capitalize text-gray-900 md:text-4xl">
                    Informations importantes
                </h2>

                <div class="grid w-full gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Item 1 -->
                    <div
                        :class="openAccordion === 0 ? 'border-primary-400 bg-primary-50/30' : 'border-gray-200 bg-white/50 backdrop-blur-sm'"
                        class="h-max rounded-lg border transition duration-500"
                    >
                        <button
                            class="inline-flex w-full items-center justify-between rounded-lg p-4 text-left transition hover:bg-gray-50"
                            @click="toggleAccordion(0)"
                        >
                            <h5 class="font-medium text-primary-700">Validation des Compétences</h5>
                            <div :class="openAccordion === 0 ? 'rotate-45' : ''"
                                 class="relative flex size-3 duration-300 ease-linear">
                                <span class="absolute top-1/2 flex h-0.5 w-full -translate-y-1/2 bg-gray-500"/>
                                <span class="absolute left-1/2 flex h-full w-0.5 -translate-x-1/2 bg-gray-500"/>
                            </div>
                        </button>
                        <div v-if="openAccordion === 0" class="border-t border-gray-100 p-4">
                            <p class="text-gray-600">
                                Les tests de certification professionnelle permettent de valider le niveau de
                                compétence, de
                                connaissance et d'aptitude des participants aux MasterClasses. Ils constituent une
                                preuve
                                d'autorité et de crédibilité professionnelles pour les certifiés.
                            </p>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div
                        :class="openAccordion === 1 ? 'border-primary-400 bg-primary-50/30' : 'border-gray-200 bg-white/50 backdrop-blur-sm'"
                        class="h-max rounded-lg border transition duration-500"
                    >
                        <button
                            class="inline-flex w-full items-center justify-between rounded-lg p-4 text-left transition hover:bg-gray-50"
                            @click="toggleAccordion(1)"
                        >
                            <h5 class="font-medium text-primary-700">Modalités d'Accès et de Passation</h5>
                            <div :class="openAccordion === 1 ? 'rotate-45' : ''"
                                 class="relative flex size-3 duration-300 ease-linear">
                                <span class="absolute top-1/2 flex h-0.5 w-full -translate-y-1/2 bg-gray-500"/>
                                <span class="absolute left-1/2 flex h-full w-0.5 -translate-x-1/2 bg-gray-500"/>
                            </div>
                        </button>
                        <div v-if="openAccordion === 1" class="border-t border-gray-100 p-4">
                            <p class="text-gray-600">Les tests de certification sont :</p>
                            <ul class="mt-2 list-outside list-disc space-y-1 pl-5 text-sm text-gray-600">
                                <li>
                                    Entièrement automatisés et accessibles en ligne immédiatement après la fin de la
                                    MasterClass.
                                </li>
                                <li>
                                    Disponibles pour une durée de 30 jours afin de permettre aux participants de
                                    finaliser
                                    et soumettre leur évaluation via notre plateforme dédiée.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div
                        :class="openAccordion === 2 ? 'border-primary-400 bg-primary-50/30' : 'border-gray-200 bg-white/50 backdrop-blur-sm'"
                        class="h-max rounded-lg border transition duration-500"
                    >
                        <button
                            class="inline-flex w-full items-center justify-between rounded-lg p-4 text-left transition hover:bg-gray-50"
                            @click="toggleAccordion(2)"
                        >
                            <h5 class="font-medium text-primary-700">Publication des Résultats et Délivrance des
                                Certifications</h5>
                            <div :class="openAccordion === 2 ? 'rotate-45' : ''"
                                 class="relative flex size-3 duration-300 ease-linear">
                                <span class="absolute top-1/2 flex h-0.5 w-full -translate-y-1/2 bg-gray-500"/>
                                <span class="absolute left-1/2 flex h-full w-0.5 -translate-x-1/2 bg-gray-500"/>
                            </div>
                        </button>
                        <div v-if="openAccordion === 2" class="border-t border-gray-100 p-4">
                            <ul class="list-outside list-disc space-y-2 pl-5 text-sm text-gray-600">
                                <li>
                                    Les résultats sont automatiquement générés et communiqués dans un délai de 7 jours
                                    après
                                    la soumission.
                                </li>
                                <li>
                                    Les certifications attestant des compétences acquises sont disponibles sous deux
                                    formats :
                                    <ul class="mt-2 list-outside list-disc space-y-2 pl-5 text-sm text-gray-600">
                                        <li>
                                            <span class="font-medium text-gray-800">Téléchargement instantané</span>
                                            via la plateforme en ligne dès la publication des résultats.
                                        </li>
                                        <li>
                                            <span class="font-medium text-gray-800">Envoi physique</span>
                                            des certifications imprimées sous 5 jours ouvrables après publication des
                                            résultats.
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="certifications" class="scroll-mt-20 bg-gray-50 pb-20">
            <h2 class="relative mx-auto mb-16 max-w-xl px-5 pt-20 text-center text-3xl font-medium capitalize text-gray-900 sm:px-10 md:text-4xl">
                Nos certifications
            </h2>
            <div
                class="sticky top-2 z-20 mx-auto mb-8 flex h-14 w-full max-w-7xl items-center justify-between rounded-md border border-gray-200 bg-white/90 px-5 shadow-sm backdrop-blur-sm sm:px-10">
                <div class="hidden text-sm text-gray-500 sm:block">
                    <span class="font-semibold text-gray-900">{{ formations.total }}</span>
                    {{ formations.total > 1 ? 'certifications disponibles' : 'certification disponible' }}
                </div>

                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <!-- Search -->
                    <div class="relative min-w-0 flex-1 sm:w-72 sm:flex-none">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" fill="currentColor" height="32" viewBox="0 0 256 256"
                             width="32"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"/>
                        </svg>
                        <input
                            :value="searchValue"
                            class="h-9 w-full rounded-md border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-irma-primary focus:ring-1 focus:ring-irma-primary/20"
                            placeholder="Rechercher"
                            type="text"
                            @input="onSearchInput"
                        />
                    </div>

                    <!-- Sort -->
                    <div class="relative hidden sm:block">
                        <select
                            :value="filters.sort"
                            class="h-9 appearance-none rounded-md border border-gray-200 bg-white pl-3 pr-8 text-sm text-gray-700 outline-none focus:border-irma-primary"
                            @change="onSortChange"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <!-- Filter -->
                    <div class="relative">
                        <button
                            :aria-expanded="filtersOpen"
                            class="relative grid size-9 place-items-center rounded-md border border-gray-200 bg-white text-gray-600 transition hover:border-irma-primary hover:text-irma-primary"
                            type="button"
                            @click="filtersOpen = !filtersOpen"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span
                                v-if="activeFilterCount > 0"
                                class="absolute -right-1 -top-1 grid size-4 place-items-center rounded-full bg-irma-primary text-[10px] font-bold text-white"
                            >
                                {{ activeFilterCount }}
                            </span>
                        </button>

                        <div
                            v-if="filtersOpen"
                            class="absolute right-0 top-[2.75rem] z-30 w-64 rounded-lg border border-gray-200 bg-white p-4 shadow-lg"
                        >
                            <div class="flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-gray-900">Filtres</h2>
                                <button class="text-xs text-primary-600 hover:text-primary-800" type="button"
                                        @click="resetFilters">
                                    Réinitialiser
                                </button>
                            </div>
                            <fieldset class="mt-4">
                                <legend class="text-xs font-semibold uppercase text-gray-500">Niveau</legend>
                                <div class="mt-2 grid grid-cols-3 gap-2">
                                    <button
                                        v-for="option in [
                                            { label: 'Débutant', value: 'beginner' },
                                            { label: 'Inter.', value: 'intermediate' },
                                            { label: 'Avancé', value: 'advanced' },
                                        ]"
                                        :key="option.value"
                                        :class="selectedLevel === option.value
                                            ? 'border-irma-primary bg-irma-primary/10 text-irma-primary'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="h-8 rounded border px-2 text-xs transition"
                                        type="button"
                                        @click="selectedLevel = selectedLevel === option.value ? '' : option.value"
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </fieldset>
                            <fieldset class="mt-4">
                                <legend class="text-xs font-semibold uppercase text-gray-500">Format</legend>
                                <div class="mt-2 grid grid-cols-3 gap-2">
                                    <button
                                        v-for="option in [
                                            { label: 'Vidéo', value: 'video' },
                                            { label: 'PDF', value: 'pdf' },
                                            { label: 'Texte', value: 'text' },
                                        ]"
                                        :key="option.value"
                                        :class="selectedContent === option.value
                                            ? 'border-irma-primary bg-irma-primary/10 text-irma-primary'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="h-8 rounded border px-2 text-xs transition"
                                        type="button"
                                        @click="selectedContent = selectedContent === option.value ? '' : option.value"
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </fieldset>
                            <button
                                class="mt-4 h-9 w-full rounded-lg bg-irma-primary text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                type="button"
                                @click="applyFilters"
                            >
                                Appliquer les filtres
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mx-auto max-w-7xl px-5 sm:px-10">
                <!-- Formation grid -->
                <div class="mt-8">
                    <div v-if="formations.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="formation in formations.data"
                            :key="formation.id"
                            class="group flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                        >
                            <!-- Image -->
                            <div class="relative aspect-video bg-gray-100">
                                <img
                                    v-if="imageUrl(formation.image)"
                                    :alt="formation.title"
                                    :src="imageUrl(formation.image)!"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-if="formation.difficulty_level"
                                    class="absolute right-2 top-2 rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-primary-700 backdrop-blur-sm"
                                >
                                    {{ formation.difficulty_level }}
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex flex-1 flex-col p-6">
                                <h2 class="line-clamp-2 text-lg font-semibold text-gray-900 transition group-hover:text-primary-600">
                                    {{ formation.title }}
                                </h2>

                                <div v-if="parseTags(formation.tags).length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="tag in parseTags(formation.tags)"
                                        :key="tag"
                                        class="flex items-center rounded-full bg-primary-50 px-3 py-1.5 text-xs font-medium text-primary-800"
                                    >
                                        <svg class="mr-1 size-3" fill="currentColor" viewBox="0 0 20 20"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path clip-rule="evenodd"
                                                  d="M5.5 3A2.5 2.5 0 0 0 3 5.5v2.879a2.5 2.5 0 0 0 .732 1.767l6.5 6.5a2.5 2.5 0 0 0 3.536 0l2.878-2.878a2.5 2.5 0 0 0 0-3.536l-6.5-6.5A2.5 2.5 0 0 0 8.38 3H5.5ZM6 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                                                  fill-rule="evenodd"/>
                                        </svg>
                                        {{ tag }}
                                    </span>
                                </div>

                                <div class="mt-auto flex flex-col justify-end pb-5 pt-3">
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <div class="flex items-center gap-1">
                                            <svg class="size-4" fill="currentColor" height="32"
                                                 viewBox="0 0 256 256" width="32" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z"/>
                                            </svg>
                                            {{ formation.duration_hours }} H
                                        </div>
                                        <div class="text-xl font-semibold text-gray-900">
                                            {{ formatPrice(formation.price) }}
                                        </div>
                                    </div>
                                </div>

                                <Link
                                    :href="route('formation.show', formation.slug)"
                                    class="mt-3 flex w-full justify-center rounded-lg bg-irma-primary px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90"
                                >
                                    Suivre la formation
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else
                        class="flex min-h-80 flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white px-5 text-center"
                    >
                        <svg class="size-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 10.607Z" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                        <h2 class="mt-4 text-lg font-semibold text-gray-900">Aucune certification trouvée</h2>
                        <p class="mt-2 max-w-md text-sm leading-6 text-gray-600">
                            Aucune certification disponible pour le moment. Veuillez vérifier plus tard.
                        </p>
                        <button
                            class="mt-5 rounded-lg bg-irma-primary px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90"
                            type="button"
                            @click="resetCatalog"
                        >
                            Réinitialiser la recherche
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="formations.last_page > 1"
                        class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-5"
                    >
                        <p class="text-xs text-gray-500">
                            Affichage de {{ formations.from }} à {{ formations.to }} sur {{ formations.total }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <template v-for="(link, index) in formations.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :class="link.active
                                        ? 'border-irma-primary bg-irma-primary text-white'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                                    :href="link.url"
                                    class="grid min-h-9 min-w-9 place-items-center rounded-md border px-3 text-xs transition"
                                    preserve-scroll
                                    preserve-state
                                >
                                    <span v-html="link.label"/>
                                </Link>
                                <span
                                    v-else
                                    class="grid min-h-9 min-w-9 place-items-center rounded-md border border-gray-100 px-3 text-xs text-gray-300"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
