<div>
    <section class="flex sm:px-1 pt-1 lg:px-1 xl:px-3 xl:pt-3">
        <div class="relative xl:max-w-[160rem] mx-auto w-full flex">
            <div class="absolute top-0 left-0 inset-x-0 h-40 flex">
                <span class="flex w-60 h-36 bg-gradient-to-tr from-primary rounded-full blur-2xl opacity-65"></span>
            </div>
            <div class="absolute inset-x-0  bg-gradient-to-tr from-bg-light/60 to-bg-lighter/60 top-0 h-4/5"></div>
            <div class="w-full grid items-center relative pt-4">
                <div
                    class="mx-auto max-w-7xl w-full px-5 sm:px-10 lg:px-5 py-20  flex flex-col lg:flex-row gap-16 items-start relative">
                    <div
                        class="relative max-w-lg lg:max-w-none space-y-7 flex flex-col w-full text-center lg:text-left lg:w-1/2 lg:flex-1">
                        <div
                            class=" text-sm pt-8 text-fg-subtitle flex items-center divide-x divide-fg/50 *:px-4 first:*:pl-0 last:*:pr-0 overflow-hidden max-w-full">
                            <a href="{{ route('home-page') }}" wire:navigate aria-label="Lien vers la page principale">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                </svg>
                            </a>
                            <div class="text-fg-subtext">
                                Formation continue
                            </div>
                        </div>
                        <h1 class="text-fg-title text-4xl/tight md:text-5xl/tight xl:text-6xl/tight">
                            Formation Continue
                        </h1>
                        <p class="text-fg max-w-lg">
                            Le savoir en mouvement, votre carrière en progression !
                        </p>
                    </div>
                    <div
                        class="flex lg:w-1/2 lg:flex-1  lg:min-h-[440px] border border-dashed rounded-md bg-bg shadow-sm shadow-gray-100/20">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col gap-16">
        <h2 class="font-medium relative text-3xl md:text-4xl text-fg-title max-w-xl mx-auto text-center">
            Nos services principaux
        </h2>
        <div data-ui-accordion data-accordion-type="single"
             class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 w-full relative">
            <div data-accordion-item data-accordion-value="item1"
                 class="h-max transition duration-500 fx-open:border-primary bg-bg/50 backdrop-blur-sm border rounded-lg border-border-light">
                <button data-accordion-trigger
                        class="group inline-flex items-center justify-between text-left w-full transition duration-500 hover:bg-bg-high/20 rounded-lg p-4 ">
                    <h5 class="text-fg-title font-medium">Objectifs de la Formation Continue</h5>
                    <div class="size-3 flex relative ease-linear duration-300 group-aria-expanded:rotate-45">
                        <span class="flex h-0.5 w-full absolute top-1/2 -translate-y-1/2 bg-fg-title/60"></span>
                        <span class="flex h-full w-0.5 absolute left-1/2 -translate-x-1/2 bg-fg-title/60"></span>
                    </div>
                </button>
                <div data-accordion-content
                     class="ease-linear transition-all max-h-0 fx-close:max-h-0 overflow-hidden">
                    <div class="p-4 border-t border-border-light">
                        <p class="text-fg-subtext">
                            La formation continue repose sur des programmes courts permettant aux professionnels
                            certifiés d'actualiser et d'améliorer leurs compétences tout au long de leur vie
                            professionnelle.
                        </p>
                    </div>
                </div>
            </div>
            <div data-accordion-item data-accordion-value="item2"
                 class="h-max lg:col-span-2 transition duration-500 fx-open:border-primary bg-bg/50 backdrop-blur-sm border rounded-lg border-border-light">
                <button data-accordion-trigger
                        class="group inline-flex items-center justify-between text-left w-full transition duration-500 hover:bg-bg-high/20 rounded-lg p-4 ">
                    <h5 class="text-fg-title font-medium">Exigences de Formation Continue</h5>
                    <div class="size-3 flex relative ease-linear duration-300 group-aria-expanded:rotate-45">
                        <span class="flex h-0.5 w-full absolute top-1/2 -translate-y-1/2 bg-fg-title/60"></span>
                        <span class="flex h-full w-0.5 absolute left-1/2 -translate-x-1/2 bg-fg-title/60"></span>
                    </div>
                </button>
                <div data-accordion-content
                     class="ease-linear transition-all max-h-0 fx-close:max-h-0 overflow-hidden">
                    <div class="p-4 border-t border-border-light grid lg:grid-cols-2 gap-12">
                        <ul class="list-disc list-outside pl-5 text-fg-subtext text-sm space-y-2">
                            <li>
                                Chaque professionnel certifié doit cumuler au moins 20 Unités de Formation Continue
                                (UFC) par période de référence de 12 mois, allant du 1er janvier au 31 décembre.
                            </li>
                            <li>
                                Les UFC accumulées sont enregistrées dans un Compte Personnel de Formation (CPF),
                                permettant un suivi et une gestion optimisée du développement professionnel.
                            </li>
                        </ul>
                        <div class="flex flex-col text-sm divide-y divide-border-light">
                            <div class="pb-6">
                                <span class="font-semibold text-fg">
                                    Calcul des UFC
                                </span>
                                <img src="/calcul-ufc.png" width="600" alt="image calcul ufc"
                                     class="mt-4 w-full h-auto">
                            </div>
                            <div class="pt-6">
                                <span class="font-semibold text-fg">
                                    Enregistrement des UFC
                                </span>
                                <p class="text-fg-subtext mt-4">
                                    L’iRMA a mis en ligne un outil électronique accessible dans l’onglet « Mes
                                    formations » dans les comptes des membres. Son usage permet l’enregistrement par les
                                    membres et la conservation des renseignements sur les formations suivies.
                                </p>
                                <p class="text-fg-subtext mt-1">
                                    Un membre qui obtient plus d’UFC qu’exigé pour la période de référence en cours se
                                    voit automatiquement transférer un maximum de 5 UFC à la période subséquente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="certifications" class="my-32 scroll-mt-20 w-full flex flex-col gap-16">
        <div
            class="mx-auto max-w-7xl w-full px-5 sm:px-10 border border-border-lighter bg-bg/30 rounded-md h-14 sticky top-2 z-20 backdrop-blur-sm flex justify-between items-center">
            <div class="text-sm md:text-base text-fg-subtext">
                Nos formations
            </div>
            <div class="flex gap-3 items-center">
                <div class="relative w-[230px] sm:w-80">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                         class="size-4 text-fg-subtext absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                         viewBox="0 0 256 256">
                        <path
                            d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z">
                        </path>
                    </svg>
                    <input type="text" wire:model.live="search" placeholder="Rechercher"
                           class="ui-form-input px-4 h-9 rounded-md peer w-full ps-9"/>
                </div>
            </div>
        </div>
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 grid">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            </div>
        </div>
    </section>
</div>
