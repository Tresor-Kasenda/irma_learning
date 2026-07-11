<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Calendar,
    Clock3,
    DollarSign,
    FileText,
    GraduationCap,
    ImageIcon,
    Layers3,
    Pencil,
    Power,
    Sparkles,
    Tag,
    Users,
    Video,
} from '@lucide/vue';
import type {Component} from 'vue';
import {computed} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {useCurrencyFormatter} from '@/composables/useCurrencyFormatter';

interface Chapter {
    id: number;
    title: string;
    content_type: string;
    duration_minutes: number | null;
    is_active: boolean;
}

interface SectionDetail {
    id: number;
    title: string;
    description: string | null;
    duration: number | null;
    is_active: boolean;
    has_exam: boolean;
    chapters_count: number;
    chapters: Chapter[];
}

interface FormationDetail {
    id: number;
    slug: string;
    title: string;
    short_description: string | null;
    description: string | null;
    image: string | null;
    difficulty_level: string;
    duration_hours: number | null;
    price: number | string | null;
    tags: string[] | null;
    is_active: boolean;
    is_featured: boolean;
    created_at: string;
    sections_count: number;
    chapters_count: number;
    enrollments_count: number;
    sections: SectionDetail[];
}

const props = defineProps<{
    formation: FormationDetail;
}>();
const {formatCurrency} = useCurrencyFormatter();

const difficultyLabels: Record<string, string> = {
    beginner: 'Débutant',
    intermediate: 'Intermédiaire',
    advanced: 'Avancé',
};

const stats = computed(() => [
    {label: 'Sections', value: props.formation.sections_count, icon: Layers3, tint: 'text-sky-300 bg-sky-400/10'},
    {label: 'Chapitres', value: props.formation.chapters_count, icon: BookOpen, tint: 'text-[#ef477d] bg-[#7d254a]/35'},
    {label: 'Inscrits', value: props.formation.enrollments_count, icon: Users, tint: 'text-emerald-300 bg-emerald-400/10'},
    {label: 'Durée', value: props.formation.duration_hours ? `${props.formation.duration_hours} h` : '—', icon: Clock3, tint: 'text-amber-300 bg-amber-400/10'},
]);

const contentTypeMeta: Record<string, { label: string; icon: Component }> = {
    video: {label: 'Vidéo', icon: Video},
    pdf: {label: 'PDF', icon: FileText},
    text: {label: 'Texte', icon: FileText},
};

function chapterMeta(type: string): { label: string; icon: Component } {
    return contentTypeMeta[type] ?? {label: type, icon: FileText};
}

function formatPrice(value: number | string | null): string {
    const amount = Number(value ?? 0);

    return amount <= 0
        ? 'Gratuit'
        : formatCurrency(amount);
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric'});
}
</script>

<template>
    <Head :title="formation.title"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.formations.index')" class="admin-muted transition hover:text-[#a23362]">
                Formations
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text max-w-[40ch] truncate font-medium">{{ formation.title }}</span>
        </template>

        <div class="mx-auto min-w-0 max-w-7xl">
            <!-- En-tête -->
            <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <Link
                        :href="safeRoute('admin.formations.index')"
                        aria-label="Retour aux formations"
                        class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                    >
                        <ArrowLeft class="size-5" :stroke-width="1.7"/>
                    </Link>
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="admin-panel-muted admin-text px-2 py-1 text-[11px] font-semibold">
                                {{ difficultyLabels[formation.difficulty_level] ?? formation.difficulty_level }}
                            </span>
                            <span
                                :class="formation.is_active ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-500/10 text-slate-400'"
                                class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold"
                            >
                                <span class="size-1.5 rounded-full" :class="formation.is_active ? 'bg-emerald-400' : 'bg-slate-500'"/>
                                {{ formation.is_active ? 'Active' : 'Masquée' }}
                            </span>
                            <span v-if="formation.is_featured" class="inline-flex items-center gap-1 bg-amber-400/10 px-2 py-1 text-[11px] font-semibold text-amber-300">
                                <Sparkles class="size-3" :stroke-width="2"/> À la une
                            </span>
                        </div>
                        <h1 class="admin-heading break-words text-2xl font-semibold tracking-tight [overflow-wrap:anywhere] sm:text-3xl">
                            {{ formation.title }}
                        </h1>
                        <p v-if="formation.short_description" class="admin-muted mt-2 max-w-2xl text-sm leading-6">
                            {{ formation.short_description }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <ConfirmAction
                        :href="safeRoute('admin.formations.toggle-active', formation.id)"
                        :message="formation.is_active ? 'Désactiver cette formation ?' : 'Activer cette formation ?'"
                        :title="formation.is_active ? 'Désactiver' : 'Activer'"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                        confirm-label="Confirmer"
                        method="patch"
                    >
                        <Power class="size-4" :stroke-width="1.7"/>
                        {{ formation.is_active ? 'Désactiver' : 'Activer' }}
                    </ConfirmAction>
                    <Link
                        :href="safeRoute('admin.formations.edit', formation.id)"
                        class="inline-flex h-11 items-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                    >
                        <Pencil class="size-4" :stroke-width="1.8"/>
                        Modifier
                    </Link>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
                <div class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-6">
                    <!-- Image -->
                    <section v-if="formation.image" class="admin-panel min-w-0 max-w-full overflow-hidden border">
                        <div class="relative aspect-video max-h-[420px] w-full overflow-hidden bg-slate-100 dark:bg-slate-950/40">
                            <img
                                :src="`/storage/${formation.image}`"
                                :alt="formation.title"
                                class="absolute inset-0 block size-full max-w-full object-cover object-center"
                                decoding="async"
                                loading="lazy"
                            />
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/35 to-transparent"/>
                        </div>
                    </section>

                    <!-- Description -->
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/35 text-[#ef477d]">
                                <BookOpen class="size-5" :stroke-width="1.7"/>
                            </span>
                            <h2 class="admin-heading font-semibold">Description</h2>
                        </div>
                        <div class="p-5 sm:p-6">
                            <p v-if="formation.description" class="admin-text whitespace-pre-line text-sm leading-7">{{ formation.description }}</p>
                            <p v-else class="admin-muted text-sm italic">Aucune description renseignée.</p>
                        </div>
                    </section>

                    <!-- Programme -->
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center justify-between gap-3 border-b px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 shrink-0 place-items-center bg-sky-400/10 text-sky-300">
                                    <Layers3 class="size-5" :stroke-width="1.7"/>
                                </span>
                                <div>
                                    <h2 class="admin-heading font-semibold">Programme</h2>
                                    <p class="admin-muted mt-0.5 text-xs">{{ formation.sections_count }} section(s) · {{ formation.chapters_count }} chapitre(s)</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3 p-5 sm:p-6">
                            <article
                                v-for="(section, index) in formation.sections"
                                :key="section.id"
                                class="admin-panel-muted min-w-0 max-w-full overflow-hidden border"
                            >
                                <div class="admin-divider flex min-w-0 items-center gap-3 border-b px-4 py-3">
                                    <span class="admin-text grid size-7 shrink-0 place-items-center bg-slate-200 text-xs font-bold dark:bg-white/5">
                                        {{ String(index + 1).padStart(2, '0') }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="admin-heading truncate text-sm font-medium">{{ section.title }}</p>
                                        <p v-if="section.description" class="admin-muted mt-0.5 truncate text-xs">{{ section.description }}</p>
                                    </div>
                                    <span v-if="section.has_exam" class="inline-flex shrink-0 items-center gap-1 bg-violet-400/10 px-2 py-0.5 text-[11px] font-semibold text-violet-300">
                                        <GraduationCap class="size-3.5" :stroke-width="1.8"/> Examen
                                    </span>
                                    <span v-if="!section.is_active" class="shrink-0 bg-slate-500/10 px-2 py-0.5 text-[11px] font-medium text-slate-400">Inactive</span>
                                </div>

                                <ul v-if="section.chapters.length > 0" class="divide-y divide-[color:var(--admin-border)]">
                                    <li v-for="chapter in section.chapters" :key="chapter.id" class="flex items-center gap-3 px-4 py-2.5">
                                        <component :is="chapterMeta(chapter.content_type).icon" class="admin-faint size-4 shrink-0" :stroke-width="1.7"/>
                                        <span class="admin-text min-w-0 flex-1 truncate text-sm">{{ chapter.title }}</span>
                                        <span class="admin-faint shrink-0 text-xs">{{ chapterMeta(chapter.content_type).label }}</span>
                                        <span v-if="chapter.duration_minutes" class="admin-muted shrink-0 text-xs">{{ chapter.duration_minutes }} min</span>
                                    </li>
                                </ul>
                                <p v-else class="admin-faint px-4 py-3 text-xs italic">Aucun chapitre dans cette section.</p>
                            </article>

                            <div v-if="formation.sections.length === 0" class="admin-divider border border-dashed px-5 py-10 text-center">
                                <Layers3 class="mx-auto size-8 text-slate-600" :stroke-width="1.5"/>
                                <p class="admin-heading mt-3 text-sm font-medium">Aucune section</p>
                                <p class="admin-muted mt-1 text-xs">Le programme de cette formation est encore vide.</p>
                                <Link :href="safeRoute('admin.formations.edit', formation.id)" class="mt-4 inline-block text-sm font-semibold text-[#ef477d] hover:text-rose-300">
                                    Ajouter des sections
                                </Link>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Aside -->
                <aside class="grid min-w-0 gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Sparkles class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <h2 class="admin-heading font-semibold">Détails</h2>
                        </div>
                        <dl class="grid gap-4 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><GraduationCap class="size-4" :stroke-width="1.7"/> Niveau</dt>
                                <dd class="admin-heading text-sm font-medium">{{ difficultyLabels[formation.difficulty_level] ?? formation.difficulty_level }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><DollarSign class="size-4" :stroke-width="1.7"/> Prix</dt>
                                <dd class="admin-heading text-sm font-medium">{{ formatPrice(formation.price) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Clock3 class="size-4" :stroke-width="1.7"/> Durée</dt>
                                <dd class="admin-heading text-sm font-medium">{{ formation.duration_hours ? `${formation.duration_hours} h` : '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Calendar class="size-4" :stroke-width="1.7"/> Créée le</dt>
                                <dd class="admin-heading text-sm font-medium">{{ formatDate(formation.created_at) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section v-if="formation.tags && formation.tags.length > 0" class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Tag class="size-5 text-sky-300" :stroke-width="1.7"/>
                            <h2 class="admin-heading font-semibold">Tags</h2>
                        </div>
                        <div class="flex flex-wrap gap-2 p-5">
                            <span v-for="tag in formation.tags" :key="tag" class="admin-panel-muted admin-text px-2.5 py-1 text-xs font-medium">
                                {{ tag }}
                            </span>
                        </div>
                    </section>

                    <section v-if="!formation.image" class="admin-panel border">
                        <div class="flex flex-col items-center gap-2 px-5 py-8 text-center">
                            <ImageIcon class="size-8 text-slate-600" :stroke-width="1.5"/>
                            <p class="admin-muted text-xs">Aucune image de couverture.</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>
