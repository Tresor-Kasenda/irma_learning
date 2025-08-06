<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('welcome', ['whiteHeader' => true])] class extends Component {};
?>

@php
    $tabs_items = [
        [
            'id' => 'certif-1',
            'text' => 'Risques Logistiques',
            'title' => 'Maîtriser l’Inattendu : Gestion des Risques Logistiques et Résilience',
        ],
        [
            'id' => 'certif-2',
            'text' => 'Certification 2',
            'title' => 'Certification title',
        ],
    ];
@endphp

<main>

    <x-header-with-image>
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 py-20 flex flex-col gap-6 items-center relative">
            <div
                class="relative max-w-lg lg:max-w-2xl space-y-6 flex flex-col items-center w-full text-center text-white">
                <div
                    class="text-sm pt-8 text-fg-subtitle flex items-center divide-x divide-white/50 *:px-4 first:*:pl-0 last:*:pr-0 overflow-hidden max-w-full mx-auto">
                    <a href="./" aria-label="Lien vers la page principale">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25">
                            </path>
                        </svg>
                    </a>
                    <div class="text-primary-300">
                        Tarifs
                    </div>
                </div>
                <h1 class="text-white text-4xl/tight md:text-5xl/tight xl:text-6xl/tight">
                    Certifications
                </h1>
                <p class="text-gray-200 max-w-lg mx-auto">
                    Validez vos compétences et boostez votre carrière grâce à nos certifications reconnues.
                </p>
            </div>
        </div>
    </x-header-with-image>

    <section x-data x-tabs class="px-5 sm:px-10 mt-10">
        <div data-tab-list-wrapper
            class="flex items-center mx-auto md:max-w-4xl w-full justify-center p-0.5 pb-0 rounded-xl bg-bg-light/30 border border-border">
            <ul data-tab-list role="tablist" class="flex justify-center items-center gap-4 flex-wrap">
                @foreach ($tabs_items as $item)
                    <li role="presentation" class="flex w-max">
                        <a href="#{{ $item['id'] }}" data-tabs-trigger data-target="{{ $item['id'] }}"
                            tabindex="0"
                            class="flex px-0.5 pt-2 pb-3 border-b-4 border-transparent fx-active:border-primary fx-active:text-primary text-sm text-fg-subtext">
                            {{ $item['text'] }} </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @foreach ($tabs_items as $item)
            <section role="tabpanel" tabindex="0" data-tab-panel
                data-state="{{ $loop->first ? 'active' : 'inactive' }}" id="{{ $item['id'] }}"
                aria-labelledby="{{ $item['id'] }}" class="fx-active:flex flex-col hidden">
                <div class="flex justify-center text-center flex-col items-center pt-10 mx-auto max-w-2xl">
                    <h3 class="text-xl sm:text-2xl md:text-3xl xl:text-4xl font-semibold text-fg-title">
                        {{ $item['title'] }}
                    </h3>

                </div>
                <div
                    class="w-full mx-auto mt-10 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:gap-y-0 lg:max-w-4xl lg:grid-cols-2">
                    <div
                        class="border rounded-lg rounded-t-lg border-border-light bg-white/60 p-8 ring-1 ring-gray-900/10 sm:mx-8 sm:rounded-b-none sm:p-10 lg:mx-0 lg:rounded-tr-none lg:rounded-bl-lg">
                        <h3 class="text-base/7 font-semibold text-primary-600">Individuel</h3>
                        <p class="mt-4 flex items-baseline gap-x-2">
                            <span class="text-5xl font-semibold tracking-tight text-fg-title">$150</span>
                            <span class="text-base text-gray-500">/Certification</span>
                        </p>
                        <p class="mt-6 text-base/7 text-fg">Idéal pour valider une compétence spécifique à un prix
                            accessible,
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
                        <h3 id="tier-enterprise" class="text-base/7 font-semibold text-primary-400">Équipe (3+
                            personnes)</h3>
                        <p class="mt-4 flex items-baseline gap-x-2">
                            <span class="text-5xl font-semibold tracking-tight text-white">$140</span>
                            <span class="text-base text-gray-400">/Certification</span>
                        </p>
                        <p class="mt-6 text-base/7 text-gray-300">Une formule complète conçue pour les équipes, avec
                            accompagnement et avantages supplémentaires.</p>
                        <ul role="list" class="mt-8 space-y-3 text-sm/6 text-gray-300 sm:mt-10">
                            <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Tous les avantages du
                                plan
                                Individuel</li>
                            <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Session de coaching
                                préparatoire (30 min)</li>
                            <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Support prioritaire
                                (réponse
                                sous 12h)
                            </li>
                            <li class="flex gap-x-3"><span class="folder-check mr-1 flex"></span> Repassage gratuit
                                inclus</li>
                        </ul>
                        <a href="#"
                            class="mt-8 font-medium sm:mt-10 btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group w-full justify-center text-center">
                            <span class="relative z-10">Commencer maintenant</span>
                            <span data-btn-layer class="before:bg-primary-800"></span>
                        </a>
                    </div>
                </div>
            </section>
        @endforeach


    </section>
</main>
