<script lang="ts" setup>
defineProps<{
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
}>();
</script>

<template>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">
            {{ label }}<span v-if="required" class="text-[#bf045b]"> *</span>
        </label>
        <slot/>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-400">{{ hint }}</p>
    </div>
</template>
