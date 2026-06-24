<script lang="ts" setup>
import {ref} from 'vue';
import LearningSidebar from '@/Components/Learning/LearningSidebar.vue';
import LearningHeader from '@/Components/Learning/LearningHeader.vue';
import type {LearningCatalogStats} from '@/types/learning';
import {useUiStore} from '@/stores';

type ActiveItem = 'dashboard' | 'formations' | 'in-progress' | 'certified' | 'enterprise';

defineProps<{
    activeItem?: ActiveItem;
    catalogStats?: LearningCatalogStats | null;
}>();

const mobileSidebarOpen = ref(false);
const uiStore = useUiStore();
</script>

<template>
    <div class="min-h-screen bg-[#071525] text-slate-100">
        <LearningSidebar
            v-model:mobileSidebarOpen="mobileSidebarOpen"
            :active-item="activeItem"
            :catalog-stats="catalogStats"
        />

        <main
            :class="['min-h-screen transition-[padding] duration-200', uiStore.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-63']">
            <LearningHeader v-model:mobileSidebarOpen="mobileSidebarOpen">
                <template #breadcrumb>
                    <slot name="breadcrumb"/>
                </template>
                <template #header-actions>
                    <slot name="header-actions"/>
                </template>
            </LearningHeader>

            <slot/>
        </main>
    </div>
</template>
