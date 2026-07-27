<script lang="ts" setup>
import {computed} from 'vue';
import {Moon, Sun} from '@lucide/vue';
import {useUiStore} from '@/stores';

const uiStore = useUiStore();
const label = computed(() => (
    uiStore.theme === 'dark' ? 'Activer le thème clair' : 'Activer le thème sombre'
));
</script>

<template>
    <button
        :aria-label="label"
        :title="label"
        class="learning-control size-10"
        type="button"
        @click="uiStore.toggleTheme"
    >
        <Sun v-if="uiStore.theme === 'dark'" class="size-5" :stroke-width="1.7"/>
        <Moon v-else class="size-5" :stroke-width="1.7"/>
    </button>
</template>
