<div>
    <section class="flex sm:px-1 pt-1 lg:px-1 xl:px-3 xl:pt-3">
        <div class="relative xl:max-w-[160rem] mx-auto w-full flex">
            <div class="absolute top-0 left-0 inset-x-0 h-40 flex">
                <span class="flex w-60 h-36 bg-gradient-to-tr from-primary rounded-full blur-2xl opacity-65"></span>
            </div>
            <div class="w-full grid items-center relative bg-gradient-to-tr from-bg-light/60 to-bg-lighter/60">
                <div
                    class="mx-auto max-w-7xl w-full px-5 sm:px-10 py-20  flex flex-col lg:flex-row gap-6 items-center relative">
                    <div
                        class="relative max-w-lg lg:max-w-2xl space-y-7 flex flex-col w-full text-center lg:text-left">
                        <div
                            class="mb-14 text-sm pt-8 text-fg-subtitle flex items-center divide-x divide-fg/50 *:px-4 first:*:pl-0 last:*:pr-0 overflow-hidden max-w-full">
                            <a href="{{ route('formations') }}" wire:navigate aria-label="Lien vers la page principale">
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
                            Dans ces domaines professionnels en constante évolution, la formation continue n’est pas
                            un choix, mais une nécessité.
                        </p>
                        <a href="#advantages"
                           class="group relative w-max btn btn-sm sm:btn-md justify-center overflow-hidden rounded-md btn-solid bg-primary-50 text-primary-800 hover:text-primary-50">
                            <div class="flex items-center relative z-10">
                                <span>En savoir plus</span>
                                <div class="ml-1 transition duration-500 group-hover:rotate-[360deg]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                                    </svg>

                                </div>
                            </div>
                            <span data-btn-layer class=" before:bg-primary-600"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="advantages"
             class="my-32 mx-auto max-w-7xl scroll-mt-20 w-full px-5 sm:px-10 flex flex-col gap-16 md:flex-row">

        <div class="flex max-w-lg">
            <h2
                class="font-medium relative text-3xl md:text-4xl text-fg-title  before:absolute before:h-1 before:w-20 before:top-0 before:left-0 before:bg-primary-900 before:rounded-full pt-5">
                La formation continue : une nécessité, pas un choix
            </h2>
        </div>

        <div
            class="flex flex-1 flex-col gap-4 text-fg bg-bg/90 backdrop-blur-sm border border-border-light/60 shadow-xl shadow-bg-light/40 rounded-lg p-5 md:p-6 lg:p-10 xl:p-16 max-w-2xl md:max-w-none mx-auto md:mx-0">
            <p>
                L’iARM offre à ses membres des formations qui leur permettent d’acquérir et de maintenir des
                connaissances fondamentales en assurances, en risk management et en compliance ainsi que
                d'actualiser et d’enrichir leurs compétences tout au long de leur vie professionnelle.
            </p>
            <p>
                A travers des sessions de 1 à 5 jours dispensées en présentiel ou en distancielle, les membres
                apprennent les fondamentaux des sujets abordés, échangent avec l’animateur sur des problématiques
                concrètes et réalisent des cas pratiques.
            </p>
            <p>
                Ces programmes de formation spécifiques dispensés par des praticiens experts s’adressent aussi aux
                entreprises dont les collaborateurs doivent maîtriser ou se perfectionner sur des expertises
                précises.
            </p>
            <p>
                Le calendrier annuel des formations et publié au plus tard le 30 novembre. Les dates et les thèmes
                des formations peuvent être révisée durant l’année en fonction de l’évolution des priorités.
            </p>
        </div>
    </section>
</div>
