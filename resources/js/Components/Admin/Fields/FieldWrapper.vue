<script lang="ts" setup>
defineProps<{
    label?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    forId?: string;
}>();
</script>

<template>
    <div>
        <label v-if="label" :for="forId" class="admin-muted mb-2 block text-xs font-semibold uppercase tracking-[0.08em]">
            {{ label }}<span v-if="required" class="text-[#ef477d]"> *</span>
        </label>
        <slot/>
        <p v-if="error" class="mt-1.5 text-xs text-rose-400">{{ error }}</p>
        <p v-else-if="hint" class="admin-muted mt-1.5 text-xs leading-5">{{ hint }}</p>
    </div>
</template>
