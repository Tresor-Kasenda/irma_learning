<script setup lang="ts">
defineProps<{
    socialAuthentication: {
        google: boolean;
        github: boolean;
    };
}>();
</script>

<template>
    <template v-if="socialAuthentication.google || socialAuthentication.github">
        <div class="relative py-1">
            <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-200" /></div>
            <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-500">ou</span></div>
        </div>

        <a
            v-if="socialAuthentication.google"
            :href="route('social.redirect', { provider: 'google' })"
            class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
        >
            <span class="text-base font-bold text-[#4285F4]">G</span>
            Continuer avec Google
        </a>

        <a
            v-if="socialAuthentication.github"
            :href="route('social.redirect', { provider: 'github' })"
            class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-[#24292f] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[#1b1f23]"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2C6.477 2 2 6.59 2 12.253c0 4.529 2.865 8.37 6.839 9.727.5.096.682-.22.682-.49 0-.242-.009-.88-.013-1.729-2.782.62-3.369-1.37-3.369-1.37-.455-1.186-1.11-1.502-1.11-1.502-.908-.638.069-.625.069-.625 1.003.072 1.531 1.057 1.531 1.057.892 1.568 2.341 1.115 2.91.853.091-.665.349-1.115.635-1.371-2.221-.26-4.555-1.139-4.555-5.066 0-1.119.389-2.034 1.029-2.751-.103-.261-.446-1.31.098-2.728 0 0 .84-.276 2.75 1.051A9.301 9.301 0 0112 6.916c.85.004 1.706.118 2.504.347 1.909-1.327 2.747-1.051 2.747-1.051.546 1.418.203 2.467.1 2.728.64.717 1.027 1.632 1.027 2.751 0 3.936-2.338 4.803-4.566 5.058.359.32.678.951.678 1.917 0 1.384-.012 2.5-.012 2.84 0 .272.18.59.688.489C19.138 20.62 22 16.78 22 12.253 22 6.59 17.523 2 12 2Z" />
            </svg>
            Continuer avec GitHub
        </a>
    </template>
</template>
