<script lang="ts" setup>
import {router, useForm} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {useCurrencyFormatter} from '@/composables/useCurrencyFormatter';

interface Chapter {
    id: number;
    title: string;
    content_type: string | null;
    duration_minutes: number | null;
    is_free: boolean;
    order_position: number;
}

interface Section {
    id: number;
    title: string;
    description: string | null;
    order_position: number;
    chapters: Chapter[];
}

interface Formation {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    description: string | null;
    image: string | null;
    price: number;
    duration_hours: number | null;
    difficulty_level: string;
    tags: string[] | null;
    students_count?: number;
    sections: Section[];
}

const props = defineProps<{
    formation: Formation;
    chapterCount: number;
    isEnrolled: boolean;
    completedChapterIds: number[];
}>();

const showFullDescription = ref(false);
const expandedSectionIds = ref<number[]>([]);
const enrollForm = useForm({});
const {formatCurrency} = useCurrencyFormatter();

const descriptionText = computed(() => props.formation.description ?? '');
const isLongDescription = computed(() => descriptionText.value.length > 300);
const truncatedDescription = computed(() =>
    isLongDescription.value
        ? descriptionText.value.substring(0, 300) + '…'
        : descriptionText.value,
);

const difficultyLabels: Record<string, string> = {
    beginner: 'Débutant',
    intermediate: 'Intermédiaire',
    advanced: 'Avancé',
};

function getDifficultyLabel(level: string): string {
    return difficultyLabels[level] ?? level;
}

function handleCTA(): void {
    if (props.isEnrolled) {
        router.get(route('course.player', { formation: props.formation.id }));
        return;
    }
    enrollForm.post(route('formation.enroll', props.formation.id));
}

function toggleSection(sectionId: number): void {
    expandedSectionIds.value = expandedSectionIds.value.includes(sectionId)
        ? expandedSectionIds.value.filter((id) => id !== sectionId)
        : [...expandedSectionIds.value, sectionId];
}

function formatDuration(minutes: number | null): string {
    return minutes ? `${minutes} min` : 'À votre rythme';
}
</script>

<template>
    <PublicLayout
        :title="formation.title"
        :meta-description="formation.short_description ?? `Formation ${formation.title} – BTPCMA`"
        :og-image="formation.image"
        :canonical-url="route('formation.show', formation.slug)"
    >
        <section class="mx-auto my-28 flex w-full max-w-7xl flex-col gap-10 px-5 sm:px-10 md:flex-row lg:gap-16">
            <article class="flex flex-col flex-1">
                <h1 class="font-medium text-xl sm:text-2xl/snug lg:text-4xl text-gray-900">
                    {{ formation.title }}
                </h1>

                <p v-if="formation.short_description" class="font-medium mt-6 text-gray-600">
                    {{ formation.short_description }}
                </p>

                <div class="mt-12 flex flex-col space-y-6">
                    <span class="text-lg font-semibold text-gray-900">Présentation</span>
                    <div class="text-gray-700 space-y-4">
                        <p class="whitespace-pre-line leading-7">
                            {{ showFullDescription ? descriptionText : truncatedDescription }}
                        </p>
                        <button
                            v-if="isLongDescription"
                            class="text-primary-600 hover:underline mt-2 text-sm font-medium"
                            type="button"
                            @click="showFullDescription = !showFullDescription"
                        >
                            {{ showFullDescription ? 'Voir moins' : 'Voir plus' }}
                        </button>
                    </div>
                </div>

                <div class="mt-12 flex flex-col space-y-6">
                    <span class="text-lg font-semibold text-gray-900">Aperçu du contenu</span>
                    <div class="text-gray-700 space-y-4">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-2">
                                <svg class="size-5 text-primary-600" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <p>{{ chapterCount }} chapitres</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="size-5 text-primary-600" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <p>Niveau : <span class="capitalize font-medium">{{
                                        getDifficultyLabel(formation.difficulty_level)
                                    }}</span></p>
                            </div>
                            <div v-if="formation.duration_hours" class="flex items-center gap-2">
                                <svg class="size-5 text-primary-600" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                                <p>{{ formation.duration_hours }} heures de formation</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="formation.sections.length > 0" class="mt-12 flex flex-col space-y-6">
                    <span class="text-lg font-semibold text-gray-900">Contenu du programme</span>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <section
                            v-for="section in formation.sections"
                            :key="section.id"
                            class="border-b border-gray-100 last:border-b-0"
                        >
                            <button
                                :aria-expanded="expandedSectionIds.includes(section.id)"
                                class="flex w-full items-center justify-between gap-4 px-4 py-4 text-left transition hover:bg-gray-50 sm:px-5"
                                type="button"
                                @click="toggleSection(section.id)"
                            >
                            <div class="flex min-w-0 items-center gap-3">
                                <svg class="size-5 text-primary-500 shrink-0" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <span class="min-w-0 break-words text-sm font-semibold text-gray-800">{{ section.title }}</span>
                            </div>
                            <span class="flex shrink-0 items-center gap-2 text-xs text-gray-500">
                                {{ section.chapters.length }} chapitre{{ section.chapters.length > 1 ? 's' : '' }}
                                <svg :class="expandedSectionIds.includes(section.id) ? 'rotate-180' : ''" class="size-4 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                            </span>
                            </button>
                            <div v-if="expandedSectionIds.includes(section.id)" class="border-t border-gray-100 bg-gray-50/70 px-4 py-2 sm:px-5">
                                <p v-if="section.description" class="border-b border-gray-200 py-3 text-sm leading-6 text-gray-600">{{ section.description }}</p>
                                <ol v-if="section.chapters.length" class="divide-y divide-gray-200">
                                    <li v-for="(chapter, index) in section.chapters" :key="chapter.id" class="flex min-w-0 items-center gap-3 py-3 text-sm">
                                        <span class="grid size-7 shrink-0 place-items-center rounded-full bg-white text-xs font-semibold text-irma-primary shadow-sm">{{ index + 1 }}</span>
                                        <span class="min-w-0 flex-1 break-words text-gray-700">{{ chapter.title }}</span>
                                        <span class="shrink-0 text-xs text-gray-500">{{ formatDuration(chapter.duration_minutes) }}</span>
                                    </li>
                                </ol>
                                <p v-else class="py-3 text-sm text-gray-500">Les chapitres seront bientôt disponibles.</p>
                            </div>
                        </section>
                    </div>
                </div>
            </article>

            <div
                class="md:sticky h-max z-20 md:w-72 lg:w-80 xl:w-90 bg-white w-full shadow-xl shadow-gray-100/50 rounded-md p-5 md:p-6 border border-gray-100 top-24">
                <span class="font-medium text-gray-700 text-sm mb-4 pb-3 border-b border-gray-100 w-full flex">
                    Détails du cours
                </span>

                <ul class="flex flex-col divide-y divide-gray-100 *:py-3 first:*:pt-0 last:*:pb-0 mt-4">
                    <!-- Durée -->
                    <li v-if="formation.duration_hours" class="flex justify-between items-center">
                        <div class="flex items-center text-gray-500 text-sm">
                            <svg class="size-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span>Durée</span>
                        </div>
                        <span class="font-semibold text-gray-900 text-right">
                            {{
                                formation.duration_hours
                            }} heures
                        </span>
                    </li>

                    <!-- Tarif -->
                    <li class="flex flex-col">
                        <div class="flex items-center text-gray-500 text-sm">
                            <svg class="size-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                            <span>Tarif</span>
                        </div>
                        <div class="flex flex-col flex-1 mt-2">
                            <div
                                class="bg-gray-50 border border-gray-100 rounded-md px-3 py-2 flex justify-between items-center">
                                <span class="text-sm text-gray-500">Prix :</span>
                                <span :class="Number(formation.price ?? 0) === 0 ? 'text-green-600' : 'text-gray-900'" class="font-semibold">
                                    {{ Number(formation.price ?? 0) === 0 ? 'Gratuit' : formatCurrency(Number(formation.price), 2) }}
                                </span>
                            </div>
                        </div>
                    </li>

                    <!-- Niveau -->
                    <li class="flex justify-between items-center">
                        <div class="flex items-center text-gray-500 text-sm">
                            <svg class="size-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                            <span>Niveau</span>
                        </div>
                        <span class="font-semibold text-gray-900 capitalize">
                            {{ getDifficultyLabel(formation.difficulty_level) }}
                        </span>
                    </li>

                    <!-- Tags -->
                    <li v-if="formation.tags?.length" class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in formation.tags"
                            :key="tag"
                            class="bg-gray-100 hover:bg-blue-100 transition-colors px-3 py-1.5 rounded-full text-xs font-medium text-primary-800 flex items-center gap-1"
                        >
                            <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd"
                                      d="M5.5 3A2.5 2.5 0 0 0 3 5.5v2.879a2.5 2.5 0 0 0 .732 1.767l6.5 6.5a2.5 2.5 0 0 0 3.536 0l2.878-2.878a2.5 2.5 0 0 0 0-3.536l-6.5-6.5A2.5 2.5 0 0 0 8.38 3H5.5ZM6 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                                      fill-rule="evenodd"/>
                            </svg>
                            {{ tag }}
                        </span>
                    </li>

                    <!-- CTA -->
                    <li>
                        <button
                            :disabled="enrollForm.processing"
                            class="group relative w-full flex items-center justify-center gap-2 h-11 rounded-md bg-irma-primary text-white text-sm font-semibold overflow-hidden transition hover:opacity-90 disabled:opacity-60"
                            type="button"
                            @click="handleCTA"
                        >
                            <span v-if="enrollForm.processing">Traitement en cours…</span>
                            <template v-else>
                                {{ isEnrolled ? 'Continuer la formation' : 'Commencer la formation' }}
                                <svg class="size-4 transition duration-500 group-hover:rotate-[360deg]"
                                     fill="none" stroke="currentColor" stroke-width="1.5"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </template>
                        </button>
                    </li>
                </ul>
            </div>
        </section>
    </PublicLayout>
</template>
