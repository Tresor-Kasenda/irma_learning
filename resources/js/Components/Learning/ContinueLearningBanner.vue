<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ContentFormatBadges from '@/Components/Learning/ContentFormatBadges.vue';
import LearningIcon from '@/Components/Learning/LearningIcon.vue';
import type { LearningFormation } from '@/types/learning';

const props = withDefaults(
    defineProps<{
        formation: LearningFormation;
        href: string;
        actionLabel: string;
        contextLabel: string;
        progress?: number | null;
        subtitle?: string | null;
    }>(),
    {
        progress: null,
        subtitle: null,
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
    (event.target as HTMLImageElement).src = fallbackImages[props.formation.id % fallbackImages.length];
}
</script>

<template>
    <section class="grid overflow-hidden border border-[#8f355d]/55 bg-[#151a2a] md:grid-cols-[240px_1fr_auto]">
        <div class="relative min-h-44 md:min-h-0">
            <img
                :src="imageUrl()"
                :alt="formation.title"
                class="absolute inset-0 size-full object-cover"
                @error="replaceBrokenImage"
            />
            <span class="absolute left-3 top-3 bg-[#a52f5c] px-2 py-1 text-[11px] font-semibold text-white">
                {{ progress !== null ? 'Reprendre' : 'À la une' }}
            </span>
        </div>

        <div class="flex flex-col justify-center p-5 md:p-6">
            <p class="text-[11px] font-semibold uppercase text-slate-500">{{ contextLabel }}</p>
            <h2 class="mt-2 text-xl font-semibold text-white sm:text-2xl">{{ formation.title }}</h2>
            <p class="mt-2 line-clamp-1 text-sm text-slate-400">
                {{ subtitle ?? formation.short_description ?? formation.description }}
            </p>

            <div v-if="progress !== null" class="mt-5 max-w-lg">
                <div class="h-1.5 bg-white/10">
                    <div class="h-full bg-[#df3e75]" :style="{ width: `${progress}%` }" />
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ progress }}% complété</p>
            </div>
            <ContentFormatBadges
                v-else
                class="mt-5"
                compact
                :videos="formation.video_count"
                :pdfs="formation.pdf_count"
                :texts="formation.text_count"
            />
        </div>

        <div class="flex items-center p-5 pt-0 md:p-6">
            <Link
                :href="href"
                class="inline-flex h-11 items-center justify-center gap-2 bg-[#a72f5d] px-5 text-sm font-semibold text-white transition hover:bg-[#c43b6d]"
            >
                <LearningIcon name="play" class="size-4 brightness-0 invert" />
                {{ actionLabel }}
            </Link>
        </div>
    </section>
</template>

