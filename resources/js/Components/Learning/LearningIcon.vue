<script setup lang="ts">
withDefaults(
    defineProps<{
        name: string;
        alt?: string;
    }>(),
    {
        alt: '',
    },
);
</script>

<template>
    <img :src="`/icons/heroicons/${name}.svg`" :alt="alt" />
</template>

