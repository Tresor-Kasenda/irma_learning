<div>
    <x-header-with-image image-half>
        <div class="w-full grid items-center relative pt-4">
            <div
                class="mx-auto max-w-7xl w-full px-5 sm:px-10 lg:px-5 py-20  flex flex-col lg:flex-row gap-16 items-start relative">
                <div class="relative max-w-lg lg:max-w-none space-y-7 flex flex-col w-full lg:w-1/2 lg:flex-1">
                    <div
                        class=" text-sm pt-8 text-fg-subtitle flex items-center justify-center divide-x divide-white/50 *:px-4 first:*:pl-0 last:*:pr-0 overflow-hidden max-w-full">
                        <a href="./" aria-label="Lien vers la page principale">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </a>
                        <div class="text-primary-300 text-center">
                            Certification
                        </div>
                    </div>
                    <h1 class="text-white text-xl text-center">
                        Certification Professionnelle
                    </h1>
                    <p class="text-gray-200 text-4xl/tight  font-medium max-w-lg mx-auto text-center italic">
                        Un gage de crédibilité qui ouvre à de nouvelles opportunités
                    </p>
                </div>
                <div
                    class="flex w-full lg:w-1/2 lg:flex-1 lg:min-h-[440px] border border-dashed rounded-md bg-bg shadow-sm shadow-gray-100/20">
                    @if (count($formations) > 0)
                        <div class="flex flex-col p-4 xl:p-5 justify-between min-h-full">
                            <span class="mb-4 flex text-fg-title font-semibold h-max">Formation en cours</span>
                            <ul
                                class="flex-1 flex flex-col gap-4 divide-y divide-gray-100/70 *:py-2 first:*:pt-0 last:*:pb-0 mb-5">
                                @foreach ($formations as $formation)
                                    <li class="flex-1 flex items-start gap-3 max-h-max hover:bg-bg-light/30 p-2 rounded-lg transition-colors"
                                        wire:key="formation-level-{{ $formation->id }}">
                                        <div class="p-2 rounded bg-bg-light text-primary flex min-w-max">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                fill="currentColor" viewBox="0 0 256 256">
                                                <path
                                                    d="M128,136a8,8,0,0,1-8,8H72a8,8,0,0,1,0-16h48A8,8,0,0,1,128,136Zm-8-40H72a8,8,0,0,0,0,16h48a8,8,0,0,0,0-16Zm112,65.47V224A8,8,0,0,1,220,231l-24-13.74L172,231A8,8,0,0,1,160,224V200H40a16,16,0,0,1-16-16V56A16,16,0,0,1,40,40H216a16,16,0,0,1,16,16V86.53a51.88,51.88,0,0,1,0,74.94ZM160,184V161.47A52,52,0,0,1,216,76V56H40V184Zm56-12a51.88,51.88,0,0,1-40,0v38.22l16-9.16a8,8,0,0,1,7.94,0l16,9.16Zm16-48a36,36,0,1,0-36,36A36,36,0,0,0,232,124Z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 flex flex-col gap-2">
                                            <!-- Title and button for small screens -->
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                                <div class="mb-2 sm:mb-0 sm:mr-4">
                                                    <h3 class="font-semibold text-fg-subtitle line-clamp-2">
                                                        {{ $formation->title }}
                                                    </h3>
                                                    <div class="flex items-center gap-1.5 text-sm text-gray-500 mt-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32"
                                                            height="32" fill="currentColor" class="size-4"
                                                            viewBox="0 0 256 256">
                                                            <path
                                                                d="M232,136.66A104.12,104.12,0,1,1,119.34,24,8,8,0,0,1,120.66,40,88.12,88.12,0,1,0,216,135.34,8,8,0,0,1,232,136.66ZM120,72v56a8,8,0,0,0,8,8h56a8,8,0,0,0,0-16H136V72a8,8,0,0,0-16,0Zm40-24a12,12,0,1,0-12-12A12,12,0,0,0,160,48Zm36,24a12,12,0,1,0-12-12A12,12,0,0,0,196,72Zm24,36a12,12,0,1,0-12-12A12,12,0,0,0,220,108Z">
                                                            </path>
                                                        </svg>
                                                        <p class="capitalize">{{ $formation->difficulty_level }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex min-w-max">
                                                    <a href="{{ route('formation.show', $formation) }}" wire:navigate
                                                        class="btn btn-sm rounded-md w-full justify-center text-white bg-primary">
                                                        Details
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Essential information -->
                                            <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-fg-subtext mt-1">
                                                <div class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32"
                                                        height="32" fill="currentColor" class="size-3.5"
                                                        viewBox="0 0 256 256">
                                                        <path
                                                            d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34Z">
                                                        </path>
                                                    </svg>
                                                    {{ $formation->duration_hours }} H
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32"
                                                        height="32" fill="currentColor" class="size-3.5"
                                                        viewBox="0 0 256 256">
                                                        <path
                                                            d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216ZM173.66,82.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32,0l-16-16a8,8,0,0,1,11.32-11.32L128,116.69l34.34-34.35A8,8,0,0,1,173.66,82.34Z">
                                                        </path>
                                                    </svg>
                                                    {{ $formation->certification_threshold }}% requis
                                                </div>
                                                <div class="flex items-center gap-1 font-medium text-primary-600">
                                                    {{ Number::currency($formation->price) }}
                                                </div>
                                            </div>

                                            <!-- Tags -->
                                            @if ($formation->tags)
                                                <div class="flex flex-wrap gap-1.5 mt-1">
                                                    @foreach (json_decode($formation->tags) ?? explode(',', $formation->tags) as $tag)
                                                        <span
                                                            class="bg-bg-lighter px-2 py-0.5 rounded-full text-xs font-medium text-primary-700">
                                                            {{ is_string($tag) ? trim($tag) : $tag }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="flex h-max">
                                <a href="#certifications"
                                    class="group relative w-max btn btn-sm sm:btn-md justify-center overflow-hidden rounded-md btn-solid bg-primary-50 text-primary-800 hover:text-primary-50">
                                    <div class="flex items-center relative z-10">
                                        <span>Decouvrir plus</span>
                                        <div class="ml-1 transition duration-500 group-hover:rotate-[360deg]">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                            </svg>

                                        </div>
                                    </div>
                                    <span data-btn-layer class=" before:bg-primary-600"></span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="w-full flex flex-col items-center px-5 sm:px-10 py-8 sm:py-16 lg:py-20 lg:px-16">
                            <svg class="mx-auto" xmlns="http://www.w3.org/2000/svg" width="168" height="164"
                                viewBox="0 0 168 164" fill="none">
                                <g filter="url(#filter0_d_14133_736)">
                                    <path
                                        d="M3.99988 81.0083C3.99988 36.7097 39.9078 1 84.0081 1C128.042 1 164 36.6932 164 81.0083C164 99.8046 157.525 117.098 146.657 130.741C131.676 149.653 108.784 161 84.0081 161C59.0675 161 36.3071 149.57 21.3427 130.741C10.4745 117.098 3.99988 99.8046 3.99988 81.0083Z"
                                        fill="#ECFDF5" />
                                </g>
                                <path
                                    d="M145.544 77.4619H146.044V76.9619V48.9851C146.044 43.424 141.543 38.9227 135.982 38.9227H67.9223C64.839 38.9227 61.9759 37.3578 60.3174 34.7606L60.3159 34.7583L56.8477 29.3908L56.8472 29.3901C54.9884 26.5237 51.8086 24.7856 48.3848 24.7856H26.4195C20.8584 24.7856 16.3571 29.287 16.3571 34.848V76.9619V77.4619H16.8571H145.544Z"
                                    fill="#D1FAE5" stroke="#6EE7B7" />
                                <path
                                    d="M63.9999 26.2856C63.9999 25.7334 64.4476 25.2856 64.9999 25.2856H141.428C143.638 25.2856 145.428 27.0765 145.428 29.2856V33.8571H67.9999C65.7907 33.8571 63.9999 32.0662 63.9999 29.8571V26.2856Z"
                                    fill="#6EE7B7" />
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                    transform="matrix(-1 0 0 1 46.8571 31)" fill="#10B981" />
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                    transform="matrix(-1 0 0 1 38.2859 31)" fill="#10B981" />
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                    transform="matrix(-1 0 0 1 29.7141 31)" fill="#10B981" />
                                <path
                                    d="M148.321 126.907L148.321 126.906L160.559 76.3043C162.7 67.5161 156.036 59.0715 147.01 59.0715H14.5902C5.56258 59.0715 -1.08326 67.5168 1.04059 76.3034L1.04064 76.3036L13.2949 126.906C14.9181 133.621 20.9323 138.354 27.8354 138.354H133.764C140.685 138.354 146.681 133.621 148.321 126.907Z"
                                    fill="#FFFFFF" stroke="#D1FAE5" />
                                <path
                                    d="M86.3858 109.572C85.2055 109.572 84.2268 108.593 84.2268 107.384C84.2268 102.547 76.9147 102.547 76.9147 107.384C76.9147 108.593 75.9359 109.572 74.7269 109.572C73.5466 109.572 72.5678 108.593 72.5678 107.384C72.5678 96.7899 88.5737 96.8186 88.5737 107.384C88.5737 108.593 87.5949 109.572 86.3858 109.572Z"
                                    fill="#10B981" />
                                <path
                                    d="M104.954 91.0616H95.9144C94.7053 91.0616 93.7265 90.0829 93.7265 88.8738C93.7265 87.6935 94.7053 86.7147 95.9144 86.7147H104.954C106.163 86.7147 107.141 87.6935 107.141 88.8738C107.141 90.0829 106.163 91.0616 104.954 91.0616Z"
                                    fill="#10B981" />
                                <path
                                    d="M65.227 91.0613H56.1877C54.9787 91.0613 53.9999 90.0825 53.9999 88.8734C53.9999 87.6931 54.9787 86.7144 56.1877 86.7144H65.227C66.4073 86.7144 67.3861 87.6931 67.3861 88.8734C67.3861 90.0825 66.4073 91.0613 65.227 91.0613Z"
                                    fill="#10B981" />
                                <circle cx="142.572" cy="121" r="24.7857" fill="#D1FAE5" stroke="#6EE7B7" />
                                <path
                                    d="M152.214 130.643L149.535 127.964M150.071 119.928C150.071 115.195 146.234 111.357 141.5 111.357C136.766 111.357 132.928 115.195 132.928 119.928C132.928 124.662 136.766 128.5 141.5 128.5C143.858 128.5 145.993 127.548 147.543 126.007C149.104 124.455 150.071 122.305 150.071 119.928Z"
                                    stroke="#10B981" stroke-width="1.6" stroke-linecap="round" />
                                <defs>
                                    <filter id="filter0_d_14133_736" x="1.99988" y="0" width="164" height="164"
                                        filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                        <feColorMatrix in="SourceAlpha" type="matrix"
                                            values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                        <feOffset dy="1" />
                                        <feGaussianBlur stdDeviation="1" />
                                        <feComposite in2="hardAlpha" operator="out" />
                                        <feColorMatrix type="matrix"
                                            values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.05 0" />
                                        <feBlend mode="normal" in2="BackgroundImageFix"
                                            result="effect1_dropShadow_14133_736" />
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_14133_736"
                                            result="shape" />
                                    </filter>
                                </defs>
                            </svg>
                            <div>
                                <h2 class="text-center text-fg-title text-xl font-semibold leading-loose pb-2">
                                    Aucune certification trouvée
                                </h2>
                                <p
                                    class="text-center text-fg text-base font-normal leading-relaxed pb-4 max-w-sm mx-auto">
                                    Aucune certification disponible pour le moment. Veuillez vérifier plus
                                    tard.
                                </p>
                                <div class="flex justify-center">
                                    <a href="{{ route('home-page') }}" wire:navigate
                                        class="btn btn-md rounded-md w-max justify-center text-white bg-primary">
                                        Revenir a l'Accueil
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </x-header-with-image>

    <section class="mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col gap-16">
        <h2 class="font-medium relative text-3xl md:text-4xl text-fg-title max-w-xl mx-auto text-center capitalize">
            Informations importantes
        </h2>
        <div x-data x-accordion data-accordion-type="single"
            class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 w-full relative">
            <div data-accordion-item data-accordion-value="item1"
                class="h-max transition duration-500 fx-open:border-primary bg-bg/50 backdrop-blur-sm border rounded-lg border-border-light">
                <button data-accordion-trigger
                    class="group inline-flex items-center justify-between text-left w-full transition duration-500 hover:bg-bg-high/20 rounded-lg p-4 ">
                    <h5 class="text-primary-700 font-medium">Validation des Compétences</h5>
                    <div class="size-3 flex relative ease-linear duration-300 group-aria-expanded:rotate-45">
                        <span class="flex h-0.5 w-full absolute top-1/2 -translate-y-1/2 bg-fg-title/60"></span>
                        <span class="flex h-full w-0.5 absolute left-1/2 -translate-x-1/2 bg-fg-title/60"></span>
                    </div>
                </button>
                <div data-accordion-content class="ease-linear transition-all fx-close:h-0 overflow-hidden">
                    <div class="p-4 border-t border-border-light">
                        <p class="text-fg-subtext">
                            Les tests de certification professionnelle permettent de valider le niveau de compétence, de
                            connaissance et d’aptitude des participants aux MasterClasses. Ils constituent une preuve
                            d'autorité et de crédibilité professionnelles pour les certifiés.
                        </p>
                    </div>
                </div>
            </div>
            <div data-accordion-item data-accordion-value="item2"
                class="h-max transition duration-500 fx-open:border-primary bg-bg/50 backdrop-blur-sm border rounded-lg border-border-light">
                <button data-accordion-trigger
                    class="group inline-flex items-center justify-between text-left w-full transition duration-500 hover:bg-bg-high/20 rounded-lg p-4 ">
                    <h5 class="text-primary-700 font-medium">Modalités d'Accès et de Passation</h5>
                    <div class="size-3 flex relative ease-linear duration-300 group-aria-expanded:rotate-45">
                        <span class="flex h-0.5 w-full absolute top-1/2 -translate-y-1/2 bg-fg-title/60"></span>
                        <span class="flex h-full w-0.5 absolute left-1/2 -translate-x-1/2 bg-fg-title/60"></span>
                    </div>
                </button>
                <div data-accordion-content class="ease-linear transition-all fx-close:h-0 overflow-hidden">
                    <div class="p-4 border-t border-border-light">
                        <div>
                            Les tests de certification sont :
                        </div>
                        <ul class="list-disc list-outside pl-5 text-fg-subtext text-sm mt-1">
                            <li>
                                Entièrement automatisés et accessibles en ligne immédiatement après la fin de la
                                MasterClass.
                            </li>
                            <li>
                                Disponibles pour une durée de 30 jours afin de permettre aux participants de finaliser
                                et soumettre leur évaluation via notre plateforme dédiée.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div data-accordion-item data-accordion-value="item3"
                class="h-max transition duration-500 fx-open:border-primary bg-bg/50 backdrop-blur-sm border rounded-lg border-border-light">
                <button data-accordion-trigger
                    class="group inline-flex items-center justify-between text-left w-full transition duration-500 hover:bg-bg-high/20 rounded-lg p-4 ">
                    <h5 class="text-primary-700 font-medium">Publication des Résultats et Délivrance des Certifications
                    </h5>
                    <div class="size-3 flex relative ease-linear duration-300 group-aria-expanded:rotate-45">
                        <span class="flex h-0.5 w-full absolute top-1/2 -translate-y-1/2 bg-fg-title/60"></span>
                        <span class="flex h-full w-0.5 absolute left-1/2 -translate-x-1/2 bg-fg-title/60"></span>
                    </div>
                </button>
                <div data-accordion-content class="ease-linear transition-all fx-close:h-0 overflow-hidden">
                    <div class="p-4 border-t border-border-light">
                        <ul class="list-disc list-outside pl-5 text-fg-subtext text-sm mt-1 space-y-2">
                            <li>
                                Les résultats sont automatiquement générés et communiqués dans un délai de 7 jours après
                                la soumission.
                            </li>
                            <li>
                                <span>
                                    Les certifications attestant des compétences acquises sont disponibles sous deux
                                    formats :
                                </span>
                                <ul class="list-disc list-outside pl-5 text-fg-subtext text-sm mt-2 space-y-2">
                                    <li>
                                        <span>Téléchargement instantané</span> via la plateforme en ligne dès la
                                        publication des résultats.
                                    </li>
                                    <li>
                                        <span>Envoi physique</span> des certifications imprimées sous 5 jours ouvrables
                                        après publication des résultats.
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="certifications" class="my-32 scroll-mt-20 w-full flex flex-col gap-16">
        <h2 class="font-medium relative text-3xl md:text-4xl text-fg-title max-w-xl mx-auto text-center capitalize">
            Nos certifications
        </h2>
        <div
            class="mx-auto max-w-7xl w-full px-5 sm:px-10 border border-border-lighter bg-bg/30 rounded-md h-14 sticky top-2 z-20 backdrop-blur-sm flex justify-between items-center">
            <div class="text-sm md:text-base text-fg-subtext">
                @if ($formationCount)
                    <span class="text-primary"> {{ $formationCount }}</span>
                    formation{{ $formationCount > 1 ? 's' : '' }} <span
                        class="hidden sm:inline">Disponible{{ $formationCount > 1 ? 's' : '' }}</span>
                @else
                    Aucune formation disponible
                @endif
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
                        class="ui-form-input px-4 h-9 rounded-md peer w-full ps-9" />
                </div>
            </div>
        </div>
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 grid">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($formations as $formation)
                    <div wire:key="formations{{ $formation->id }}"
                        class="bg-bg border border-border-light overflow-hidden shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50 rounded-lg flex flex-col">
                        <div class="rounded bg-bg-light aspect-video relative">
                            <img src="{{ asset('storage/' . $formation->image) }}" alt="{{ $formation->title }}"
                                width="2000" height="1333" class="w-full h-full rounded-md object-cover">
                            <div
                                class="absolute top-2 right-2 bg-bg/80 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium text-primary-700">
                                {{ $formation->difficulty_level->getLabel() }}
                            </div>
                        </div>
                        <div class="p-6 flex flex-col flex-1">
                            <h2 x-data="{ showTooltip: false }" @mouseenter="showTooltip = true"
                                @mouseleave="showTooltip = false" class="relative">
                                <a href="{{ route('formation.show', $formation) }}" wire:navigate
                                    class="text-lg sm:text-xl font-semibold text-fg-subtitle line-clamp-2 group-hover:text-primary-600 ease-linear duration-200">
                                    {{ $formation->title }}
                                </a>
                                <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 transform translate-y-0"
                                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                                    class="absolute z-50 bottom-full left-0 mb-1 p-2 bg-gray-950 text-white shadow-sm rounded-md text-xs font-light max-w-md">
                                    {{ $formation->title }}
                                </div>
                            </h2>
                            <p class="my-4 text-fg-subtext line-clamp-1">
                                {!! str($formation->description)->limit(80) !!}
                            </p>

                            <!-- Tags -->
                            @if ($formation->tags)
                                <div class="flex flex-wrap gap-2">
                                    @foreach (json_decode($formation->tags) ?? explode(',', $formation->tags) as $tag)
                                        <span
                                            class="bg-bg-lighter hover:bg-blue-100 transition-colors px-3 py-1.5 rounded-full text-xs font-medium text-primary-800 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" class="w-3 h-3 mr-1">
                                                <path fill-rule="evenodd"
                                                    d="M5.5 3A2.5 2.5 0 0 0 3 5.5v2.879a2.5 2.5 0 0 0 .732 1.767l6.5 6.5a2.5 2.5 0 0 0 3.536 0l2.878-2.878a2.5 2.5 0 0 0 0-3.536l-6.5-6.5A2.5 2.5 0 0 0 8.38 3H5.5ZM6 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ is_string($tag) ? trim($tag) : $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pb-5 flex flex-col flex-1">
                                <div class="flex items-center justify-between text-sm text-fg-subtext">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            fill="currentColor" class="size-4" viewBox="0 0 256 256">
                                            <path
                                                d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z">
                                            </path>
                                        </svg>
                                        {{ $formation->duration_hours }} H
                                    </div>
                                    <div class="flex items-center gap-1 font-semibold text-fg-subtitle text-xl">
                                        {{ Number::currency($formation->price) }}
                                    </div>
                                </div>

                                <!-- Additional information -->
                                <div class="flex flex-wrap justify-between mt-3 text-xs text-fg-subtext">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            fill="currentColor" class="size-4" viewBox="0 0 256 256">
                                            <path
                                                d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216ZM173.66,82.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32,0l-16-16a8,8,0,0,1,11.32-11.32L128,116.69l34.34-34.35A8,8,0,0,1,173.66,82.34Z">
                                            </path>
                                        </svg>
                                        Certification {{ $formation->certification_threshold }}% requis
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            fill="currentColor" class="size-4" viewBox="0 0 256 256">
                                            <path
                                                d="M222.14,58.87A8,8,0,0,0,216,56H54.68L49.79,29.14A16,16,0,0,0,34.05,16H16a8,8,0,0,0,0,16h18L59.56,172.29a24,24,0,0,0,5.33,11.27,28,28,0,1,0,44.4,8.44h45.42a27.75,27.75,0,0,0-2.71,12,28,28,0,1,0,28-28H83.17a8,8,0,0,1-7.87-6.57L72.13,152h116a24,24,0,0,0,23.61-19.71l12.16-66.86A8,8,0,0,0,222.14,58.87ZM180,192a12,12,0,1,1-12,12A12,12,0,0,1,180,192Zm-96,0a12,12,0,1,1-12,12A12,12,0,0,1,84,192Zm104.24-74.26A8,8,0,0,1,180,136H69.22L57.59,72H206.76Z">
                                            </path>
                                        </svg>
                                        {{ $formation->is_active ? 'Disponible' : 'Bientôt disponible' }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            fill="currentColor" class="size-4" viewBox="0 0 256 256">
                                            <path
                                                d="M239.2,97.29a16,16,0,0,0-13.81-11L166,81.17,142.72,25.81h0a15.95,15.95,0,0,0-29.44,0L90.07,81.17,30.61,86.32a16,16,0,0,0-9.11,28.06L66.61,153.8,53.09,212.34a16,16,0,0,0,23.84,17.34l51-31,51.11,31a16,16,0,0,0,23.84-17.34l-13.51-58.6,45.1-39.36A16,16,0,0,0,239.2,97.29Zm-15.22,5-45.1,39.36a16,16,0,0,0-5.08,15.71L187.35,216v0l-51.07-31a15.9,15.9,0,0,0-16.54,0l-51,31h0L82.2,157.4a16,16,0,0,0-5.08-15.71L32,102.35a.37.37,0,0,1,0-.09l59.44-5.14a16,16,0,0,0,13.35-9.75L128,32.08l23.2,55.29a16,16,0,0,0,13.35,9.75l59.45,5.14Z">
                                            </path>
                                        </svg>
                                        {{ $formation->is_featured ? 'Formation vedette' : 'Formation standard' }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            fill="currentColor" class="size-4" viewBox="0 0 256 256">
                                            <path
                                                d="M247.15,212.42l-56-112a8,8,0,0,0-14.31,0l-21.71,43.43A88,88,0,0,1,108,126.93,103.65,103.65,0,0,0,135.69,64H160a8,8,0,0,0,0-16H104V32a8,8,0,0,0-16,0V48H32a8,8,0,0,0,0,16h87.63A87.76,87.76,0,0,1,96,116.35a87.74,87.74,0,0,1-19-31,8,8,0,1,0-15.08,5.34A103.63,103.63,0,0,0,84,127a87.55,87.55,0,0,1-52,17,8,8,0,0,0,0,16,103.46,103.46,0,0,0,64-22.08,104.18,104.18,0,0,0,51.44,21.31l-26.6,53.19a8,8,0,0,0,14.31,7.16L148.94,192h70.11l13.79,27.58A8,8,0,0,0,240,224a8,8,0,0,0,7.15-11.58ZM156.94,176,184,121.89,211.05,176Z">
                                            </path>
                                        </svg>
                                        {{ strtoupper($formation->language) }}
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('formation.show', $formation) }}" wire:navigate
                                class="btn btn-md rounded w-full justify-center mt-3 btn-solid bg-primary-600 text-white group">
                                <span class="relative z-10">
                                    Suivre la formation
                                </span>
                                <span data-btn-layer class=" before:bg-primary-800"></span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="w-full col-span-full">
                        <div
                            class="w-full flex flex-col items-center px-5 sm:px-10 py-8 sm:py-16 lg:py-20 lg:px-16 border border-dashed rounded-md bg-primary-50/20">
                            <svg class="mx-auto" xmlns="http://www.w3.org/2000/svg" width="168" height="164"
                                viewBox="0 0 168 164" fill="none">
                                <g filter="url(#filter0_d_14133_736)">
                                    <path
                                        d="M3.99988 81.0083C3.99988 36.7097 39.9078 1 84.0081 1C128.042 1 164 36.6932 164 81.0083C164 99.8046 157.525 117.098 146.657 130.741C131.676 149.653 108.784 161 84.0081 161C59.0675 161 36.3071 149.57 21.3427 130.741C10.4745 117.098 3.99988 99.8046 3.99988 81.0083Z"
                                        fill="#ECFDF5" />
                                </g>
                                <path
                                    d="M145.544 77.4619H146.044V76.9619V48.9851C146.044 43.424 141.543 38.9227 135.982 38.9227H67.9223C64.839 38.9227 61.9759 37.3578 60.3174 34.7606L60.3159 34.7583L56.8477 29.3908L56.8472 29.3901C54.9884 26.5237 51.8086 24.7856 48.3848 24.7856H26.4195C20.8584 24.7856 16.3571 29.287 16.3571 34.848V76.9619V77.4619H16.8571H145.544Z"
                                    fill="#D1FAE5" stroke="#6EE7B7" />
                                <path
                                    d="M63.9999 26.2856C63.9999 25.7334 64.4476 25.2856 64.9999 25.2856H141.428C143.638 25.2856 145.428 27.0765 145.428 29.2856V33.8571H67.9999C65.7907 33.8571 63.9999 32.0662 63.9999 29.8571V26.2856Z"
                                    fill="#6EE7B7" />
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                    transform="matrix(-1 0 0 1 46.8571 31)" fill="#10B981" />
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                    transform="matrix(-1 0 0 1 38.2859 31)" fill="#10B981" />
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                    transform="matrix(-1 0 0 1 29.7141 31)" fill="#10B981" />
                                <path
                                    d="M148.321 126.907L148.321 126.906L160.559 76.3043C162.7 67.5161 156.036 59.0715 147.01 59.0715H14.5902C5.56258 59.0715 -1.08326 67.5168 1.04059 76.3034L1.04064 76.3036L13.2949 126.906C14.9181 133.621 20.9323 138.354 27.8354 138.354H133.764C140.685 138.354 146.681 133.621 148.321 126.907Z"
                                    fill="#FFFFFF" stroke="#D1FAE5" />
                                <path
                                    d="M86.3858 109.572C85.2055 109.572 84.2268 108.593 84.2268 107.384C84.2268 102.547 76.9147 102.547 76.9147 107.384C76.9147 108.593 75.9359 109.572 74.7269 109.572C73.5466 109.572 72.5678 108.593 72.5678 107.384C72.5678 96.7899 88.5737 96.8186 88.5737 107.384C88.5737 108.593 87.5949 109.572 86.3858 109.572Z"
                                    fill="#10B981" />
                                <path
                                    d="M104.954 91.0616H95.9144C94.7053 91.0616 93.7265 90.0829 93.7265 88.8738C93.7265 87.6935 94.7053 86.7147 95.9144 86.7147H104.954C106.163 86.7147 107.141 87.6935 107.141 88.8738C107.141 90.0829 106.163 91.0616 104.954 91.0616Z"
                                    fill="#10B981" />
                                <path
                                    d="M65.227 91.0613H56.1877C54.9787 91.0613 53.9999 90.0825 53.9999 88.8734C53.9999 87.6931 54.9787 86.7144 56.1877 86.7144H65.227C66.4073 86.7144 67.3861 87.6931 67.3861 88.8734C67.3861 90.0825 66.4073 91.0613 65.227 91.0613Z"
                                    fill="#10B981" />
                                <circle cx="142.572" cy="121" r="24.7857" fill="#D1FAE5" stroke="#6EE7B7" />
                                <path
                                    d="M152.214 130.643L149.535 127.964M150.071 119.928C150.071 115.195 146.234 111.357 141.5 111.357C136.766 111.357 132.928 115.195 132.928 119.928C132.928 124.662 136.766 128.5 141.5 128.5C143.858 128.5 145.993 127.548 147.543 126.007C149.104 124.455 150.071 122.305 150.071 119.928Z"
                                    stroke="#10B981" stroke-width="1.6" stroke-linecap="round" />
                                <defs>
                                    <filter id="filter0_d_14133_736" x="1.99988" y="0" width="164" height="164"
                                        filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                        <feColorMatrix in="SourceAlpha" type="matrix"
                                            values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                        <feOffset dy="1" />
                                        <feGaussianBlur stdDeviation="1" />
                                        <feComposite in2="hardAlpha" operator="out" />
                                        <feColorMatrix type="matrix"
                                            values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.05 0" />
                                        <feBlend mode="normal" in2="BackgroundImageFix"
                                            result="effect1_dropShadow_14133_736" />
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_14133_736"
                                            result="shape" />
                                    </filter>
                                </defs>
                            </svg>
                            <div>
                                <h2 class="text-center text-fg-title text-xl font-semibold leading-loose pb-2">
                                    Aucune certification trouvée
                                </h2>
                                <p
                                    class="text-center text-fg text-base font-normal leading-relaxed pb-4 max-w-sm mx-auto">
                                    Aucune certification disponible pour le moment. Veuillez vérifier plus tard.
                                </p>
                                <div class="flex justify-center">
                                    <a href="{{ route('home-page') }}" wire:navigate
                                        class="btn btn-md rounded-md w-max justify-center text-white bg-primary">
                                        Revenir a l'Accueil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
