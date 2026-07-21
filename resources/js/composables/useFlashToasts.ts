import {usePage} from '@inertiajs/vue3';
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import type {AdminToastMessage} from '@/Components/Admin/AdminToasts.vue';
import type {FlashMessages} from '@/types';
import type {AdminToastPayload} from '@/utilities/toast';

export function useFlashToasts(eventName = 'app:toast') {
    const page = usePage();
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

    onMounted(() => window.addEventListener(eventName, handleToast));
    onBeforeUnmount(() => {
        window.removeEventListener(eventName, handleToast);
        toastTimers.forEach((timer) => window.clearTimeout(timer));
        toastTimers.clear();
    });

    return {
        dismissToast,
        pushToast,
        toasts,
    };
}
