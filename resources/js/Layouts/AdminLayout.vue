<script lang="ts" setup>
import {usePage} from '@inertiajs/vue3';
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/Admin/AdminTopbar.vue';
import AdminToasts, {type AdminToastMessage} from '@/Components/Admin/AdminToasts.vue';
import {useUiStore} from '@/stores';
import type {FlashMessages} from '@/types';
import type {AdminToastPayload} from '@/utilities/toast';

const sidebarOpen = ref(false);
const page = usePage();
const uiStore = useUiStore();

const flash = computed<FlashMessages>(() => page.props.flash ?? {});
const toasts = ref<AdminToastMessage[]>([]);
const toastTimers = new Map<number, ReturnType<typeof window.setTimeout>>();
let nextToastId = 1;

function dismissToast(id: number): void {
    const timer = toastTimers.get(id);
    if (timer) {
        window.clearTimeout(timer);
        toastTimers.delete(id);
    }
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function pushToast(payload: AdminToastPayload): void {
    const id = nextToastId++;
    toasts.value.push({...payload, id});
    toastTimers.set(id, window.setTimeout(() => dismissToast(id), 5000));
}

function handleToast(event: Event): void {
    pushToast((event as CustomEvent<AdminToastPayload>).detail);
}

watch(
    flash,
    (value) => {
        if (value?.success) {
            pushToast({type: 'success', message: value.success});
        } else if (value?.error) {
            pushToast({type: 'error', message: value.error});
        } else if (value?.info) {
            pushToast({type: 'info', message: value.info});
        }
    },
    {immediate: true, deep: true},
);

onMounted(() => window.addEventListener('admin:toast', handleToast));
onBeforeUnmount(() => {
    window.removeEventListener('admin:toast', handleToast);
    toastTimers.forEach((timer) => window.clearTimeout(timer));
    toastTimers.clear();
});
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
