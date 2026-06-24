<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ContentFormatBadges from '@/Components/Learning/ContentFormatBadges.vue';
import type { LearningFormation } from '@/types/learning';

const props = withDefaults(
    defineProps<{
        formation: LearningFormation;
        href: string;
        progress?: number | null;
        badge?: string;
    }>(),
    {
        progress: null,
        badge: '',
    },
);

const fallbackImages = [
    '/images/image1.webp',
    '/images/image2.webp',
    '/images/course-2.jpg',
    '/images/home_bg.jpg',
];

function imageUrl(): string {
    if (!props.formation.image) {
        return fallbackImages[props.formation.id % fallbackImages.length];
    }

    if (/^https?:\/\//.test(props.formation.image) || props.formation.image.startsWith('/')) {
        return props.formation.image;
    }

    return `/storage/${props.formation.image}`;
}

function replaceBrokenImage(event: Event): void {
    const image = event.target as HTMLImageElement;
    image.src = fallbackImages[props.formation.id % fallbackImages.length];
}

function difficultyLabel(): string {
    const labels: Record<string, string> = {
        beginner: 'Débutant',
        intermediate: 'Intermédiaire',
        advanced: 'Avancé',
    };

    return labels[props.formation.difficulty_level] ?? props.formation.difficulty_level;
}

function difficultyClass(): string {
    const classes: Record<string, string> = {
        beginner: 'text-emerald-300',
        intermediate: 'text-amber-300',
        advanced: 'text-rose-300',
    };

    return classes[props.formation.difficulty_level] ?? 'text-slate-300';
}

function formationBadge(): string {
    if (props.badge) {
        return props.badge;
    }

    const normalizedTags = props.formation.tags?.map((tag) => tag.toLowerCase()) ?? [];

    if (normalizedTags.some((tag) => tag.includes('entreprise'))) {
        return 'Entreprise';
    }

    if (normalizedTags.some((tag) => tag.includes('continue'))) {
        return 'Continue';
    }

    return props.formation.is_featured ? 'Certifiante' : 'Formation';
}

function formatDuration(): string {
    const hours = props.formation.duration_hours;

    if (!hours) {
        return 'À votre rythme';
    }

    const wholeHours = Math.floor(hours);
    const minutes = Math.round((hours - wholeHours) * 60);

    return minutes > 0 ? `${wholeHours} h ${minutes} min` : `${wholeHours} h`;
}

function formatPrice(): string {
    const amount = Number(props.formation.price ?? 0);

    if (amount <= 0) {
        return 'Gratuit';
    }

    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <article
        class="group flex min-h-[374px] flex-col overflow-hidden border border-white/10 bg-[#101d2d] transition hover:-translate-y-0.5 hover:border-[#a53a66]/65 hover:shadow-2xl"
    >
        <Link :href="href" class="relative block h-32 overflow-hidden">
            <img
                :src="imageUrl()"
                :alt="formation.title"
                class="size-full object-cover transition duration-300 group-hover:scale-[1.03]"
                @error="replaceBrokenImage"
            />
            <span class="absolute left-3 top-3 bg-[#a72f5d] px-2 py-1 text-[11px] font-semibold text-white">
                {{ formationBadge() }}
            </span>
        </Link>

        <div class="border-b border-white/10 px-3 py-2.5">
            <ContentFormatBadges
                compact
                :videos="formation.video_count"
                :pdfs="formation.pdf_count"
                :texts="formation.text_count"
            />
        </div>

        <div class="flex flex-1 flex-col p-3.5">
            <Link
                :href="href"
                class="line-clamp-2 text-base font-semibold leading-6 text-white transition group-hover:text-[#ff79a5]"
            >
                {{ formation.title }}
            </Link>
            <p class="mt-1.5 line-clamp-2 text-sm leading-5 text-slate-400">
                {{ formation.short_description ?? formation.description }}
            </p>

            <div class="mt-auto pt-5">
                <div class="flex items-end justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-slate-200">Équipe pédagogique IRMA</p>
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ formation.chapter_count ?? 0 }} ressources
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium" :class="difficultyClass()">{{ difficultyLabel() }}</p>
                        <p class="mt-1 text-[11px] text-slate-500">{{ formatDuration() }}</p>
                    </div>
                </div>

                <div v-if="progress !== null" class="mt-4 flex items-center gap-3">
                    <div class="h-1.5 flex-1 bg-white/10">
                        <div class="h-full bg-[#df3e75]" :style="{ width: `${progress}%` }" />
                    </div>
                    <span class="text-xs font-semibold text-[#ff79a5]">{{ progress }}%</span>
                </div>
                <div v-else class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-slate-500">{{ formation.students_count ?? 0 }} apprenants</span>
                    <span
                        class="text-base font-semibold"
                        :class="Number(formation.price ?? 0) <= 0 ? 'text-emerald-300' : 'text-white'"
                    >
                        {{ formatPrice() }}
                    </span>
                </div>
            </div>
        </div>
    </article>
</template>

