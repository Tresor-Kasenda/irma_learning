<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface FormationData {
    id: number;
    title: string;
    slug: string;
    image: string | null;
    difficulty_level: string;
    duration_hours: number | null;
    price: number | null;
    is_active: boolean;
    is_featured: boolean;
    short_description: string | null;
    description: string | null;
    creator?: { name: string } | null;
}

interface EnrollmentData {
    id: number;
    progress_percentage: number | null;
    formation: FormationData | null;
}

interface ChapterData {
    id: number;
    title: string;
    section: { formation: FormationData | null } | null;
}

interface UserProgressData {
    id: number;
    progress_percentage: number | null;
    trackable: ChapterData | null;
}

interface StatItem {
    totalEnrollments: number;
    activeEnrollments: number;
    completedEnrollments: number;
    averageProgress: number;
    totalTimeSpent: number;
    certificatesEarned: number;
}

interface CategoryItem {
    name: string;
    count: number;
}

const props = defineProps<{
    myEnrollments: EnrollmentData[];
    continueWatching: UserProgressData | null;
    recommendedFormations: FormationData[];
    stats: StatItem;
    popularCategories: CategoryItem[];
    search: string | null;
}>();

let debounceTimer: ReturnType<typeof setTimeout> | undefined;

function onSearchInput(e: Event) {
    const target = e.target as HTMLInputElement;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route('dashboard'),
            { q: target.value || undefined },
            { preserveState: true, replace: true },
        );
    }, 300);
}

function formatHours(hours: number | null): string {
    return hours ? `${hours}h` : '';
}

function formatPrice(price: number | null): string {
    if (price === null || price <= 0) return 'Gratuit';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
}

function getDifficultyLabel(level: string | null): string {
    if (!level) return 'Débutant';
    const labels: Record<string, string> = {
        beginner: 'Débutant',
        intermediate: 'Intermédiaire',
        advanced: 'Avancé',
    };
    return labels[level] ?? level;
}

function getCreatorName(formation: FormationData | null): string {
    if (!formation) return 'IRMA Learning';
    return (formation as any).creator?.name ?? 'IRMA Learning';
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        Bonjour, {{ $page.props.auth.user.name }} 👋
                    </h2>
                    <p class="text-gray-600">Prêt à poursuivre votre apprentissage aujourd'hui ?</p>
                </div>

                <div v-if="continueWatching?.trackable?.section?.formation" class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Continuer à apprendre</h3>
                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl overflow-hidden shadow-lg">
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-2/5 relative h-64 md:h-auto">
                                <img v-if="continueWatching.trackable.section.formation.image"
                                     :src="'/storage/' + continueWatching.trackable.section.formation.image"
                                     :alt="continueWatching.trackable.section.formation.title"
                                     class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-primary-800 flex items-center justify-center">
                                    <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="md:w-3/5 p-8 text-white flex flex-col justify-center">
                                <div class="mb-2">
                                    <span class="text-primary-200 text-sm font-medium">Dernier visionnement</span>
                                </div>
                                <h4 class="text-2xl font-bold mb-2">{{ continueWatching.trackable.section.formation.title }}</h4>
                                <p class="text-primary-100 mb-4">{{ continueWatching.trackable.title }}</p>
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm mb-2">
                                        <span>{{ Math.round(continueWatching.progress_percentage ?? 0) }}% terminé</span>
                                    </div>
                                    <div class="w-full bg-primary-800 rounded-full h-2">
                                        <div class="bg-white h-full rounded-full" :style="{ width: (continueWatching.progress_percentage ?? 0) + '%' }" />
                                    </div>
                                </div>
                                <div>
                                    <Link :href="route('course.player', continueWatching.trackable.section.formation.id)"
                                          class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold px-6 py-3 rounded-lg hover:bg-primary-50 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Continuer
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="myEnrollments.length > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Mes formations</h3>
                        <Link :href="route('formations-lists')" class="text-primary-600 hover:text-primary-700 font-semibold text-sm">
                            Voir tout
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="enrollment in myEnrollments.slice(0, 4)" :key="enrollment.id"
                             class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden group cursor-pointer"
                        >
                            <div class="relative h-32 bg-gray-200">
                                <img v-if="enrollment.formation?.image"
                                     :src="'/storage/' + enrollment.formation.image"
                                     :alt="enrollment.formation.title"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div v-else class="flex items-center justify-center h-full bg-gradient-to-br from-primary-500 to-primary-600">
                                    <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="p-4">
                                <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 text-sm group-hover:text-primary-600 transition-colors">
                                    {{ enrollment.formation?.title }}
                                </h4>
                                <div class="text-xs text-gray-500 mb-3">
                                    Par {{ getCreatorName(enrollment.formation) }}
                                </div>

                                <div class="mb-3">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-primary-600 h-full rounded-full" :style="{ width: (enrollment.progress_percentage ?? 0) + '%' }" />
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 font-medium">
                                    {{ Math.round(enrollment.progress_percentage ?? 0) }}% terminé
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Vos statistiques</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-3xl font-bold text-gray-900">{{ stats.activeEnrollments }}</div>
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">Formations en cours</div>
                        </div>

                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-3xl font-bold text-gray-900">{{ stats.completedEnrollments }}</div>
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">Formations complétées</div>
                        </div>

                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-3xl font-bold text-gray-900">{{ stats.certificatesEarned }}</div>
                                <div class="p-3 bg-yellow-100 rounded-lg">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">Certificats obtenus</div>
                        </div>

                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-3xl font-bold text-gray-900">{{ stats.averageProgress }}%</div>
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">Progression moyenne</div>
                        </div>
                    </div>
                </section>

                <div v-if="recommendedFormations.length > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Recommandé pour vous</h3>
                            <p class="text-gray-600 text-sm mt-1">Basé sur votre activité d'apprentissage</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <Link v-for="formation in recommendedFormations" :key="formation.id"
                              :href="route('formation.show', formation.slug)"
                              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden group"
                        >
                            <div class="relative h-32 bg-gray-200">
                                <img v-if="formation.image"
                                     :src="'/storage/' + formation.image"
                                     :alt="formation.title"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div v-else class="flex items-center justify-center h-full bg-gradient-to-br from-gray-400 to-gray-500">
                                    <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div v-if="formation.is_featured" class="absolute top-2 right-2">
                                    <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded">Populaire</span>
                                </div>
                            </div>

                            <div class="p-4">
                                <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 text-sm group-hover:text-primary-600 transition-colors">
                                    {{ formation.title }}
                                </h4>
                                <div class="text-xs text-gray-500 mb-3">
                                    Par {{ getCreatorName(formation) }}
                                </div>

                                <div class="flex items-center gap-3 text-xs text-gray-600 mb-3">
                                    <div v-if="formation.duration_hours" class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ formatHours(formation.duration_hours) }}</span>
                                    </div>
                                    <div v-if="formation.difficulty_level" class="flex items-center">
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">
                                            {{ getDifficultyLabel(formation.difficulty_level) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span v-if="formation.price && formation.price > 0" class="text-lg font-bold text-gray-900">
                                        {{ new Intl.NumberFormat('fr-FR').format(formation.price) }} FCFA
                                    </span>
                                    <span v-else class="text-lg font-bold text-green-600">Gratuit</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <section class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Catégories populaires</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link v-for="category in popularCategories" :key="category.name"
                              :href="route('home-page')"
                              class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-all hover:scale-105 duration-300 text-center group"
                        >
                            <div class="text-lg font-bold text-gray-900 mb-1 group-hover:text-primary-600 transition-colors">
                                {{ category.name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ category.count }} formation{{ category.count > 1 ? 's' : '' }}
                            </div>
                        </Link>
                    </div>
                </section>

                <section class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-8 text-center text-white">
                    <h3 class="text-2xl font-bold mb-3">Développez vos compétences dès aujourd'hui</h3>
                    <p class="text-primary-100 mb-6 max-w-2xl mx-auto">
                        Explorez notre catalogue de formations et trouvez celle qui correspond à vos objectifs professionnels.
                    </p>
                    <Link :href="route('home-page')"
                          class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold px-8 py-3 rounded-lg hover:bg-primary-50 transition-colors"
                    >
                        Explorer les formations
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
