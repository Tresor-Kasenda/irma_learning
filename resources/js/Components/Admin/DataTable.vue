<script generic="T extends object = Record<string, unknown>" lang="ts" setup>
import {router} from '@inertiajs/vue3';
import {ArrowDown, ArrowUp, Search} from '@lucide/vue';
import {computed, ref, watch} from 'vue';
import Pagination from '@/Components/Admin/Pagination.vue';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    type?: 'text' | 'badge' | 'boolean' | 'date' | 'number';
    align?: 'left' | 'center' | 'right';
}

type FilterValue = string | number | undefined;
type Filters = Record<string, FilterValue>;
type RowId = number | string;

interface Paginated {
    data: T[];
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = withDefaults(
    defineProps<{
        rows: Paginated;
        columns: Column[];
        indexRoute: string;
        filters?: Filters;
        searchable?: boolean;
        selectable?: boolean;
        rowKey?: string;
    }>(),
    {
        filters: () => ({}),
        searchable: false,
        selectable: false,
        rowKey: 'id',
    },
);

const emit = defineEmits<{ (e: 'update:selected', ids: RowId[]): void }>();

const search = ref<string>(String(props.filters.search ?? ''));
const selected = ref<RowId[]>([]);
let debounce: ReturnType<typeof setTimeout> | undefined;

function rowId(row: T): RowId {
    const value = (row as Record<string, unknown>)[props.rowKey];

    return typeof value === 'number' ? value : String(value);
}

function cellValue(row: T, key: string): unknown {
    return (row as Record<string, unknown>)[key];
}

function clean(params: Filters): Filters {
    return Object.fromEntries(
        Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined),
    );
}

function navigate(overrides: Filters): void {
    router.get(props.indexRoute, clean({...props.filters, search: search.value, ...overrides}), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onSearch(event: Event): void {
    search.value = (event.target as HTMLInputElement).value;
    clearTimeout(debounce);
    debounce = setTimeout(() => navigate({page: undefined}), 300);
}

function sortBy(column: Column): void {
    if (!column.sortable) {
        return;
    }
    const dir = props.filters.sort === column.key && props.filters.dir === 'asc' ? 'desc' : 'asc';
    navigate({sort: column.key, dir});
}

const allChecked = computed(
    () => props.rows.data.length > 0 && selected.value.length === props.rows.data.length,
);
const someChecked = computed(() => selected.value.length > 0 && !allChecked.value);

watch(
    () => props.rows.data.map((row) => rowId(row)).join('|'),
    () => clearSelection(),
);

function toggleAll(): void {
    selected.value = allChecked.value ? [] : props.rows.data.map((row) => rowId(row));
    emit('update:selected', selected.value);
}

function toggleRow(id: RowId): void {
    selected.value = selected.value.includes(id)
        ? selected.value.filter((x) => x !== id)
        : [...selected.value, id];
    emit('update:selected', selected.value);
}

function clearSelection(): void {
    selected.value = [];
    emit('update:selected', selected.value);
}

function alignClass(col: Column): string {
    const map = {left: 'text-left', center: 'text-center', right: 'text-right'} as const;

    return map[col.align ?? 'left'];
}

function formatCell(value: unknown, col: Column): string {
    if (value === null || value === undefined) {
        return '—';
    }
    if (col.type === 'date') {
        return new Date(value as string | number).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    return String(value);
}
</script>

<template>
    <div class="admin-panel overflow-hidden border">
        <div v-if="searchable || $slots.filters"
             class="admin-divider flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center">
            <label v-if="searchable" class="relative block min-w-0 flex-1">
                <span class="sr-only">Rechercher</span>
                <Search :stroke-width="1.8"
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"/>
                <input
                    :value="search"
                    class="admin-field h-10 w-full border pl-9 pr-3 text-sm outline-none"
                    placeholder="Rechercher…"
                    type="search"
                    @input="onSearch"
                />
            </label>
            <div class="flex flex-wrap items-center gap-2">
                <slot name="filters"/>
            </div>
        </div>

        <slot :clear-selection="clearSelection" :selected="selected" name="bulk"/>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="admin-divider admin-panel-muted admin-muted border-b text-left text-[10px] uppercase tracking-widest">
                    <th v-if="selectable" class="w-10 px-4 py-3">
                        <input
                            :checked="allChecked"
                            :indeterminate="someChecked"
                            aria-label="Sélectionner toutes les lignes de cette page"
                            class="size-4 rounded border-slate-300 accent-[#a23362]"
                            type="checkbox"
                            @change="toggleAll"
                        />
                    </th>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        :class="[alignClass(col), col.sortable ? 'cursor-pointer select-none hover:text-[#a23362]' : '']"
                        class="px-4 py-3 font-semibold"
                        @click="sortBy(col)"
                    >
                            <span class="inline-flex items-center gap-1">
                                {{ col.label }}
                                <ArrowUp v-if="col.sortable && filters.sort === col.key && filters.dir === 'asc'"
                                         class="size-3.5 text-[#ef477d]"/>
                                <ArrowDown v-if="col.sortable && filters.sort === col.key && filters.dir !== 'asc'"
                                           class="size-3.5 text-[#ef477d]"/>
                            </span>
                    </th>
                    <th v-if="$slots.actions" class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <tr v-for="row in rows.data" :key="rowId(row)" class="transition hover:bg-slate-50 dark:hover:bg-white/3">
                    <td v-if="selectable" class="px-4 py-3">
                        <input
                            :aria-label="`Sélectionner ${String(cellValue(row, 'title') ?? rowId(row))}`"
                            :checked="selected.includes(rowId(row))"
                            class="size-4 rounded border-slate-300 accent-[#a23362]"
                            type="checkbox"
                            @change="toggleRow(rowId(row))"
                        />
                    </td>
                    <td v-for="col in columns" :key="col.key" :class="alignClass(col)"
                        class="admin-text px-4 py-3.5">
                        <slot :name="`cell-${col.key}`" :row="row" :value="cellValue(row, col.key)">
                                <span
                                    v-if="col.type === 'boolean'"
                                    :class="cellValue(row, col.key) ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'admin-panel-muted admin-muted'"
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ cellValue(row, col.key) ? 'Oui' : 'Non' }}
                                </span>
                            <span v-else-if="col.type === 'badge'"
                                  class="admin-panel-muted admin-text inline-flex px-2 py-0.5 text-xs font-medium">
                                    {{ formatCell(cellValue(row, col.key), col) }}
                                </span>
                            <span v-else>{{ formatCell(cellValue(row, col.key), col) }}</span>
                        </slot>
                    </td>
                    <td v-if="$slots.actions" class="px-4 py-3 text-right">
                        <slot :row="row" name="actions"/>
                    </td>
                </tr>
                <tr v-if="rows.data.length === 0">
                    <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)"
                        class="admin-muted px-4 py-10 text-center text-sm">
                        Aucun élément à afficher.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <Pagination :from="rows.from" :links="rows.links" :to="rows.to" :total="rows.total"/>
    </div>
</template>
