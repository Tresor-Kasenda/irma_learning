<script lang="ts" setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Calendar,
    ClipboardCheck,
    Clock3,
    FileText,
    GraduationCap,
    Hash,
    Pencil,
    Power,
    Settings2,
    Video,
} from '@lucide/vue';
import type {Component} from 'vue';
import {computed, ref} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import ExamEditor from '@/Components/Admin/ExamEditor.vue';
import ResourceFormModal from '@/Components/Admin/ResourceFormModal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {createEmptyExam, type ExamEditorData} from '@/types/admin-exam';
import {safeRoute} from '@/utilities/route';

interface Chapter {
    id: number;
    title: string;
    content_type: string;
    duration_minutes: number | null;
    is_free: boolean;
    is_active: boolean;
}

interface SectionDetail {
    id: number;
    title: string;
    description: string | null;
    duration: number | null;
    order_position: number;
    is_active: boolean;
    created_at: string;
    formation: { id: number; title: string };
    has_exam: boolean;
    exam: { id: number; title: string } | null;
    chapters_count: number;
    chapters: Chapter[];
}

const props = defineProps<{
    section: SectionDetail;
}>();

const examModalOpen = ref(false);
const examForm = useForm<ExamEditorData & {examable_type: string; examable_id: number}>({
    ...createEmptyExam(),
    examable_type: 'App\\Models\\Section',
    examable_id: props.section.id,
});

function createExam(): void {
    examForm.post(safeRoute('admin.exams.store'), {
        onSuccess: () => {
            examModalOpen.value = false;
        },
    });
}

const stats = computed(() => [
    {label: 'Chapitres', value: props.section.chapters_count, icon: BookOpen, tint: 'text-[#ef477d] bg-[#7d254a]/35'},
    {label: 'Durée', value: props.section.duration ? `${props.section.duration} min` : '—', icon: Clock3, tint: 'text-amber-300 bg-amber-400/10'},
    {label: 'Position', value: `#${props.section.order_position}`, icon: Hash, tint: 'text-sky-300 bg-sky-400/10'},
]);

const contentTypeMeta: Record<string, { label: string; icon: Component }> = {
    video: {label: 'Vidéo', icon: Video},
    pdf: {label: 'PDF', icon: FileText},
    text: {label: 'Texte', icon: FileText},
};

function chapterMeta(type: string): { label: string; icon: Component } {
    return contentTypeMeta[type] ?? {label: type, icon: FileText};
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric'});
}
</script>

<template>
    <Head :title="section.title"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.sections.index')" class="admin-muted transition hover:text-[#a23362]">
                Sections
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text max-w-[40ch] truncate font-medium">{{ section.title }}</span>
        </template>

        <div class="mx-auto min-w-0 max-w-7xl">
            <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <Link
                        :href="safeRoute('admin.sections.index')"
                        aria-label="Retour aux sections"
                        class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                    >
                        <ArrowLeft class="size-5" :stroke-width="1.7"/>
                    </Link>
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <Link
                                :href="safeRoute('admin.formations.show', section.formation.id)"
                                class="admin-panel-muted admin-text inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold transition hover:text-[#a23362]"
                            >
                                <GraduationCap class="size-3.5" :stroke-width="1.8"/> {{ section.formation.title }}
                            </Link>
                            <span
                                :class="section.is_active ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-500/10 text-slate-400'"
                                class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold"
                            >
                                <span class="size-1.5 rounded-full" :class="section.is_active ? 'bg-emerald-400' : 'bg-slate-500'"/>
                                {{ section.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span v-if="section.has_exam" class="inline-flex items-center gap-1 bg-violet-400/10 px-2 py-1 text-[11px] font-semibold text-violet-300">
                                <GraduationCap class="size-3" :stroke-width="2"/> Examen
                            </span>
                        </div>
                        <h1 class="admin-heading break-words text-2xl font-semibold tracking-tight [overflow-wrap:anywhere] sm:text-3xl">
                            {{ section.title }}
                        </h1>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <Link
                        v-if="section.exam"
                        :href="safeRoute('admin.exams.show', section.exam.id)"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                    >
                        <ClipboardCheck class="size-4" :stroke-width="1.7"/>
                        Gérer l’examen
                    </Link>
                    <button
                        v-else
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                        type="button"
                        @click="examModalOpen = true"
                    >
                        <ClipboardCheck class="size-4" :stroke-width="1.7"/>
                        Créer l’examen
                    </button>
                    <ConfirmAction
                        :href="safeRoute('admin.sections.toggle-active', section.id)"
                        :message="section.is_active ? 'Désactiver cette section ?' : 'Activer cette section ?'"
                        :title="section.is_active ? 'Désactiver' : 'Activer'"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                        confirm-label="Confirmer"
                        method="patch"
                    >
                        <Power class="size-4" :stroke-width="1.7"/>
                        {{ section.is_active ? 'Désactiver' : 'Activer' }}
                    </ConfirmAction>
                    <Link
                        :href="safeRoute('admin.sections.edit', section.id)"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                    >
                        <Pencil class="size-4" :stroke-width="1.8"/>
                        Modifier
                    </Link>
                </div>
            </div>

            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div v-for="stat in stats" :key="stat.label" class="admin-panel flex items-center gap-4 border p-5">
                    <span class="grid size-11 shrink-0 place-items-center" :class="stat.tint">
                        <component :is="stat.icon" class="size-5" :stroke-width="1.7"/>
                    </span>
                    <div>
                        <p class="admin-heading text-2xl font-semibold tracking-tight">{{ stat.value }}</p>
                        <p class="admin-muted text-xs">{{ stat.label }}</p>
                    </div>
                </div>
            </div>

            <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="grid min-w-0 gap-6">
                    <section v-if="section.description" class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/35 text-[#ef477d]">
                                <BookOpen class="size-5" :stroke-width="1.7"/>
                            </span>
                            <h2 class="admin-heading font-semibold">Description</h2>
                        </div>
                        <div class="p-5 sm:p-6">
                            <p class="admin-text whitespace-pre-line text-sm leading-7">{{ section.description }}</p>
                        </div>
                    </section>

                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-sky-400/10 text-sky-300">
                                <BookOpen class="size-5" :stroke-width="1.7"/>
                            </span>
                            <div>
                                <h2 class="admin-heading font-semibold">Chapitres</h2>
                                <p class="admin-muted mt-0.5 text-xs">{{ section.chapters_count }} chapitre(s)</p>
                            </div>
                        </div>

                        <ul v-if="section.chapters.length > 0" class="divide-y divide-[color:var(--admin-border)]">
                            <li v-for="(chapter, index) in section.chapters" :key="chapter.id" class="flex items-center gap-3 px-5 py-3 sm:px-6">
                                <span class="admin-faint w-6 shrink-0 text-xs font-semibold">{{ String(index + 1).padStart(2, '0') }}</span>
                                <component :is="chapterMeta(chapter.content_type).icon" class="admin-faint size-4 shrink-0" :stroke-width="1.7"/>
                                <span class="admin-text min-w-0 flex-1 truncate text-sm">{{ chapter.title }}</span>
                                <span v-if="chapter.is_free" class="shrink-0 bg-emerald-400/10 px-2 py-0.5 text-[11px] font-medium text-emerald-300">Gratuit</span>
                                <span v-if="!chapter.is_active" class="shrink-0 bg-slate-500/10 px-2 py-0.5 text-[11px] font-medium text-slate-400">Inactif</span>
                                <span class="admin-faint shrink-0 text-xs">{{ chapterMeta(chapter.content_type).label }}</span>
                                <span v-if="chapter.duration_minutes" class="admin-muted shrink-0 text-xs">{{ chapter.duration_minutes }} min</span>
                            </li>
                        </ul>
                        <div v-else class="admin-divider m-5 border border-dashed px-5 py-10 text-center sm:m-6">
                            <BookOpen class="mx-auto size-8 text-slate-600" :stroke-width="1.5"/>
                            <p class="admin-heading mt-3 text-sm font-medium">Aucun chapitre</p>
                            <p class="admin-muted mt-1 text-xs">Cette section n'a pas encore de chapitre.</p>
                            <Link :href="safeRoute('admin.sections.edit', section.id)" class="mt-4 inline-block text-sm font-semibold text-[#ef477d] hover:text-rose-300">
                                Ajouter des chapitres
                            </Link>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Settings2 class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <h2 class="admin-heading font-semibold">Détails</h2>
                        </div>
                        <dl class="grid gap-4 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><GraduationCap class="size-4" :stroke-width="1.7"/> Formation</dt>
                                <dd class="admin-heading max-w-[60%] truncate text-sm font-medium">{{ section.formation.title }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Hash class="size-4" :stroke-width="1.7"/> Position</dt>
                                <dd class="admin-heading text-sm font-medium">#{{ section.order_position }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Clock3 class="size-4" :stroke-width="1.7"/> Durée</dt>
                                <dd class="admin-heading text-sm font-medium">{{ section.duration ? `${section.duration} min` : '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Calendar class="size-4" :stroke-width="1.7"/> Créée le</dt>
                                <dd class="admin-heading text-sm font-medium">{{ formatDate(section.created_at) }}</dd>
                            </div>
                        </dl>
                    </section>

                </aside>
            </div>
        </div>

        <ResourceFormModal
            :show="examModalOpen"
            :processing="examForm.processing"
            title="Créer l’examen de la section"
            size="xl"
            :slide-over="false"
            submit-label="Créer l’examen"
            @close="examModalOpen = false"
            @submit="createExam"
        >
            <ExamEditor v-model="examForm" :errors="examForm.errors"/>
        </ResourceFormModal>
    </AdminLayout>
</template>
