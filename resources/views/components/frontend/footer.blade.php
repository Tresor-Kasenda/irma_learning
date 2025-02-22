<button data-scroll-to-top
    class="fixed bottom-5 right-5 bg-primary-600 hover:bg-primary-700 text-white size-10 rounded-full shadow-lg transition-all duration-300 invisible opacity-0 fx-visible:visible fx-visible:opacity-100"
    aria-label="Scroll to top">
    ↑
</button>
<footer class="w-full mt-10 border-t border-border-light/80 pt-10">
    <div class="mx-auto max-w-7xl px-5 sm:px-10">
        <!--Grid-->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-10 py-10 max-sm:max-w-sm max-sm:mx-auto gap-y-8">
            <div class="col-span-full lg:col-span-3 lg:pr-16">
                <a href="{{ route('home-page') }}" wire:navigate aria-label="Page Accueil du Site Betterlife"
                    class="flex w-max gap-2 items-center">
                    <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                        class="h-14 w-auto">
                    <img src="{{ asset('images/irma-text-primary.svg') }}" alt="Irma Text" width="131" height="51.53"
                        class="h-12 w-auto">
                </a>
                <div
                    class="flex flex-col divide-y divide-border *:py-2 first:*:pt-0 last:*:pb-0 mt-8 p-4 bg-bg-lighter rounded-md">
                    <div class="flex items-center gap-3">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span class="text-sm text-fg">
                            269, Av. KASONGO NYEMBO, Q/ Baudouin, Lubumbashi, RD Congo
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span class="text-sm text-fg">
                            2, Avenue Père Boka, Commune de la Gombe, Kinshasa, RD Congo
                        </span>
                    </div>
                    <a href="mailto:communication@irmardc.org" class="flex items-center gap-3 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z" />
                        </svg>

                        <span class="text-sm text-fg group-hover:text-primary-600">
                            communication@irmardc.org
                        </span>
                    </a>
                    <a href="tel:+" class="flex items-center gap-3 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        <span class="text-sm text-fg group-hover:text-primary-600">
                            +243 819 742 171
                        </span>
                    </a>
                </div>
            </div>


            <div class="col-span-2 flex flex-col space-y-7">
                <h4 class="text-lg text-fg-title font-medium">Navigation</h4>
                <ul class="text-sm transition-all duration-500 grid grid-cols-2 gap-x-8 gap-y-6 text-fg">
                    <li class="flex">
                        <a href="{{ route('certifications') }}" wire:navigate class="hover:text-fg-title">
                            Certification
                        </a>
                    </li>
                    <li class="flex">
                        <a href="{{ route('formations-lists') }}" wire:navigate class="hover:text-fg-title">
                            Formation continue
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                </ul>
            </div>
            <!--End Col-->
            <div class="flex flex-col space-y-7">
                <h4 class="text-lg text-fg-title font-medium">Ressources</h4>
                <ul class="text-sm  transition-all duration-500 grid gap-y-6 text-fg">
                    <li class="flex">
                        <a href="{{ route('certifications') }}" wire:navigate class="hover:text-fg-title">
                            Certification
                        </a>
                    </li>
                    <li class="flex">
                        <a href="{{ route('formations-lists') }}" wire:navigate class="hover:text-fg-title">
                            Formation
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!--Grid-->
        <div class="py-7 border-t border-gray-200">
            <div class="flex items-center justify-center flex-col lg:justify-between lg:flex-row">
                <span class="text-sm text-gray-500 ">
                    © Irma <span data-current-year>{{ now()->format('Y') }}</span>,
                    All rights reserved.
                </span>
                <div class="flex mt-4 space-x-4 sm:justify-center lg:mt-0 ">
                    <a href="javascript:"
                        class="w-9 h-9 rounded-full bg-gray-700 flex justify-center items-center hover:bg-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                            fill="none">
                            <g id="Social Media">
                                <path id="Vector"
                                    d="M11.3214 8.93666L16.4919 3.05566H15.2667L10.7772 8.16205L7.1914 3.05566H3.05566L8.47803 10.7774L3.05566 16.9446H4.28097L9.022 11.552L12.8088 16.9446H16.9446L11.3211 8.93666H11.3214ZM9.64322 10.8455L9.09382 10.0765L4.72246 3.95821H6.60445L10.1322 8.8959L10.6816 9.66481L15.2672 16.083H13.3852L9.64322 10.8458V10.8455Z"
                                    fill="white" />
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
