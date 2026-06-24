<script lang="ts" setup>
import LearningLayout from '@/Layouts/LearningLayout.vue';
import type {LearningCatalogStats} from '@/types/learning';

type ActiveItem = 'dashboard' | 'formations' | 'in-progress' | 'certified' | 'enterprise';

withDefaults(
    defineProps<{
        activeItem: ActiveItem;
        catalogStats?: LearningCatalogStats | null;
    }>(),
    {
        catalogStats: null,
    },
);
</script>

<template>
    <LearningLayout :active-item="activeItem" :catalog-stats="catalogStats">
        <template #breadcrumb>
            <slot name="breadcrumb"/>
        </template>
        <template #header-actions>
            <slot name="header-actions"/>
        </template>

        <slot/>
    </LearningLayout>
</template>
