<script setup lang="ts">
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

interface Chapter {
    id: number;
    title: string;
    duration_minutes: number | null;
}

interface Section {
    id: number;
    title: string;
    chapters: Chapter[];
}

interface SectionState {
    id: number;
    unlocked: boolean;
    chapters_complete: boolean;
    exam_id: number | null;
    exam_title: string | null;
    exam_passed: boolean | null;
    exam_missing: boolean;
    needs_exam: boolean;
}

const props = withDefaults(
    defineProps<{
        sections: Section[];
        currentChapterId: number | null;
        completedChapters: number[];
        lockedSectionIds?: number[];
        sectionStates?: SectionState[];
    }>(),
    {
        lockedSectionIds: () => [],
        sectionStates: () => [],
    },
);

const emit = defineEmits<{
    select: [chapterId: number];
    selectExam: [examId: number];
}>();

function isCompleted(chapterId: number): boolean {
    return props.completedChapters.includes(chapterId);
}

function isLocked(sectionId: number): boolean {
    return props.lockedSectionIds.includes(sectionId);
}

function formatMinutes(minutes: number | null): string {
    return minutes ? `${minutes} min` : 'À votre rythme';
}

function sectionState(sectionId: number): SectionState | null {
    return props.sectionStates.find((state) => state.id === sectionId) ?? null;
}
</script>

<template>
    <div class="grid gap-5">
        <section v-for="section in sections" :key="section.id">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-white">
                <LearningIcon
                    :name="isLocked(section.id) ? 'lock-closed' : 'book-open'"
                    class="size-4 brightness-0 invert"
                    :class="isLocked(section.id) ? 'opacity-50' : 'opacity-80'"
                />
                <span :class="isLocked(section.id) ? 'text-slate-500' : 'text-white'">{{ section.title }}</span>
            </h4>

            <div class="mt-2 grid gap-1">
                <button
                    v-for="chapter in section.chapters"
                    :key="chapter.id"
                    type="button"
                    :disabled="isLocked(section.id)"
                    class="w-full border px-3 py-3 text-left transition"
                    :class="[
                        currentChapterId === chapter.id
                            ? 'border-[#a72f5d] bg-[#7d254a]/35'
                            : 'border-transparent text-slate-400 hover:border-white/10 hover:bg-white/5 hover:text-white',
                        isLocked(section.id) ? 'cursor-not-allowed opacity-40 hover:border-transparent hover:bg-transparent' : '',
                    ]"
                    @click="isLocked(section.id) ? null : emit('select', chapter.id)"
                >
                    <span class="flex items-start gap-3">
                        <span
                            class="mt-0.5 grid size-6 shrink-0 place-items-center"
                            :class="isCompleted(chapter.id)
                                ? 'bg-emerald-500/20 text-emerald-300'
                                : currentChapterId === chapter.id
                                    ? 'bg-[#d24376] text-white'
                                    : 'bg-white/5 text-slate-500'"
                        >
                            <LearningIcon
                                :name="isCompleted(chapter.id) ? 'check' : 'play'"
                                class="size-3.5 brightness-0 invert"
                            />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-100">{{ chapter.title }}</span>
                            <span class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                <span>{{ formatMinutes(chapter.duration_minutes) }}</span>
                                <span v-if="isCompleted(chapter.id)" class="text-emerald-300">Terminé</span>
                                <span v-else-if="currentChapterId === chapter.id" class="text-[#ff79a5]">En cours</span>
                            </span>
                        </span>
                    </span>
                </button>

                <button
                    v-if="sectionState(section.id)?.exam_id"
                    :disabled="!sectionState(section.id)?.chapters_complete"
                    :class="sectionState(section.id)?.exam_passed
                        ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200'
                        : sectionState(section.id)?.needs_exam
                            ? 'border-amber-400/40 bg-amber-400/10 text-amber-100'
                            : 'border-white/10 text-slate-400'"
                    class="mt-1 flex w-full items-center gap-3 border px-3 py-3 text-left transition enabled:hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-45"
                    type="button"
                    @click="sectionState(section.id)?.exam_id && emit('selectExam', sectionState(section.id)!.exam_id!)"
                >
                    <span class="grid size-6 shrink-0 place-items-center bg-white/5">
                        <LearningIcon :name="sectionState(section.id)?.exam_passed ? 'academic-cap' : 'document-text'" class="size-3.5 brightness-0 invert"/>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold">{{ sectionState(section.id)?.exam_title || 'Évaluation de la section' }}</span>
                        <span class="mt-1 block text-[11px] opacity-70">
                            {{ sectionState(section.id)?.exam_passed ? 'Réussie' : sectionState(section.id)?.chapters_complete ? 'Obligatoire pour continuer' : 'Disponible après les chapitres' }}
                        </span>
                    </span>
                </button>

                <div v-else class="mt-1 flex items-start gap-3 border border-rose-400/30 bg-rose-400/10 px-3 py-3 text-rose-200">
                    <LearningIcon class="mt-0.5 size-4 shrink-0 brightness-0 invert" name="x-mark"/>
                    <p class="text-xs leading-5">Évaluation obligatoire non configurée. Contactez l’administration.</p>
                </div>
            </div>
        </section>
    </div>
</template>
