<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock3,
    Download,
    FileText,
    GraduationCap,
    Hash,
    Layers3,
    Pencil,
    Power,
    Settings2,
    Video,
} from '@lucide/vue';
import {computed} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface ChapterDetail {
    id: number;
    title: string;
    description: string | null;
    content: string | null;
    content_html: string;
    content_type: string;
    video_url: string | null;
    media_url: string | null;
    cover_image: string | null;
    duration_minutes: number | null;
    is_free: boolean;
    is_active: boolean;
    order_position: number;
    created_at: string;
    section: { id: number; title: string; formation: { id: number; title: string } };
}

const props = defineProps<{
    chapter: ChapterDetail;
}>();

const typeLabels: Record<string, string> = {
    text: 'Texte',
    video: 'Vidéo',
    pdf: 'PDF',
};

const stats = computed(() => [
    {label: 'Type', value: typeLabels[props.chapter.content_type] ?? props.chapter.content_type, icon: FileText, tint: 'text-[#ef477d] bg-[#7d254a]/35'},
    {label: 'Durée', value: props.chapter.duration_minutes ? `${props.chapter.duration_minutes} min` : '—', icon: Clock3, tint: 'text-amber-300 bg-amber-400/10'},
    {label: 'Position', value: `#${props.chapter.order_position}`, icon: Hash, tint: 'text-sky-300 bg-sky-400/10'},
]);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('fr-FR', {day: '2-digit', month: 'long', year: 'numeric'});
}
</script>

<template>
    <Head :title="chapter.title"/>

    <AdminLayout>
        <template #breadcrumb>
            <Link :href="safeRoute('admin.chapters.index')" class="admin-muted transition hover:text-[#a23362]">
                Chapitres
            </Link>
            <span class="admin-faint">/</span>
            <span class="admin-text max-w-[40ch] truncate font-medium">{{ chapter.title }}</span>
        </template>

        <div class="mx-auto min-w-0 max-w-7xl">
            <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <Link
                        :href="safeRoute('admin.chapters.index')"
                        aria-label="Retour aux chapitres"
                        class="admin-divider admin-muted admin-hover mt-1 grid size-10 shrink-0 place-items-center border transition"
                    >
                        <ArrowLeft class="size-5" :stroke-width="1.7"/>
                    </Link>
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <Link
                                :href="safeRoute('admin.sections.show', chapter.section.id)"
                                class="admin-panel-muted admin-text inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold transition hover:text-[#a23362]"
                            >
                                <Layers3 class="size-3.5" :stroke-width="1.8"/> {{ chapter.section.title }}
                            </Link>
                            <span class="admin-panel-muted admin-text px-2 py-1 text-[11px] font-semibold">{{ typeLabels[chapter.content_type] ?? chapter.content_type }}</span>
                            <span
                                :class="chapter.is_active ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-500/10 text-slate-400'"
                                class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold"
                            >
                                <span class="size-1.5 rounded-full" :class="chapter.is_active ? 'bg-emerald-400' : 'bg-slate-500'"/>
                                {{ chapter.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <span v-if="chapter.is_free" class="bg-emerald-400/10 px-2 py-1 text-[11px] font-semibold text-emerald-300">Aperçu gratuit</span>
                        </div>
                        <h1 class="admin-heading break-words text-2xl font-semibold tracking-tight [overflow-wrap:anywhere] sm:text-3xl">
                            {{ chapter.title }}
                        </h1>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <ConfirmAction
                        :href="safeRoute('admin.chapters.toggle-active', chapter.id)"
                        :message="chapter.is_active ? 'Désactiver ce chapitre ?' : 'Activer ce chapitre ?'"
                        :title="chapter.is_active ? 'Désactiver' : 'Activer'"
                        class="admin-divider admin-text admin-hover inline-flex h-11 items-center gap-2 border px-4 text-sm font-medium transition"
                        confirm-label="Confirmer"
                        method="patch"
                    >
                        <Power class="size-4" :stroke-width="1.7"/>
                        {{ chapter.is_active ? 'Désactiver' : 'Activer' }}
                    </ConfirmAction>
                    <Link
                        :href="safeRoute('admin.chapters.edit', chapter.id)"
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

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="grid gap-6">
                    <section v-if="chapter.description" class="admin-panel border">
                        <div class="admin-divider border-b px-5 py-4 sm:px-6">
                            <h2 class="admin-heading font-semibold">Description</h2>
                        </div>
                        <div class="p-5 sm:p-6">
                            <p class="admin-text whitespace-pre-line text-sm leading-7">{{ chapter.description }}</p>
                        </div>
                    </section>

                    <!-- Contenu selon le type -->
                    <section class="admin-panel border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center bg-[#7d254a]/35 text-[#ef477d]">
                                <FileText class="size-5" :stroke-width="1.7"/>
                            </span>
                            <h2 class="admin-heading font-semibold">Contenu</h2>
                        </div>
                        <div class="p-5 sm:p-6">
                            <video
                                v-if="chapter.content_type === 'video' && chapter.video_url"
                                :src="`/storage/${chapter.video_url}`"
                                class="w-full rounded-lg"
                                controls
                            />
                            <div v-else-if="chapter.content_type === 'pdf' && chapter.media_url" class="grid gap-4">
                                <img v-if="chapter.cover_image" :src="`/storage/${chapter.cover_image}`" alt="" class="max-h-64 w-auto rounded-lg border border-[color:var(--admin-border)]"/>
                                <a
                                    :href="`/storage/${chapter.media_url}`"
                                    class="admin-divider admin-text admin-hover inline-flex w-fit items-center gap-2 border px-4 py-2 text-sm font-medium transition"
                                    target="_blank"
                                >
                                    <Download class="size-4" :stroke-width="1.7"/>
                                    Ouvrir le PDF
                                </a>
                                <div
                                    v-if="chapter.content_html"
                                    class="rich-markdown admin-text text-sm"
                                    v-html="chapter.content_html"
                                />
                            </div>
                            <div
                                v-else-if="chapter.content_html"
                                class="rich-markdown admin-text text-sm"
                                v-html="chapter.content_html"
                            />
                            <p v-else class="admin-muted text-sm italic">Aucun contenu pour ce chapitre.</p>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-6 xl:sticky xl:top-24">
                    <section class="admin-panel min-w-0 overflow-hidden border">
                        <div class="admin-divider flex items-center gap-3 border-b px-5 py-4">
                            <Settings2 class="size-5 text-amber-300" :stroke-width="1.7"/>
                            <h2 class="admin-heading font-semibold">Détails</h2>
                        </div>
                        <dl class="grid min-w-0 gap-4 p-5">
                            <div class="grid min-w-0 grid-cols-[auto_minmax(0,1fr)] items-start gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><GraduationCap class="size-4" :stroke-width="1.7"/> Formation</dt>
                                <dd class="admin-heading min-w-0 break-words text-right text-sm font-medium [overflow-wrap:anywhere]">{{ chapter.section.formation.title }}</dd>
                            </div>
                            <div class="grid min-w-0 grid-cols-[auto_minmax(0,1fr)] items-start gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Layers3 class="size-4" :stroke-width="1.7"/> Section</dt>
                                <dd class="admin-heading min-w-0 break-words text-right text-sm font-medium [overflow-wrap:anywhere]">{{ chapter.section.title }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Video class="size-4" :stroke-width="1.7"/> Type</dt>
                                <dd class="admin-heading text-sm font-medium">{{ typeLabels[chapter.content_type] ?? chapter.content_type }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Clock3 class="size-4" :stroke-width="1.7"/> Durée</dt>
                                <dd class="admin-heading text-sm font-medium">{{ chapter.duration_minutes ? `${chapter.duration_minutes} min` : '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="admin-muted flex items-center gap-2 text-sm"><Calendar class="size-4" :stroke-width="1.7"/> Créé le</dt>
                                <dd class="admin-heading text-sm font-medium">{{ formatDate(chapter.created_at) }}</dd>
                            </div>
                        </dl>
                    </section>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>
