<script lang="ts" setup>
defineProps<{
    count: number;
}>();
</script>

<template>
    <Transition
        enter-active-class="transition duration-150"
        enter-from-class="-translate-y-1 opacity-0"
        leave-active-class="transition duration-150"
        leave-to-class="-translate-y-1 opacity-0"
    >
        <div
            v-if="count > 0"
            class="flex flex-wrap items-center gap-3 border-b border-[#a23362]/40 bg-[#7d254a]/15 px-4 py-2.5"
        >
            <span class="text-sm font-medium text-[#7d254a] dark:text-rose-200">{{ count }} sélectionné(s)</span>
            <div class="flex flex-wrap items-center gap-2">
                <slot/>
            </div>
        </div>
    </Transition>
</template>
