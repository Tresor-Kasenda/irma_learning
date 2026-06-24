<script lang="ts" setup>
import {Head, Link} from '@inertiajs/vue3';

defineProps<{
    title?: string;
    whiteHeader?: boolean;
}>();

function scrollToTop() {
    window.scrollTo({top: 0, behavior: 'smooth'});
}
</script>

<template>
    <Head :title="title ?? 'Irma RDC'"/>

    <div class="min-h-screen bg-white font-sans text-gray-900">
        <header class="fixed top-0 z-50 w-full pt-5 px-0.5 sm:px-5 xl:pt-7 bg-white">
            <nav :class="whiteHeader
                     ? 'bg-white/10 backdrop-blur-sm'
                     : 'bg-white/10 border border-gray-200/30 shadow shadow-gray-100/40'"
                 class="mx-auto w-full max-w-7xl flex items-center justify-between gap-10 px-5 py-1.5 rounded-lg">

                <div class="lg:min-w-max flex">
                    <Link :href="route('home-page')" class="flex w-max gap-1">
                        <img :src="whiteHeader ? '/assets/irma-logo-base.svg' : '/images/irma-logo-base.svg'"
                             alt="logo Irma" class="h-14 w-auto" height="100" width="200"/>
                        <img :src="whiteHeader ? '/assets/irma-text.svg' : '/images/irma-text-primary.svg'"
                             alt="Irma Text" class="h-12 w-auto max-[500px]:hidden" height="51.53" width="131"/>
                    </Link>
                </div>

                <div class="lg:flex-1 lg:justify-start hidden lg:flex items-center gap-5">
                    <Link
                        :class="[
                            'py-2 text-sm ease-linear duration-100 inline-flex',
                            route().current('certifications') ? 'text-irma-primary font-semibold' : 'text-gray-700 hover:text-irma-primary'
                        ]"
                        :href="route('certifications')"
                    >Nos Formations
                    </Link>
                    <Link
                        :class="[
                            'py-2 text-sm ease-linear duration-100 inline-flex',
                            route().current('pages.pricings') ? 'text-irma-primary font-semibold' : 'text-gray-700 hover:text-irma-primary'
                        ]"
                        :href="route('pages.pricings')"
                    >Nos tarifs
                    </Link>
                </div>

                <div class="lg:min-w-max flex items-center gap-x-2">
                    <template v-if="!$page.props.auth?.user">
                        <Link :href="route('login')"
                              class="hidden sm:flex px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 rounded-lg transition">
                            Se connecter
                        </Link>
                        <Link :href="route('register')"
                              class="px-4 py-2 text-sm bg-primary-600 text-white bg-irma-primary rounded-lg hover:bg-primary-700 transition">
                            S'inscrire
                        </Link>
                    </template>
                    <template v-else>
                        <div
                            class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"/>
                            <span class="text-sm font-medium text-green-700">{{ $page.props.auth.user.name }}</span>
                        </div>
                        <Link :href="route('dashboard')"
                              class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"/>
                            </svg>
                            Mon Espace
                        </Link>
                    </template>

                    <button aria-label="Menu" class="flex lg:hidden p-2 border-l border-gray-200">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </nav>
        </header>

        <main>
            <slot/>
        </main>

        <button
            aria-label="Scroll to top"
            class="fixed bottom-5 right-5 bg-primary-600 hover:bg-primary-700 bg-irma-accent text-white size-10 rounded-full shadow-lg transition-all"
            @click="scrollToTop">
            ↑
        </button>

        <footer class="w-full mt-10 border-t border-gray-200/80 pt-10">
            <div class="mx-auto max-w-7xl px-5 sm:px-10">
                <div
                    class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-10 py-10 max-sm:max-w-sm max-sm:mx-auto gap-y-8">
                    <div class="col-span-full lg:col-span-3 lg:pr-16">
                        <Link :href="route('home-page')" class="flex w-max gap-2 items-center">
                            <img alt="logo Irma" class="h-14 w-auto" height="100" src="/images/irma-logo-base.svg"
                                 width="200"/>
                            <img alt="Irma Text" class="h-12 w-auto" height="51.53" src="/images/irma-text-primary.svg"
                                 width="131"/>
                        </Link>
                        <div
                            class="flex flex-col divide-y divide-gray-200 *:py-2 first:*:pt-0 last:*:pb-0 mt-8 p-4 bg-gray-50 rounded-md text-sm text-gray-600">
                            <div class="flex items-center gap-3">
                                <svg class="size-4 shrink-0" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                    <path
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <span>269, Av. KASONGO NYEMBO, Q/ Baudouin, Lubumbashi, RD Congo</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="size-4 shrink-0" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                    <path
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <span>2, Avenue Père Boka, Commune de la Gombe, Kinshasa, RD Congo</span>
                            </div>
                            <a class="flex items-center gap-3 hover:text-primary-600"
                               href="mailto:communication@irmardc.org">
                                <svg class="size-4" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <span>communication@irmardc.org</span>
                            </a>
                            <a class="flex items-center gap-3 hover:text-primary-600" href="tel:+243819742171">
                                <svg class="size-4" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                                <span>+243 819 742 171</span>
                            </a>
                        </div>
                    </div>

                    <div class="col-span-2 flex flex-col space-y-7">
                        <h4 class="text-lg text-gray-900 font-medium">Navigation</h4>
                        <ul class="text-sm grid grid-cols-2 gap-x-8 gap-y-6 text-gray-600">
                            <li>
                                <Link
                                    :class="[
                                        'hover:text-gray-900',
                                        route().current('certifications') ? 'text-irma-primary font-medium' : 'text-gray-600'
                                    ]"
                                    :href="route('certifications')"
                                >Certification
                                </Link>
                            </li>
                            <li><a class="hover:text-gray-900" href="#">A propos de nous</a></li>
                        </ul>
                    </div>

                    <div class="flex flex-col space-y-7">
                        <h4 class="text-lg text-gray-900 font-medium">Ressources</h4>
                        <ul class="text-sm grid gap-y-6 text-gray-600">
                            <li>
                                <Link
                                    :class="[
                                        'hover:text-gray-900',
                                        route().current('certifications') ? 'text-irma-primary font-medium' : 'text-gray-600'
                                    ]"
                                    :href="route('certifications')"
                                >Certification
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="py-7 border-t border-gray-200">
                    <div class="flex items-center justify-center flex-col lg:justify-between lg:flex-row">
                        <span class="text-sm text-gray-500">
                            © Irma {{ new Date().getFullYear() }}, All rights reserved.
                        </span>
                        <div class="flex mt-4 space-x-4 sm:justify-center lg:mt-0">
                            <a class="w-9 h-9 rounded-full bg-gray-700 flex justify-center items-center hover:bg-primary-600"
                               href="#">
                                <svg fill="none" height="20" viewBox="0 0 20 20" width="20"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M11.3214 8.93666L16.4919 3.05566H15.2667L10.7772 8.16205L7.1914 3.05566H3.05566L8.47803 10.7774L3.05566 16.9446H4.28097L9.022 11.552L12.8088 16.9446H16.9446L11.3211 8.93666H11.3214ZM9.64322 10.8455L9.09382 10.0765L4.72246 3.95821H6.60445L10.1322 8.8959L10.6816 9.66481L15.2672 16.083H13.3852L9.64322 10.8458V10.8455Z"
                                        fill="white"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
