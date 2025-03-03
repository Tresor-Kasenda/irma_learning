<?php

use Livewire\Volt\Component;
use App\Enums\MasterClassResourceEnum;

new class extends Component {
    //
}; ?>

<div>
    <div aria-hidden="true" data-overlay-slid-chapter
        class="fixed inset-0 bg-fg-title/50 z-[70] backdrop-blur-sm lg:hidden lg:invisible invisible opacity-0 fx-open:opacity-100 fx-open:visible">
    </div>
    <div class="max-w-[95rem] grid lg:grid-cols-[350px_minmax(0,1fr)] w-full mx-auto">
        <aside class="side-nav-course lg:border-r border-border">
            <div class="h-16 border-b border-border px-4 flex items-center justify-between sticky lg:static top-0 bg-bg">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate aria-label="Retour dashboard"
                        class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 flex items-center justify-center rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                            class="size-5" viewBox="0 0 256 256">
                            <path
                                d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z">
                            </path>
                        </svg>
                    </a>
                    <a href="{{ route('profile') }}" wire:navigate aria-label="Retour dashboard"
                        class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 hidden sm:flex items-center justify-center rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                            class="size-5" viewBox="0 0 256 256">
                            <path
                                d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z">
                            </path>
                        </svg>
                    </a>
                    <a href="{{ route('profile') }}" wire:navigate aria-label="Info"
                        class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 hidden sm:flex items-center justify-center rounded-md">

                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                            class="size-5" viewBox="0 0 256 256">
                            <path
                                d="M225.86,102.82c-3.77-3.94-7.67-8-9.14-11.57-1.36-3.27-1.44-8.69-1.52-13.94-.15-9.76-.31-20.82-8-28.51s-18.75-7.85-28.51-8c-5.25-.08-10.67-.16-13.94-1.52-3.56-1.47-7.63-5.37-11.57-9.14C146.28,23.51,138.44,16,128,16s-18.27,7.51-25.18,14.14c-3.94,3.77-8,7.67-11.57,9.14C88,40.64,82.56,40.72,77.31,40.8c-9.76.15-20.82.31-28.51,8S41,67.55,40.8,77.31c-.08,5.25-.16,10.67-1.52,13.94-1.47,3.56-5.37,7.63-9.14,11.57C23.51,109.72,16,117.56,16,128s7.51,18.27,14.14,25.18c3.77,3.94,7.67,8,9.14,11.57,1.36,3.27,1.44,8.69,1.52,13.94.15,9.76.31,20.82,8,28.51s18.75,7.85,28.51,8c5.25.08,10.67.16,13.94,1.52,3.56,1.47,7.63,5.37,11.57,9.14C109.72,232.49,117.56,240,128,240s18.27-7.51,25.18-14.14c3.94-3.77,8-7.67,11.57-9.14,3.27-1.36,8.69-1.44,13.94-1.52,9.76-.15,20.82-.31,28.51-8s7.85-18.75,8-28.51c.08-5.25.16-10.67,1.52-13.94,1.47-3.56,5.37-7.63,9.14-11.57C232.49,146.28,240,138.44,240,128S232.49,109.73,225.86,102.82Zm-11.55,39.29c-4.79,5-9.75,10.17-12.38,16.52-2.52,6.1-2.63,13.07-2.73,19.82-.1,7-.21,14.33-3.32,17.43s-10.39,3.22-17.43,3.32c-6.75.1-13.72.21-19.82,2.73-6.35,2.63-11.52,7.59-16.52,12.38S132,224,128,224s-9.15-4.92-14.11-9.69-10.17-9.75-16.52-12.38c-6.1-2.52-13.07-2.63-19.82-2.73-7-.1-14.33-.21-17.43-3.32s-3.22-10.39-3.32-17.43c-.1-6.75-.21-13.72-2.73-19.82-2.63-6.35-7.59-11.52-12.38-16.52S32,132,32,128s4.92-9.15,9.69-14.11,9.75-10.17,12.38-16.52c2.52-6.1,2.63-13.07,2.73-19.82.1-7,.21-14.33,3.32-17.43S70.51,56.9,77.55,56.8c6.75-.1,13.72-.21,19.82-2.73,6.35-2.63,11.52-7.59,16.52-12.38S124,32,128,32s9.15,4.92,14.11,9.69,10.17,9.75,16.52,12.38c6.1,2.52,13.07,2.63,19.82,2.73,7,.1,14.33.21,17.43,3.32s3.22,10.39,3.32,17.43c.1,6.75.21,13.72,2.73,19.82,2.63,6.35,7.59,11.52,12.38,16.52S224,124,224,128,219.08,137.15,214.31,142.11ZM120,136V80a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm20,36a12,12,0,1,1-12-12A12,12,0,0,1,140,172Z">
                            </path>
                        </svg>
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex flex-col items-center gap-2">
                        {{-- <div class="text-sm text-gray-600">Progress: {{ $this->getProgressPercentage() }}%</div>
                        <div class="w-20 h-2 bg-bg-high rounded-full">
                            <div class="h-full bg-primary rounded-full"
                                style="width: {{ $this->getProgressPercentage() }}%;"></div>
                        </div> --}}
                    </div>
                    <div class="flex lg:hidden">
                        <button aria-label="Afficher le menu" data-trigger-slid-chapter
                            class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 flex items-center justify-center rounded-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                class="size-5" viewBox="0 0 256 256">
                                <path
                                    d="M88,64a8,8,0,0,1,8-8H216a8,8,0,0,1,0,16H96A8,8,0,0,1,88,64Zm128,56H96a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Zm0,64H96a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16ZM56,56H40a8,8,0,0,0,0,16H56a8,8,0,0,0,0-16Zm0,64H40a8,8,0,0,0,0,16H56a8,8,0,0,0,0-16Zm0,64H40a8,8,0,0,0,0,16H56a8,8,0,0,0,0-16Z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="ui-side-chapter-nav flex flex-col" data-slid-chapter>
                <div class="p-4 space-y-4 overflow-y-auto overflow-hidden flex-1">
                    <div class=" bg-bg-lighter p-2 space-y-3">
                        {{-- @foreach ($masterClass->chapters as $chapter)
                            <button wire:click.prevent="setActiveChapter({{ $chapter->id }})"
                                wire:key="{{ $chapter->id }}" @class([
                                    'course-chater-item w-full text-left p-4 rounded-md transition-all',
                                    'active' => $activeChapter?->id === $chapter->id,
                                    'disabled cursor-not-allowed' =>
                                        !$chapter->isCompleted() &&
                                        $loop->index > 0 &&
                                        !$masterClass->chapters[$loop->index - 1]->isCompleted() &&
                                        !$this->canAccessChapter($chapter),
                                    'hover:bg-gray-50' => $activeChapter?->id !== $chapter->id,
                                ])
                                @if (!$this->canAccessChapter($chapter)) disabled @endif>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" @class([
                                        'h-4 w-4',
                                        'text-primary' => $chapter->isCompleted(),
                                        'text-success' => $activeChapter?->id === $chapter->id,
                                        'text-gray-400' =>
                                            !$chapter->isCompleted() && $activeChapter?->id !== $chapter->id,
                                    ])>
                                    @if ($chapter->isCompleted())
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <path d="m9 11 3 3L22 4"></path>
                                    @else
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    @endif
                                </svg>
                                <span @class([
                                    'text-sm flex items-center gap-2',
                                    'text-fg-title font-medium' => $activeChapter?->id === $chapter->id,
                                    'text-gray-600' => $activeChapter?->id !== $chapter->id,
                                ])>
                                    {{ str($chapter->title)->ucfirst() }}
                                    @if (!$this->canAccessChapter($chapter))
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-gray-400"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        @endforeach --}}
                    </div>
                </div>
                <div class="h-16 flex items-center w-full p-4 border-t border-gray-200 bg-bg">
                    {{-- @if ($masterClass->chapters->every(fn($chapter) => $chapter->isCompleted()))
                        <button
                            class="w-full bg-primary-600 text-white btn btn-md rounded-md flex items-center justify-center gap-2 hover:bg-primary-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-award h-5 w-5">
                                <circle cx="12" cy="8" r="6"></circle>
                                <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
                            </svg>
                            Voir mon certificat
                        </button>
                    @endif --}}
                </div>
            </div>
        </aside>
        <main class="min-h-screen bg-bg py-8 px-4 sm:px-10 lg:px-5 xl:px-8">
            <div class="max-w-4xl mx-auto font-helvetica flex flex-col divide-y divide-border">
                <!-- Section: Informations Générales -->
                <section class="pb-8 flex flex-col">
                    <h2 class="text-2xl font-semibold text-primary-700 mb-6">Informations Générales</h2>

                    <div class="mb-8">
                        <h3 class="text-xl font-medium text-fg-subtitle mb-3">Objectif de l'examen</h3>
                        <p class="text-fg mb-3">L'examen global de certification en gestion des risques RH vise à
                            évaluer votre maîtrise approfondie des concepts, méthodes et outils permettant d'identifier,
                            évaluer, traiter et suivre les risques liés aux ressources humaines dans un cadre
                            organisationnel.</p>
                        <p class="text-fg">Il s'agit d'un examen avancé, conçu pour tester vos compétences
                            analytiques et décisionnelles, ainsi que votre capacité à appliquer ces connaissances à des
                            situations réelles.</p>
                    </div>

                    <div>
                        <h3 class="text-xl font-medium text-fg-subtitle mb-3">Structure de l'examen</h3>
                        <p class="text-fg mb-4">L'examen est composé de 50 questions, réparties en 7 sections,
                            couvrant l'ensemble du cycle de gestion des risques RH.</p>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border-y border-border rounded-md">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="py-3 px-4 text-left text-primary-800 font-semibold border-b">Thème
                                        </th>
                                        <th class="py-3 px-4 text-center text-primary-800 font-semibold border-b">Nombre
                                            de Questions</th>
                                        <th class="py-3 px-4 text-center text-primary-800 font-semibold border-b">Point
                                            par Question</th>
                                        <th class="py-3 px-4 text-center text-primary-800 font-semibold border-b">Total
                                            Points</th>
                                        <th class="py-3 px-4 text-left text-primary-800 font-semibold border-b">Format
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-3 px-4 border-b text-fg">1. Le Risque et l'Entreprise
                                            (Concept du Risque, Termes Associés, Catégorisation des Risques)</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">10</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">2</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">20</td>
                                        <td class="py-3 px-4 border-b text-fg">QCM classiques</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="py-3 px-4 border-b text-fg">2. Le Contexte du Risque</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">10</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">2</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">20</td>
                                        <td class="py-3 px-4 border-b text-fg">QCM analytiques</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 border-b text-fg">3. Les Critères de la Gestion des
                                            Risques</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">5</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">2</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">10</td>
                                        <td class="py-3 px-4 border-b text-fg">QCM analytiques</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="py-3 px-4 border-b text-fg">4. Identification des Risques RH
                                        </td>
                                        <td class="py-3 px-4 border-b text-fg text-center">5</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">3</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">15</td>
                                        <td class="py-3 px-4 border-b text-fg">Etudes de cas avec QCM</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 border-b text-fg">5. Evaluation des Risques RH</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">5</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">2</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">10</td>
                                        <td class="py-3 px-4 border-b text-fg">QCM analytiques</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="py-3 px-4 border-b text-fg">6. Traitement des Risques RH</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">10</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">5</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">25</td>
                                        <td class="py-3 px-4 border-b text-fg">Scénarios pratiques avec QCM</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 border-b text-fg">7. Mise en œuvre et Suivi</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">5</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">2</td>
                                        <td class="py-3 px-4 border-b text-fg text-center">10</td>
                                        <td class="py-3 px-4 border-b text-fg">QCM analytiques</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="my-4 flex flex-wrap gap-4 p-4 border border-border-light rounded-md">
                            <div class="flex flex-col max-w-xl w-full">
                                <div class="flex items-center w-full">
                                    <span class="font-medium text-primary-800 w-56 flex">Total des questions:</span> 
                                    <span>50</span>
                                </div>
                                <div class="flex items-center w-full">
                                    <span class="font-medium text-primary-800 w-56 flex">Total des points:</span>
                                    <span> 100</span>
                                </div>
                                <div class="flex items-center w-full">
                                    <span class="font-medium text-primary-800 w-56 flex">Seuil de réussite:</span> 
                                    <span>70%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section: Types de questions -->
                <section class="py-8 flex flex-col">
                    <h2 class="text-2xl font-semibold text-primary-700 mb-6">Types de questions et méthodes d'évaluation
                    </h2>

                    <p class="text-fg mb-4">L'examen comporte plusieurs formats de questions pour tester
                        différents niveaux de compétences :</p>

                    <div class="grid gap-6 mb-6">
                        <div class="bg-white p-6 rounded-md border border-gray-200 shadow-sm">
                            <div class="flex items-center mb-3">
                                <h3 class="text-lg font-medium text-fg-subtitle">QCM classiques</h3>
                            </div>
                            <p class="text-fg text-sm">Ces questions évaluent vos connaissances fondamentales et
                                votre compréhension des concepts de gestion des risques RH.</p>
                            <p class="text-fg text-sm mt-2">Une seule réponse est correcte parmi les 4
                                propositions.</p>
                            <div class="mt-3 text-xs text-gray-500">Sections: 1ère, 2ème, 3ème, 5ème et 7ème Parties
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-md border border-gray-200 shadow-sm">
                            <div class="flex items-center mb-3">
                                <h3 class="text-lg font-medium text-fg-subtitle">Etudes de cas avec QCM</h3>
                            </div>
                            <p class="text-fg text-sm">Vous serez confronté à un scénario détaillé, nécessitant
                                l'analyse d'une situation réelle pour identifier le risque principal.</p>
                            <p class="text-fg text-sm mt-2">Les réponses portent sur l'évaluation des facteurs de
                                risque et les indicateurs appropriés.</p>
                            <div class="mt-3 text-xs text-gray-500">Section: 4 – Identification des Risques RH</div>
                        </div>

                        <div class="bg-white p-6 rounded-md border border-gray-200 shadow-sm">
                            <div class="flex items-center mb-3">
                                <h3 class="text-lg font-medium text-fg-subtitle">Scénarios pratiques avec QCM</h3>
                            </div>
                            <p class="text-fg text-sm">Ce format vous met dans une situation où vous devez
                                choisir la meilleure stratégie pour traiter un risque RH spécifique.</p>
                            <p class="text-fg text-sm mt-2">Les décisions à prendre couvrent l'évitement, la
                                contrôle, l'acceptation ou le transfert des risques.</p>
                            <div class="mt-3 text-xs text-gray-500">Section: 6 – Traitement des Risques RH</div>
                        </div>
                    </div>
                </section>

                <!-- Section: Barème et critères -->
                <section class="py-8 flex flex-col">
                    <h2 class="text-2xl font-semibold text-primary-700 mb-6">Barème et critères de réussite</h2>

                    <ul class="space-y-2 text-fg mb-6 list-disc list-outside pl-4">
                        <li>
                            Chaque question est notée en fonction de la complexité de l'analyse requise.
                        </li>
                        <li>
                            70 points sur 100 sont nécessaires pour obtenir la certification.
                        </li>
                        <li>
                            Les sections les plus stratégiques, notamment le traitement des risques RH (25%), ont
                                un poids plus élevé, car elles reflètent l'importance de la gestion proactive des
                                risques en entreprise.
                        </li>
                    </ul>

                    <div class="bg-bg-light/40 p-6 rounded-md border border-border/30">
                        <h3 class="text-lg font-medium text-primary-800 mb-3">Durée d'Evaluation</h3>
                        <p class="text-fg">La plateforme d'évaluation restera ouverte pendant 30 jours afin de
                            permettre aux participants de passer l'examen à leur convenance.</p>
                    </div>
                </section>

                <!-- Section: Résultats et certification -->
                <section class="pt-8 flex flex-col">
                    <h2 class="text-2xl font-semibold text-primary-700 mb-6">Résultats et certification</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-xl font-medium text-fg-subtitle mb-3">Correction et notation</h3>
                            <ul class="space-y-2 text-fg list-disc list-outside pl-4">
                                <li >
                                    <span>Une fois l'examen terminé, votre score final sera calculé
                                        automatiquement.</span>
                                </li>
                                <li>
                                    <span>Vous recevrez votre résultat détaillé indiquant vos points obtenus par
                                        section.</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-xl font-medium text-fg-subtitle mb-3">Certification</h3>
                            <ul class="space-y-2 text-fg list-disc list-outside pl-4">
                                <li>
                                    <span>Les candidats ayant obtenu 70% ou plus recevront une certification en Gestion
                                        des Risques RH, attestant de leurs compétences avancées.</span>
                                </li>
                                <li>
                                    <span>En cas d'échec, une session de rattrapage pourra être organisée après 7 jours
                                        à compter à partir de la communication des résultats.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-xl font-medium text-fg-subtitle mb-3">Délivrance de la Certification</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white p-5 rounded-md border border-gray-200 shadow-sm">
                                <div class="flex items-center mb-2">
                                    <h4 class="font-medium text-fg-subtitle">Certificat numérique</h4>
                                </div>
                                <p class="text-fg text-sm">Disponible immédiatement après la validation de
                                    l'examen via notre plateforme en ligne.</p>
                            </div>

                            <div class="bg-white p-5 rounded-md border border-gray-200 shadow-sm">
                                <div class="flex items-center mb-2">
                                    <h4 class="font-medium text-fg-subtitle">Certificat physique</h4>
                                </div>
                                <p class="text-fg text-sm">Les participants peuvent recevoir un certificat
                                    imprimé sous 5 jours ouvrables après la validation de leur évaluation.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-primary-50 p-6 rounded-md bg-white border border-gray-200 shadow-sm">
                        <h3 class="text-xl font-medium text-primary-800 mb-3">Maintien de la Certification</h3>
                        <p class="text-fg mb-3">Chaque professionnel certifié est tenu de cumuler au moins 20
                            Unités de Formation Continue (UFC) sur une période de deux ans afin de maintenir la validité
                            de sa certification.</p>
                        <p class="text-fg">L'objectif est d'assurer que les connaissances acquises restent
                            pertinentes face aux évolutions des pratiques et des réglementations en matière de gestion
                            des risques.</p>
                    </div>
                    <div class="mt-10 p-6 rounded-md bg-bg border border-border shadow-sm">
                        <p class="text-fg">
                            Nous vous souhaitons plein succès dans cette certification ! 
                        </p>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>
