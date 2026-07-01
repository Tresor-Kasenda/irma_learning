<script lang="ts" setup>
import {router} from '@inertiajs/vue3';
import SelectField from '@/Components/Admin/Fields/SelectField.vue';

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

function onChange(key: string, value: string): void {
    router.get(props.indexRoute, clean({...props.filters, [key]: value, page: undefined}), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <SelectField
        v-for="def in definitions"
        :id="`admin-filter-${def.key}`"
        :key="def.key"
        :label="def.label"
        :model-value="filters[def.key] ?? def.defaultValue ?? ''"
        :options="filterOptions(def)"
        compact
        hide-label
        @update:model-value="onChange(def.key, $event)"
    />
</template>
