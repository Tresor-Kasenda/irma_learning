<script lang="ts" setup>
import {router} from '@inertiajs/vue3';
import SearchableSelect from '@/Components/Admin/Fields/SearchableSelect.vue';

export interface FilterDef {
    key: string;
    label: string;
    options: { value: string; label: string }[];
    includeEmpty?: boolean;
    emptyLabel?: string;
    defaultValue?: string;
}

const props = withDefaults(
    defineProps<{
        indexRoute: string;
        filters: Record<string, any>;
        definitions: FilterDef[];
    }>(),
    {},
);

function clean(params: Record<string, any>): Record<string, any> {
    return Object.fromEntries(
        Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined),
    );
}

function filterOptions(definition: FilterDef): { value: string; label: string }[] {
    if (definition.includeEmpty === false) {
        return definition.options;
    }

    return [
        {value: '', label: definition.emptyLabel ?? `${definition.label} : tous`},
        ...definition.options,
    ];
}

function onChange(key: string, value: string | number | (string | number)[] | null): void {
    const normalizedValue = Array.isArray(value) ? value.join(',') : value == null ? '' : String(value);

    router.get(props.indexRoute, clean({...props.filters, [key]: normalizedValue, page: undefined}), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <SearchableSelect
        v-for="def in definitions"
        :id="`admin-filter-${def.key}`"
        :key="def.key"
        :clearable="false"
        :label="def.label"
        :model-value="filters[def.key] ?? def.defaultValue ?? ''"
        :options="filterOptions(def)"
        :searchable="false"
        class="min-w-36 flex-1 sm:flex-none"
        compact
        hide-label
        @update:model-value="onChange(def.key, $event)"
    />
</template>
