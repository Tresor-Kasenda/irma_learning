<script setup lang="ts">
import LearningIcon from '@/Components/Learning/LearningIcon.vue';

interface Exam {
    id: number;
}

interface Chapter {
    id: number;
    title: string;
    duration_minutes: number | null;
    exams?: Exam | null;
}

interface Section {
    id: number;
    title: string;
    chapters: Chapter[];
}

const props = defineProps<{
    sections: Section[];
    currentChapterId: number | null;
    completedChapters: number[];
}>();

const emit = defineEmits<{
    select: [chapterId: number];
}>();

function isCompleted(chapterId: number): boolean {
    return props.completedChapters.includes(chapterId);
}

function formatMinutes(minutes: number | null): string {
    return minutes ? `${minutes} min` : 'À votre rythme';
}
</script>

<template>
    <div class="grid gap-5">
        <section v-for="section in sections" :key="section.id">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-white">
                <LearningIcon name="book-open" class="size-4 brightness-0 invert opacity-80" />
                {{ section.title }}
            </h4>

            <div class="mt-2 grid gap-1">
                <button
                    v-for="chapter in section.chapters"
                    :key="chapter.id"
                    type="button"
                    class="w-full border px-3 py-3 text-left transition"
                    :class="currentChapterId === chapter.id
                        ? 'border-[#a72f5d] bg-[#7d254a]/35'
                        : 'border-transparent text-slate-400 hover:border-white/10 hover:bg-white/5 hover:text-white'"
                    @click="emit('select', chapter.id)"
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
                                :name="isCompleted(chapter.id) ? 'academic-cap' : 'play'"
                                class="size-3.5 brightness-0 invert"
                            />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-100">{{ chapter.title }}</span>
                            <span class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                <span>{{ formatMinutes(chapter.duration_minutes) }}</span>
                                <span v-if="chapter.exams" class="text-amber-300">Examen final</span>
                            </span>
                        </span>
                    </span>
                </button>
            </div>
        </section>
    </div>
</template>
