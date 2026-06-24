<script setup lang="ts">
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

interface Exam {
    id: number;
    title: string;
    description?: string | null;
    duration_minutes: number;
    passing_score: number;
    max_attempts?: number;
}

defineProps<{
    exam: Exam;
    passed: boolean;
    chapterCompleted: boolean;
}>();

const emit = defineEmits<{
    take: [];
    complete: [];
}>();
</script>

<template>
    <section class="mt-8 border border-amber-400/25 bg-[#171d2b]">
        <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex items-start gap-4">
                <span
                    class="grid size-12 shrink-0 place-items-center"
                    :class="passed ? 'bg-emerald-500/15' : 'bg-amber-500/15'"
                >
                    <LearningIcon
                        :name="passed ? 'academic-cap' : 'document-text'"
                        class="size-6 brightness-0 invert"
                    />
                </span>
                <div>
                    <p class="text-[11px] font-semibold uppercase" :class="passed ? 'text-emerald-300' : 'text-amber-300'">
                        Étape finale du chapitre
                    </p>
                    <h3 class="mt-1 text-lg font-semibold text-white">{{ exam.title }}</h3>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                        {{ exam.description ?? 'Validez les connaissances acquises avant de poursuivre le parcours.' }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500">
                        <span>{{ exam.duration_minutes }} minutes</span>
                        <span>Note requise : {{ exam.passing_score }}%</span>
                        <span v-if="exam.max_attempts">{{ exam.max_attempts }} tentative(s)</span>
                        <span v-else>Tentatives illimitées</span>
                    </div>
                </div>
            </div>

            <button
                v-if="!passed"
                type="button"
                class="inline-flex h-11 shrink-0 items-center justify-center gap-2 bg-amber-500 px-5 text-sm font-semibold text-[#211807] transition hover:bg-amber-400"
                @click="emit('take')"
            >
                <LearningIcon name="document-text" class="size-4" />
                Passer l'examen
            </button>
            <button
                v-else-if="!chapterCompleted"
                type="button"
                class="inline-flex h-11 shrink-0 items-center justify-center gap-2 bg-emerald-500 px-5 text-sm font-semibold text-[#061b12] transition hover:bg-emerald-400"
                @click="emit('complete')"
            >
                <LearningIcon name="arrow-right" class="size-4" />
                Valider et continuer
            </button>
            <span v-else class="inline-flex h-11 shrink-0 items-center gap-2 border border-emerald-400/30 px-4 text-sm font-semibold text-emerald-300">
                <LearningIcon name="academic-cap" class="size-4 brightness-0 invert" />
                Examen réussi
            </span>
        </div>
    </section>
</template>
