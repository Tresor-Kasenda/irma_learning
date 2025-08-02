<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('welcome')] class extends Component {};
?>

<main>
    <section class="flex sm:px-1 pt-1 lg:px-1 xl:px-3 xl:pt-3">
        <div class="relative xl:max-w-[160rem] mx-auto w-full flex">
            <div class="absolute top-0 left-0 inset-x-0 h-40 flex">
                <span class="flex w-60 h-36 bg-gradient-to-tr from-primary rounded-full blur-2xl opacity-65"></span>
            </div>
            <div class="w-full flex flex-col items-center relative bg-gradient-to-tr from-bg-light/60 to-bg-lighter/60">
                <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 py-20 flex flex-col gap-6 items-center relative">
                    <div class="relative max-w-lg lg:max-w-2xl space-y-6 flex flex-col items-center w-full text-center">
                        <div
                            class="text-sm pt-8 text-fg-subtitle flex items-center divide-x divide-fg/50 *:px-4 first:*:pl-0 last:*:pr-0 overflow-hidden max-w-full mx-auto">
                            <a href="./" aria-label="Lien vers la page principale">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25">
                                    </path>
                                </svg>
                            </a>
                            <div class="text-fg-subtext">
                                Tarifs
                            </div>
                        </div>
                        <h1 class="text-fg-title text-4xl/tight md:text-5xl/tight xl:text-6xl/tight">
                            Certifications
                        </h1>
                        <p class="text-fg max-w-lg mx-auto">
                            Validez vos compétences et boostez votre carrière grâce à nos certifications reconnues.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="">
        <div
            class="w-full mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:mt-20 sm:gap-y-0 lg:max-w-4xl lg:grid-cols-2">
            <!-- Plan Individuel -->
            <div
                class="border rounded-lg rounded-t-lg border-border-light bg-white/60 p-8 ring-1 ring-gray-900/10 sm:mx-8 sm:rounded-b-none sm:p-10 lg:mx-0 lg:rounded-tr-none lg:rounded-bl-lg">
                <h3 class="text-base/7 font-semibold text-primary-600">Individuel</h3>
                <p class="mt-4 flex items-baseline gap-x-2">
                    <span class="text-5xl font-semibold tracking-tight text-fg-title">$150</span>
                    <span class="text-base text-gray-500">/personne</span>
                </p>
                <p class="mt-6 text-base/7 text-fg">Idéal pour valider une compétence spécifique à un prix accessible,
                    seul(e).</p>
                <ul role="list" class="mt-8 space-y-3 text-sm/6 text-gray-600 sm:mt-10">
                    <li class="flex gap-x-3">
                        <span class="folder-check mr-1 flex"></span> Accès à l’examen en ligne
                    </li>
                    <li class="flex gap-x-3">
                        <span class="folder-check mr-1 flex"></span> Résultats instantanés
                    </li>
                    <li class="flex gap-x-3">
                        <span class="folder-check mr-1 flex"></span> Certificat numérique téléchargeable
                    </li>
                    <li class="flex gap-x-3">
                        <span class="folder-check mr-1 flex"></span> Support par e-mail sous 48h
                    </li>
                </ul>
                <a href="#"
                    class="mt-8 font-medium sm:mt-10 btn btn-sm sm:btn-md btn-solid bg-white border border-border text-gray-800 hover:text-white group w-full justify-center text-center">
                    <span class="relative z-10">Commencer maintenant</span>
                    <span data-btn-layer class="before:bg-primary-600"></span>
                </a>
            </div>

            <div
                class="relative border rounded-lg border-gray-800 bg-fg-title p-8 shadow-2xl ring-1 ring-gray-900/10 sm:p-10">
                <h3 id="tier-enterprise" class="text-base/7 font-semibold text-primary-400">Équipe (3+ personnes)</h3>
                <p class="mt-4 flex items-baseline gap-x-2">
                    <span class="text-5xl font-semibold tracking-tight text-white">$140</span>
                    <span class="text-base text-gray-400">/personne</span>
                </p>
                <p class="mt-6 text-base/7 text-gray-300">Une formule complète conçue pour les équipes, avec
                    accompagnement et avantages supplémentaires.</p>
                <ul role="list" class="mt-8 space-y-3 text-sm/6 text-gray-300 sm:mt-10">
                    <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Tous les avantages du plan
                        Individuel</li>
                    <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Session de coaching
                        préparatoire (30 min)</li>
                    <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Support prioritaire (réponse
                        sous 12h)</li>
                    <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Repassage gratuit inclus</li>
                </ul>
                <a href="#"
                    class="mt-8 font-medium sm:mt-10 btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group w-full justify-center text-center">
                    <span class="relative z-10">Commencer maintenant</span>
                    <span data-btn-layer class="before:bg-primary-800"></span>
                </a>
            </div>

        </div>
    </section>
</main>
