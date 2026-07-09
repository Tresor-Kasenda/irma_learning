<script lang="ts" setup>
import {ref} from 'vue';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/Admin/AdminTopbar.vue';
import AdminToasts from '@/Components/Admin/AdminToasts.vue';
import {useFlashToasts} from '@/composables/useFlashToasts';
import {useUiStore} from '@/stores';

const sidebarOpen = ref(false);
const uiStore = useUiStore();
const {dismissToast, toasts} = useFlashToasts('admin:toast');
</script>

<template>
    <div class="admin-page min-h-screen">
        <AdminSidebar v-model:open="sidebarOpen"/>

        <div
            :class="uiStore.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
            class="min-h-screen min-w-0 max-w-full overflow-x-hidden transition-[padding] duration-200"
        >
            <AdminTopbar @toggle="sidebarOpen = !sidebarOpen">
                <template #breadcrumb>
                    <slot name="breadcrumb"/>
                </template>
                <template #header-actions>
                    <slot name="header-actions"/>
                </template>
            </AdminTopbar>

            <AdminToasts :toasts="toasts" @dismiss="dismissToast"/>

            <main class="min-w-0 max-w-full overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <slot/>
            </main>
        </div>
    </div>
</template>
