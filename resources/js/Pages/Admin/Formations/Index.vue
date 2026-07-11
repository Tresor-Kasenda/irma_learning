<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import {Eye, ImageIcon, Pencil, Plus, Power, Trash2} from '@lucide/vue';
import BulkActionBar from '@/Components/Admin/BulkActionBar.vue';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';
import {useCurrencyFormatter} from '@/composables/useCurrencyFormatter';

interface FormationRow {
    id: number;
    slug: string;
    title: string;
    image: string | null;
    difficulty_level: string;
    duration_hours: number | null;
    price: number | string | null;
    is_active: boolean;
    is_featured: boolean;
    chapter_count: number;
    students_count: number;
}

interface FormationsPage {
    data: FormationRow[];
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    formations: FormationsPage;
    filters: Record<string, string | undefined>;
}>();

const indexRoute = safeRoute('admin.formations.index');
const {formatCurrency} = useCurrencyFormatter();

const difficultyOptions = [
    {value: 'beginner', label: 'Débutant'},
    {value: 'intermediate', label: 'Intermédiaire'},
    {value: 'advanced', label: 'Avancé'},
];

const difficultyLabels: Record<string, string> = {
    beginner: 'Débutant',
    intermediate: 'Intermédiaire',
    advanced: 'Avancé',
};

const columns: Column[] = [
    {key: 'title', label: 'Titre', sortable: true},
    {key: 'difficulty_level', label: 'Niveau'},
    {key: 'price', label: 'Prix', sortable: true, align: 'right'},
    {key: 'chapter_count', label: 'Chapitres', align: 'center'},
    {key: 'students_count', label: 'Inscrits', align: 'center'},
    {key: 'is_featured', label: 'Vedette', type: 'boolean', align: 'center'},
    {key: 'is_active', label: 'Actif', type: 'boolean', align: 'center'},
];

const filterDefs: FilterDef[] = [
    {key: 'difficulty_level', label: 'Niveau', options: difficultyOptions},
    {key: 'is_active', label: 'Statut', options: [{value: '1', label: 'Actives'}, {value: '0', label: 'Inactives'}]},
    {key: 'is_featured', label: 'Vedette', options: [{value: '1', label: 'Oui'}, {value: '0', label: 'Non'}]},
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
];

function formatPrice(value: number | string | null): string {
    const amount = Number(value ?? 0);

    return amount <= 0
        ? 'Gratuit'
        : formatCurrency(amount);
}
</script>

<template>
    <Head title="Formations"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="admin-text font-medium">Formations</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#ef477d]">Catalogue</p>
                    <h1 class="admin-heading mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Formations</h1>
                    <p class="admin-muted mt-2 text-sm">{{ formations.total }} formation(s) disponibles dans le catalogue.</p>
                </div>
                <Link
                    :href="safeRoute('admin.formations.create')"
                    class="inline-flex h-11 items-center justify-center gap-2 bg-[#a23362] px-5 text-sm font-semibold text-white transition hover:bg-[#b2386e]"
                >
                    <Plus class="size-4" :stroke-width="2"/>
                    Nouvelle formation
                </Link>
            </div>

            <DataTable
                :columns="columns"
                :filters="filters"
                :index-route="indexRoute"
                :rows="formations"
                searchable
                selectable
            >
                <template #filters>
                    <FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/>
                </template>

                <template #bulk="{selected, clearSelection}">
                    <BulkActionBar :count="selected.length">
                        <button
                            class="admin-text admin-hover border border-[#a23362]/30 px-3 py-1 text-xs font-medium transition"
                            type="button"
                            @click="clearSelection"
                        >
                            Effacer la sélection
                        </button>
                    </BulkActionBar>
                </template>

                <template #cell-title="{row}">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="row.image"
                            :src="`/storage/${row.image}`"
                            alt=""
                            class="size-10 shrink-0 object-cover"
                        />
                        <span v-else class="admin-panel-muted admin-muted grid size-10 shrink-0 place-items-center">
                            <ImageIcon class="size-4" :stroke-width="1.7"/>
                        </span>
                        <Link :href="safeRoute('admin.formations.show', row.id)" class="admin-heading font-medium transition hover:text-[#a23362]">
                            {{ row.title }}
                        </Link>
                    </div>
                </template>

                <template #cell-difficulty_level="{value}">
                    <span class="admin-panel-muted admin-text inline-flex px-2 py-0.5 text-xs font-medium">
                        {{ difficultyLabels[value as string] ?? value }}
                    </span>
                </template>

                <template #cell-price="{value}">
                    {{ formatPrice(value as number | string | null) }}
                </template>

                <template #actions="{row}">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="safeRoute('admin.formations.show', row.id)"
                            :aria-label="`Voir ${row.title}`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Voir le détail"
                        >
                            <Eye class="size-4" :stroke-width="1.7"/>
                        </Link>
                        <Link
                            :href="safeRoute('admin.formations.edit', row.id)"
                            :aria-label="`Modifier ${row.title}`"
                            class="admin-muted admin-hover p-2 transition"
                            title="Modifier"
                        >
                            <Pencil class="size-4" :stroke-width="1.7"/>
                        </Link>
                        <ConfirmAction
                            :aria-label="`${row.is_active ? 'Désactiver' : 'Activer'} ${row.title}`"
                            :href="safeRoute('admin.formations.toggle-active', row.id)"
                            :message="row.is_active ? 'Désactiver cette formation ?' : 'Activer cette formation ?'"
                            :title="row.is_active ? 'Désactiver' : 'Activer'"
                            class="admin-muted admin-hover p-2 transition"
                            confirm-label="Confirmer"
                            method="patch"
                        >
                            <Power class="size-4" :stroke-width="1.7"/>
                        </ConfirmAction>
                        <ConfirmAction
                            :aria-label="`Supprimer ${row.title}`"
                            :href="safeRoute('admin.formations.destroy', row.id)"
                            class="p-2 text-rose-500 transition hover:bg-rose-400/10 hover:text-rose-300"
                            danger
                            message="Cette formation et ses sections seront supprimées."
                            method="delete"
                            title="Supprimer la formation"
                        >
                            <Trash2 class="size-4" :stroke-width="1.7"/>
                        </ConfirmAction>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
