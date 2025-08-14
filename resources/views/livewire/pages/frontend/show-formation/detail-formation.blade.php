<div>
    <section class="my-32 mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col md:flex-row gap-16">
        <article class="flex flex-col flex-1">
            <div class="flex flex-col">
                <h1 class="font-medium text-xl sm:text-2xl/snug lg:text-5xl text-fg-title">
                    {{ $formation->title }}
                </h1>
                <p class="font-medium mt-6 text-fg-subtext">
                    {{ $formation->short_description ?? 'Apprenez et maîtrisez les compétences essentielles' }}
                </p>
            </div>

            <div class="mt-12 flex flex-col space-y-6">
                <span class="text-lg font-semibold text-fg-title">Présentation</span>
                <div class="text-fg space-y-4">
                    <div x-data="{ showFullDescription: false }" wire:key="description-content">
                        <p x-html="showFullDescription ? `{!! $formation->description !!}` : `{!! Str::limit($formation->description, 300) !!}`"></p>

                        @if(strlen($formation->description) > 300)
                            <button @click="showFullDescription = !showFullDescription"
                                    class="text-primary-600 hover:underline mt-2">
                                <span x-text="showFullDescription ? 'Voir moins' : 'Voir plus'"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col space-y-6">
                <span class="text-lg font-semibold text-fg-title">Aperçu du contenu</span>
                <div class="text-fg space-y-4">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5 text-primary-600">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                            </svg>
                            <p>{{ $moduleCount ?? 0 }} modules</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5 text-primary-600">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"/>
                            </svg>
                            <p>{{ $chapterCount ?? 0 }} chapitres</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5 text-primary-600">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                            </svg>
                            <p>Niveau: <span class="capitalize">{{ $formation->difficulty_level }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <div
            class="md:sticky h-max z-[20] md:w-72 lg:w-80 xl:w-[22.5rem] bg-bg w-full shadow-xl shadow-bg-high/50 rounded-md p-5 md:p-6 border border-gray-100 top-24">
            <span class="font-medium text-fg text-sm mb-4 pb-3 border-b w-full flex">Détails du cours</span>
            <ul class="flex flex-col divide-y divide-border-light *:py-3 first:*:pt-0 last:*:pb-0 mt-4">
                <li class="flex justify-between items-center">
                    <div class="flex items-center text-fg-subtext text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>
                        </svg>
                        <span>Langue</span>
                    </div>
                    <div class="font-semibold text-fg-title text-right capitalize">
                        {{ $formation->language === 'fr' ? 'Français' : ($formation->language === 'en' ? 'Anglais' : $formation->language) }}
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <div class="flex items-center text-fg-subtext text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <span>Durée</span>
                    </div>
                    <div class="font-semibold text-fg-title text-right">
                        {{ $formation->duration_hours }} heures
                    </div>
                </li>
                <li class="flex flex-col">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                        </svg>
                        <span class="text-fg-subtext text-sm">Tarif</span>
                    </div>
                    <div class="flex flex-col flex-1">
                        <ul class="bg-bg-lighter border border-border-light rounded-md px-2 py-1 mt-2 text-fg list-disc flex flex-col gap-1">
                            <li class="flex justify-between items-center">
                                <span class="text-sm text-fg-subtext">Prix:</span>
                                <span class="text-right text-fg-subtitle font-semibold">
                                    {{ $formation->price > 0 ? $formation->price . '$' : 'Gratuit' }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <div class="flex items-center text-fg-subtext text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                        </svg>
                        <span>Certification</span>
                    </div>
                    <div class="font-semibold text-right flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             class="size-5 mr-1 text-green-500">
                            <path fill-rule="evenodd"
                                  d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span>Seuil de réussite: {{ $formation->certification_threshold }}%</span>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <div class="flex items-center text-fg-subtext text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/>
                        </svg>
                        <span>Niveau</span>
                    </div>
                    <div class="font-semibold text-fg-title text-right capitalize">
                        {{ $formation->difficulty_level === 'beginner' ? 'Débutant' :
                          ($formation->difficulty_level === 'intermediate' ? 'Intermédiaire' : 'Avancé') }}
                    </div>
                </li>
                <li class="flex flex-wrap gap-2">
                    @if($formation->tags)
                        <div class="flex flex-wrap gap-2">
                            @foreach(json_decode($formation->tags) ?? explode(',', $formation->tags) as $tag)
                                <span
                                    class="bg-bg-lighter hover:bg-blue-100 transition-colors px-3 py-1.5 rounded-full text-xs font-medium text-primary-800 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         class="w-3 h-3 mr-1">
                                        <path fill-rule="evenodd"
                                              d="M5.5 3A2.5 2.5 0 0 0 3 5.5v2.879a2.5 2.5 0 0 0 .732 1.767l6.5 6.5a2.5 2.5 0 0 0 3.536 0l2.878-2.878a2.5 2.5 0 0 0 0-3.536l-6.5-6.5A2.5 2.5 0 0 0 8.38 3H5.5ZM6 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                    {{ is_string($tag) ? trim($tag) : $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </li>
                <li>
                    <button
                        wire:click="enroll"
                        wire:loading.attr="disabled"
                        class="group relative w-full btn btn-sm sm:btn-md justify-center overflow-hidden rounded-md btn-solid {{ $isEnrolled ? 'btn-solid bg-primary-600' : 'bg-green-600' }} text-white">
                        <div class="flex items-center relative z-10">
                            <span wire:loading.remove>
                                {{ $isEnrolled ? 'Accéder à la formation' : 'S\'inscrire à la formation' }}
                            </span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement en cours...
                            </span>
                            <div class="ml-1 transition duration-500 group-hover:rotate-[360deg]" wire:loading.remove>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </div>
                        </div>
                        <span data-btn-layer class="before:{{ $isEnrolled ? 'bg-blue-800' : 'bg-green-800' }}"></span>
                    </button>
                </li>
            </ul>
        </div>
    </section>
</div>
