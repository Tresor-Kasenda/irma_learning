<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div class="absolute inset-x-0 -top-2 h-56 bg-bg-lighter rounded-b-xl"></div>
    <div class="px-4 sm:px-10 lg:px-5 xl:px-8 xl:max-w-[88rem] w-full mx-auto">
        <div data-results-tabs
            class="w-full bg-bg shadow-md shadow-gray-100/30 border border-border/60 p-4 sm:p-10 rounded-md relative">
            <div data-tab-list-wrapper class="w-full border-b border-border-light">
                <ul data-tab-list class="flex items-center gap-5">
                    <li class="flex relative text-fg">
                        <a href="#" data-tabs-trigger data-target="tab1" tabindex="0" aria-label=""
                            class="pb-3 border-b-2 border-transparent fx-active:border-primary fx-active:text-primary">
                            Evalutations
                        </a>
                    </li>
                    <li class="flex relative text-fg">
                        <a href="#" data-tabs-trigger data-target="tab2" tabindex="0" aria-label=""
                            class="pb-3 border-b-2 border-transparent fx-active:border-primary fx-active:text-primary">
                            Resultats
                        </a>
                    </li>
                </ul>
            </div>
            <div data-panels-container>
                <div role="tabpanel" tabindex="0" data-tab-panel data-state="active" id="tab1" class="pt-6">
                    <div class="border rounded-md border-border-light p-0.5">
                        <div class="py-3 px-4 rounded bg-bg-light flex items-center gap-4 text-fg-title">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                    fill="currentColor" class="size-6 flex text-fg-title" viewBox="0 0 256 256">
                                    <path
                                        d="M232,48H160a40,40,0,0,0-32,16A40,40,0,0,0,96,48H24a8,8,0,0,0-8,8V200a8,8,0,0,0,8,8H96a24,24,0,0,1,24,24,8,8,0,0,0,16,0,24,24,0,0,1,24-24h72a8,8,0,0,0,8-8V56A8,8,0,0,0,232,48ZM96,192H32V64H96a24,24,0,0,1,24,24V200A39.81,39.81,0,0,0,96,192Zm128,0H160a39.81,39.81,0,0,0-24,8V88a24,24,0,0,1,24-24h64ZM160,88h40a8,8,0,0,1,0,16H160a8,8,0,0,1,0-16Zm48,40a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,128Zm0,32a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,160Z">
                                    </path>
                                </svg>
                            </span>
                            <div>
                                Titre master classe
                            </div>
                        </div>
                        <div class="px-4 flex w-full">
                            <div class="py-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
                                <div
                                    class="bg-bg px-6 py-4 rounded-md shadow-md shadow-gray-100 border border-border/50">
                                    <div class="flex flex-col h-full">
                                        <div>
                                            <h3 class="text-lg font-medium text-fg-title">Chapitre 1: Algèbre
                                                Linéaire</h3>
                                            <p class="mt-1 text-sm text-gray-500">Points obtenus: <span
                                                    class="font-semibold text-emerald-600">18/20</span></p>
                                        </div>
                                        <div class="mt-4 relative group">
                                            <button class="flex items-center text-blue-600 hover:text-blue-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                    fill="currentColor" class="h-5 w-5 mr-2" viewBox="0 0 256 256">
                                                    <path
                                                        d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm16-40a8,8,0,0,1-8,8,16,16,0,0,1-16-16V128a8,8,0,0,1,0-16,16,16,0,0,1,16,16v40A8,8,0,0,1,144,176ZM112,84a12,12,0,1,1,12,12A12,12,0,0,1,112,84Z">
                                                    </path>
                                                </svg>
                                                <span class="text-sm">Voir commentaires</span>
                                            </button>
                                            <div
                                                class="absolute left-0 w-64 sm:w-full mt-2 p-4 bg-primary-50 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible group-active:visible group-active:opacity-100 group-focus-within:opacity-100 group-focus-within:visible transition-all duration-300 z-10">
                                                <p class="text-sm text-fg">Excellent travail! Maîtrise
                                                    exceptionnelle des concepts fondamentaux.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" tabindex="0" data-tab-panel data-state="active" id="tab2" class="pt-6">

                </div>
            </div>
        </div>
    </div>
</div>
