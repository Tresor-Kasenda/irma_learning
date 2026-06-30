<script lang="ts" setup>
import {usePage} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/Admin/AdminTopbar.vue';
import type {FlashMessages} from '@/types';

const sidebarOpen = ref(false);
const page = usePage();

const flash = computed<FlashMessages>(() => page.props.flash ?? {});
const banner = ref<{ type: 'success' | 'error' | 'info'; message: string } | null>(null);

watch(
    flash,
    (value) => {
        if (value?.success) {
            banner.value = {type: 'success', message: value.success};
        } else if (value?.error) {
            banner.value = {type: 'error', message: value.error};
        } else if (value?.info) {
            banner.value = {type: 'info', message: value.info};
        }
        if (banner.value) {
            window.setTimeout(() => (banner.value = null), 5000);
        }
    },
    {immediate: true, deep: true},
);

const bannerClass = computed(() => ({
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    error: 'border-red-200 bg-red-50 text-red-800',
    info: 'border-sky-200 bg-sky-50 text-sky-800',
}[banner.value?.type ?? 'info']));
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800">
        <AdminSidebar v-model:open="sidebarOpen"/>

        <div class="lg:pl-64">
            <AdminTopbar @toggle="sidebarOpen = !sidebarOpen">
                <template #breadcrumb>
                    <slot name="breadcrumb"/>
                </template>
            </AdminTopbar>

            <Transition
                enter-active-class="transition duration-150"
                enter-from-class="-translate-y-2 opacity-0"
                leave-active-class="transition duration-150"
                leave-to-class="-translate-y-2 opacity-0"
            >
                <div v-if="banner" :class="bannerClass" class="mx-4 mt-4 rounded-lg border px-4 py-3 text-sm sm:mx-6 lg:mx-8">
                    {{ banner.message }}
                </div>
            </Transition>

            <main class="p-4 sm:p-6 lg:p-8">
                <slot/>
            </main>
        </div>
    </div>
</template>
