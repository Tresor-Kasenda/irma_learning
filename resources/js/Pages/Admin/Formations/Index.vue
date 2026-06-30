<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';
import ConfirmAction from '@/Components/Admin/ConfirmAction.vue';
import DataTable, {type Column} from '@/Components/Admin/DataTable.vue';
import FilterBar, {type FilterDef} from '@/Components/Admin/FilterBar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {safeRoute} from '@/utilities/route';

interface FormationRow {
    id: number;
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
];

function formatPrice(value: number | string | null): string {
    const amount = Number(value ?? 0);

    return amount <= 0
        ? 'Gratuit'
        : new Intl.NumberFormat('fr-FR', {style: 'currency', currency: 'USD', maximumFractionDigits: 0}).format(amount);
}
</script>

<template>
    <Head title="Formations"/>

    <AdminLayout>
        <template #breadcrumb>
            <span class="font-medium text-slate-700">Formations</span>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Formations</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ formations.total }} formation(s) au catalogue.</p>
                </div>
                <Link
                    :href="safeRoute('admin.formations.create')"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#bf045b] px-4 text-sm font-semibold text-white transition hover:opacity-90"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                    </svg>
                    Nouvelle formation
                </Link>
            </div>

            <DataTable
                :columns="columns"
                :filters="filters"
                :index-route="indexRoute"
                :rows="formations"
                searchable
            >
                <template #filters>
                    <FilterBar :definitions="filterDefs" :filters="filters" :index-route="indexRoute"/>
                </template>

                <template #cell-title="{row}">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="row.image"
                            :src="`/storage/${row.image}`"
                            alt=""
                            class="size-9 shrink-0 rounded-md object-cover"
                        />
                        <span v-else class="grid size-9 shrink-0 place-items-center rounded-md bg-slate-100 text-slate-400">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path d="M4 16l4-4 4 4 4-6 4 6M4 5h16v14H4z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="font-medium text-slate-800">{{ row.title }}</span>
                    </div>
                </template>

                <template #cell-difficulty_level="{value}">
                    <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                        {{ difficultyLabels[value as string] ?? value }}
                    </span>
                </template>

                <template #cell-price="{value}">
                    {{ formatPrice(value as number | string | null) }}
                </template>

                <template #actions="{row}">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="safeRoute('admin.formations.edit', row.id)"
                            class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="Modifier"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path d="M4 20h4L18 10l-4-4L4 16v4zM14 6l4 4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </Link>
                        <ConfirmAction
                            :href="safeRoute('admin.formations.toggle-active', row.id)"
                            :message="row.is_active ? 'Désactiver cette formation ?' : 'Activer cette formation ?'"
                            :title="row.is_active ? 'Désactiver' : 'Activer'"
                            class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            confirm-label="Confirmer"
                            method="patch"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path d="M12 3v9M6.3 6.3a8 8 0 1011.4 0" stroke-linecap="round"/>
                            </svg>
                        </ConfirmAction>
                        <ConfirmAction
                            :href="safeRoute('admin.formations.destroy', row.id)"
                            class="rounded-md p-1.5 text-red-500 hover:bg-red-50"
                            danger
                            message="Cette formation et ses sections seront supprimées."
                            method="delete"
                            title="Supprimer la formation"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path d="M6 7h12M9 7V5h6v2M7 7l1 13h8l1-13" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </ConfirmAction>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
