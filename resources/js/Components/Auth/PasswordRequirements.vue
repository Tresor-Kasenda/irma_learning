<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    password: string;
    dark?: boolean;
}>(), {
    dark: false,
});

const requirements = computed(() => [
    { label: 'Au moins 12 caractères', fulfilled: props.password.length >= 12 },
    { label: 'Une lettre minuscule', fulfilled: /[a-z]/.test(props.password) },
    { label: 'Une lettre majuscule', fulfilled: /[A-Z]/.test(props.password) },
    { label: 'Un chiffre', fulfilled: /\d/.test(props.password) },
    { label: 'Un symbole', fulfilled: /[^A-Za-z0-9]/.test(props.password) },
]);
</script>

<template>
    <ul
        aria-label="Exigences du mot de passe"
        class="grid gap-1 text-xs sm:grid-cols-2"
        :class="dark ? 'text-slate-400' : 'text-gray-500'"
    >
        <li v-for="requirement in requirements" :key="requirement.label" class="flex items-center gap-1.5">
            <span
                aria-hidden="true"
                class="size-1.5 rounded-full"
                :class="requirement.fulfilled ? 'bg-emerald-500' : (dark ? 'bg-slate-600' : 'bg-gray-300')"
            />
            <span :class="requirement.fulfilled ? 'text-emerald-600 dark:text-emerald-400' : ''">
                {{ requirement.label }}
            </span>
        </li>
    </ul>
</template>
