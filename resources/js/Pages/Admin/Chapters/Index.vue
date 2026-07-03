<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {Eye, Pencil, Plus, Power, Trash2} from '@lucide/vue';
import {computed} from 'vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface SectionOption {
    value: number;
    label: string;
}

interface ChapterRow {
    id: number;
    title: string;
    content_type: string;
    duration_minutes: number | null;
    is_free: boolean;
    is_active: boolean;
    section: { id: number; title: string; formation: { id: number; title: string } | null } | null;
}

interface ChaptersPage {
    data: ChapterRow[];
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    chapters: ChaptersPage;
    sections: SectionOption[];
    filters: Record<string, string | undefined>;
}>();

const indexRoute = safeRoute('admin.chapters.index');

const typeLabels: Record<string, string> = {
    text: 'Texte',
    video: 'Vidéo',
    pdf: 'PDF',
};

const columns: Column[] = [
    {key: 'title', label: 'Titre', sortable: true},
    {key: 'section', label: 'Section'},
    {key: 'content_type', label: 'Type'},
    {key: 'duration_minutes', label: 'Durée', align: 'center'},
    {key: 'is_active', label: 'Actif', type: 'boolean', align: 'center'},
];

const filterDefs = computed<FilterDef[]>(() => [
    {key: 'section_id', label: 'Section', options: props.sections.map((s) => ({value: String(s.value), label: s.label}))},
    {
        key: 'content_type',
        label: 'Type',
        options: [
            {value: 'text', label: 'Texte'},
            {value: 'video', label: 'Vidéo'},
            {value: 'pdf', label: 'PDF'},
        ],
    },
    {key: 'is_active', label: 'Statut', options: [{value: '1', label: 'Actifs'}, {value: '0', label: 'Inactifs'}]},
    {
        key: 'per_page',
        label: 'Lignes par page',
        options: [
            {value: '10', label: '10 lignes'},
            {value: '25', label: '25 lignes'},
            {value: '50', label: '50 lignes'},
            {value: '100', label: '100 lignes'},
        ],
        includeEmpty: false,
        defaultValue: '10',
    },
]);
</script>

<template>
    <Head title="Chapitres"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="admin-text font-medium">Chapitres</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Catalogue</p>
                    <h1 class="admin-heading mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Chapitres</h1>
                    <p class="admin-muted mt-2 text-sm">{{ chapters.total }} chapitre(s) au total.</p>
                </div>
                <Link
                    :href="safeRoute('admin.chapters.create')"
                    class="inline-flex h-11 items-center justify-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                >
                    <Plus class="size-4" :stroke-width="2"/>
                    Nouveau chapitre
                </Link>
            </div>

            <DataTable
                :columns="columns"
                :filters="filters"
                :index-route="indexRoute"
                :rows="chapters"
                searchable
            >
                <template #filters>
                    <FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/>
                </template>

                <template #cell-title="{row}">
                    <Link :href="safeRoute('admin.chapters.show', row.id)" class="admin-heading font-medium transition hover:text-[#a23362]">
                        {{ row.title }}
                    </Link>
                </template>

                <template #cell-section="{row}">
                    <div v-if="row.section" class="min-w-0">
                        <p class="admin-text truncate text-sm">{{ row.section.title }}</p>
                        <p v-if="row.section.formation" class="admin-faint truncate text-xs">{{ row.section.formation.title }}</p>
                    </div>
                    <span v-else class="admin-faint text-xs">—</span>
                </template>

                <template #cell-content_type="{value}">
                    <span class="admin-panel-muted admin-text inline-flex px-2 py-0.5 text-xs font-medium">
                        {{ typeLabels[value as string] ?? value }}
                    </span>
                </template>

                <template #cell-duration_minutes="{value}">
                    <span class="admin-text text-sm">{{ value ? `${value} min` : '—' }}</span>
                </template>

                <template #actions="{row}">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="safeRoute('admin.chapters.show', row.id)"
                            :aria-label="`Voir ${row.title}`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Voir le détail"
                        >
                            <Eye class="size-4" :stroke-width="1.7"/>
                        </Link>
                        <Link
                            :href="safeRoute('admin.chapters.edit', row.id)"
                            :aria-label="`Modifier ${row.title}`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Modifier"
                        >
                            <Pencil class="size-4" :stroke-width="1.7"/>
                        </Link>
                        <ConfirmAction
                            :aria-label="`${row.is_active ? 'Désactiver' : 'Activer'} ${row.title}`"
                            :href="safeRoute('admin.chapters.toggle-active', row.id)"
                            :message="row.is_active ? 'Désactiver ce chapitre ?' : 'Activer ce chapitre ?'"
                            :title="row.is_active ? 'Désactiver' : 'Activer'"
                            class="admin-muted admin-hover p-2 transition"
                            confirm-label="Confirmer"
                            method="patch"
                        >
                            <Power class="size-4" :stroke-width="1.7"/>
                        </ConfirmAction>
                        <ConfirmAction
                            :aria-label="`Supprimer ${row.title}`"
                            :href="safeRoute('admin.chapters.destroy', row.id)"
                            class="p-2 text-rose-500 transition hover:bg-rose-400/10 hover:text-rose-300"
                            danger
                            message="Ce chapitre et ses fichiers seront supprimés."
                            method="delete"
                            title="Supprimer le chapitre"
                        >
                            <Trash2 class="size-4" :stroke-width="1.7"/>
                        </ConfirmAction>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
