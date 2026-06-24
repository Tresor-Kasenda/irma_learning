<script lang="ts" setup>

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
    <img :alt="alt" :src="`/icons/heroicons/${name}.svg`"/>
</template>

