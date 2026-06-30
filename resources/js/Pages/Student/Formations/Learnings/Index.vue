<script lang="ts" setup>
import {Head, Link, router} from '@inertiajs/vue3';
import {computed} from 'vue';
import FormationCard from '@/Components/Learning/FormationCard.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import LearningLayout from '@/Layouts/LearningLayout.vue';
import type {LearningCatalogStats, LearningFormation} from '@/types/learning';
import {safeRoute} from '@/utilities/route';

interface ProgressStats {
    inProgress: number;
    averageProgress: number;
    completed: number;
}

const props = defineProps<{
    courses: LearningFormation[];
    stats: ProgressStats;
    catalogStats: LearningCatalogStats;
    filters: { sort: string };
}>();

const sortOptions = [
    {label: 'Activité récente', value: 'recent'},
    {label: 'Progression décroissante', value: 'progress-desc'},
    {label: 'Progression croissante', value: 'progress-asc'},
    {label: 'Titre A–Z', value: 'title'},
];

const summaryCards = computed(() => [
    {label: 'Cours en cours', value: props.stats.inProgress, icon: 'play-circle'},
    {label: 'Progression moyenne', value: `${props.stats.averageProgress}%`, icon: 'chart-bar'},
    {label: 'Cours terminés', value: props.stats.completed, icon: 'academic-cap'},
]);

function onSortChange(event: Event): void {
    const sort = (event.target as HTMLSelectElement).value;

    router.get(
        safeRoute('student.progress'),
        sort === 'recent' ? {} : {sort},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function enrollmentProgress(formation: LearningFormation): number {
    return Math.round(Number(formation.enrollments?.[0]?.progress_percentage ?? 0));
}

function courseHref(formation: LearningFormation): string {
    return safeRoute('course.player', formation.id);
}
</script>

<template>
    <Head title="Cours en cours"/>

    <LearningLayout :catalog-stats="catalogStats" active-item="in-progress">
        <template #breadcrumb>
            <span class="text-slate-300">En cours</span>
        </template>

        <div class="mx-auto max-w-[1540px] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <section class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-400">
                    IRMA Learning
                </p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Vos cours en cours
                </h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-400 sm:text-base">
                    Reprenez là où vous vous êtes arrêté. Suivez la progression de chacun de vos cours et
                    avancez à votre rythme vers la certification.
                </p>
            </section>

            <section class="mt-8 grid gap-3 sm:grid-cols-3">
                <div
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="flex items-center gap-4 border border-white/10 bg-[#101d2d] p-4"
                >
                    <span class="grid size-11 shrink-0 place-items-center border border-white/10 bg-[#16253a] text-sky-300">
                        <LearningIcon :name="card.icon" class="size-5 brightness-0 invert opacity-80"/>
                    </span>
                    <div class="min-w-0">
                        <p class="text-2xl font-semibold text-white">{{ card.value }}</p>
                        <p class="truncate text-xs text-slate-400">{{ card.label }}</p>
                    </div>
                </div>
            </section>

            <section class="mt-9">
                <div
                    v-if="courses.length > 0"
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-slate-400">
                        <span class="font-semibold text-slate-200">{{ courses.length }}</span>
                        cours en progression
                    </p>
                    <label class="relative self-start sm:self-auto">
                        <span class="sr-only">Trier les cours</span>
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

                <div
                    v-if="courses.length > 0"
                    class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <FormationCard
                        v-for="course in courses"
                        :key="course.id"
                        :formation="course"
                        :href="courseHref(course)"
                        :progress="enrollmentProgress(course)"
                        variant="scrimba"
                    />
                </div>

                <div
                    v-else
                    class="flex min-h-72 flex-col items-center justify-center border border-dashed border-white/15 bg-[#0c1b2c] px-5 text-center"
                >
                    <LearningIcon class="size-10 brightness-0 invert opacity-30" name="chart-bar"/>
                    <h2 class="mt-5 text-lg font-semibold text-white">Aucun cours en cours</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-400">
                        Inscrivez-vous à une formation pour commencer votre apprentissage. Vos cours en
                        progression apparaîtront ici.
                    </p>
                    <Link
                        :href="safeRoute('student.learnings')"
                        class="mt-5 inline-flex h-10 items-center bg-sky-500 px-4 text-sm font-semibold text-white transition hover:bg-sky-400"
                    >
                        Explorer les formations
                    </Link>
                </div>
            </section>
        </div>
    </LearningLayout>
</template>
