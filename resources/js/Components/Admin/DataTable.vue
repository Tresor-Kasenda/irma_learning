<script lang="ts" setup generic="T extends object = Record<string, unknown>">
import {router} from '@inertiajs/vue3';
import {computed, ref} from 'vue';
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

function alignClass(col: Column): string {
    const map = {left: 'text-left', center: 'text-center', right: 'text-right'} as const;

    return map[col.align ?? 'left'];
}

function formatCell(value: unknown, col: Column): string {
    if (value === null || value === undefined) {
        return '—';
    }
    if (col.type === 'date') {
        return new Date(value as string | number).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short', year: 'numeric'});
    }

    return String(value);
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div v-if="searchable || $slots.filters" class="flex flex-col gap-3 border-b border-slate-200 p-3 sm:flex-row sm:items-center">
            <label v-if="searchable" class="relative block min-w-0 flex-1">
                <span class="sr-only">Rechercher</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 21l-4.3-4.3M11 19a8 8 0 110-16 8 8 0 010 16z" stroke-linecap="round"/>
                </svg>
                <input
                    :value="search"
                    class="h-10 w-full rounded-lg border border-slate-200 pl-9 pr-3 text-sm outline-none focus:border-[#bf045b]"
                    placeholder="Rechercher…"
                    type="search"
                    @input="onSearch"
                />
            </label>
            <div class="flex items-center gap-2"><slot name="filters"/></div>
        </div>

        <slot name="bulk" :selected="selected"/>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th v-if="selectable" class="w-10 px-4 py-3">
                            <input :checked="allChecked" type="checkbox" class="rounded border-slate-300" @change="toggleAll"/>
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[alignClass(col), col.sortable ? 'cursor-pointer select-none hover:text-slate-800' : '']"
                            class="px-4 py-3 font-semibold"
                            @click="sortBy(col)"
                        >
                            <span class="inline-flex items-center gap-1">
                                {{ col.label }}
                                <span v-if="col.sortable && filters.sort === col.key" class="text-[#bf045b]">
                                    {{ filters.dir === 'asc' ? '↑' : '↓' }}
                                </span>
                            </span>
                        </th>
                        <th v-if="$slots.actions" class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in rows.data" :key="rowId(row)" class="hover:bg-slate-50">
                        <td v-if="selectable" class="px-4 py-3">
                            <input
                                :checked="selected.includes(rowId(row))"
                                type="checkbox"
                                class="rounded border-slate-300"
                                @change="toggleRow(rowId(row))"
                            />
                        </td>
                        <td v-for="col in columns" :key="col.key" :class="alignClass(col)" class="px-4 py-3 text-slate-700">
                            <slot :name="`cell-${col.key}`" :row="row" :value="cellValue(row, col.key)">
                                <span
                                    v-if="col.type === 'boolean'"
                                    :class="cellValue(row, col.key) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ cellValue(row, col.key) ? 'Oui' : 'Non' }}
                                </span>
                                <span v-else-if="col.type === 'badge'" class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                    {{ formatCell(cellValue(row, col.key), col) }}
                                </span>
                                <span v-else>{{ formatCell(cellValue(row, col.key), col) }}</span>
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-4 py-3 text-right">
                            <slot name="actions" :row="row"/>
                        </td>
                    </tr>
                    <tr v-if="rows.data.length === 0">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-4 py-10 text-center text-sm text-slate-400">
                            Aucun élément à afficher.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :from="rows.from" :links="rows.links" :to="rows.to" :total="rows.total"/>
    </div>
</template>
