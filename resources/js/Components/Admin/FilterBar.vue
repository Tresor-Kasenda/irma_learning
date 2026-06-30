<script lang="ts" setup>
import {router} from '@inertiajs/vue3';

export interface FilterDef {
    key: string;
    label: string;
    options: { value: string; label: string }[];
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

function onChange(key: string, event: Event): void {
    const value = (event.target as HTMLSelectElement).value;
    router.get(props.indexRoute, clean({...props.filters, [key]: value, page: undefined}), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <label v-for="def in definitions" :key="def.key" class="relative">
        <span class="sr-only">{{ def.label }}</span>
        <select
            :value="filters[def.key] ?? ''"
            class="h-10 rounded-lg border border-slate-200 bg-white pl-3 pr-8 text-sm text-slate-600 outline-none focus:border-[#bf045b]"
            @change="onChange(def.key, $event)"
        >
            <option value="">{{ def.label }} : tous</option>
            <option v-for="opt in def.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
    </label>
</template>
