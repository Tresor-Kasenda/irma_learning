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
                        class="flex w-full lg:w-1/2 lg:flex-1 lg:min-h-[440px] border border-dashed rounded-md bg-bg shadow-sm shadow-gray-100/20">
                        @if (count($formations) > 0)
                            <div class="flex flex-col w-full p-4 xl:p-5 lg:h-full justify-between">
                                <span class="mb-4 flex text-fg-title font-semibold">Formation en cours</span>
                                <ul
                                    class=" flex-1 flex flex-col gap-4 divide-y divide-gray-100/70 *:py-2 first:*:pt-0 last:*:pb-0 mb-5">
                                    @foreach ($formations as $training)
                                        <li class="flex-1 flex items-start gap-3" wire:key="{{ $training->id }}">
                                            <div class="p-2 rounded bg-bg-light text-primary flex min-w-max">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                     fill="currentColor" viewBox="0 0 256 256">
                                                    <path
                                                        d="M128,136a8,8,0,0,1-8,8H72a8,8,0,0,1,0-16h48A8,8,0,0,1,128,136Zm-8-40H72a8,8,0,0,0,0,16h48a8,8,0,0,0,0-16Zm112,65.47V224A8,8,0,0,1,220,231l-24-13.74L172,231A8,8,0,0,1,160,224V200H40a16,16,0,0,1-16-16V56A16,16,0,0,1,40,40H216a16,16,0,0,1,16,16V86.53a51.88,51.88,0,0,1,0,74.94ZM160,184V161.47A52,52,0,0,1,216,76V56H40V184Zm56-12a51.88,51.88,0,0,1-40,0v38.22l16-9.16a8,8,0,0,1,7.94,0l16,9.16Zm16-48a36,36,0,1,0-36,36A36,36,0,0,0,232,124Z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div
                                                class="flex-1 flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                                <div class="mb-4 sm:mb-0 sm:mr-4">
                                                    <h3 class="font-semibold text-fg-subtitle line-clamp-2">
                                                        {{ $training->title }}
                                                    </h3>
                                                    <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32"
                                                             height="32" fill="currentColor" class="size-4"
                                                             viewBox="0 0 256 256">
                                                            <path
                                                                d="M232,136.66A104.12,104.12,0,1,1,119.34,24,8,8,0,0,1,120.66,40,88.12,88.12,0,1,0,216,135.34,8,8,0,0,1,232,136.66ZM120,72v56a8,8,0,0,0,8,8h56a8,8,0,0,0,0-16H136V72a8,8,0,0,0-16,0Zm40-24a12,12,0,1,0-12-12A12,12,0,0,0,160,48Zm36,24a12,12,0,1,0-12-12A12,12,0,0,0,196,72Zm24,36a12,12,0,1,0-12-12A12,12,0,0,0,220,108Z">
                                                            </path>
                                                        </svg>
                                                        <p>Fin
                                                            le {{ $training->completed_at->translatedFormat('d F Y') }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex min-w-max">
                                                    <a href="{{ route('formation-details', $training) }}"
                                                       wire:navigate
                                                       class="btn btn-sm rounded-md w-full justify-center text-white bg-primary">
                                                        Suivre
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <div>
                                    <a href="#certifications"
                                       class="group relative w-max btn btn-sm sm:btn-md justify-center overflow-hidden rounded-md btn-solid bg-primary-50 text-primary-800 hover:text-primary-50">
                                        <div class="flex items-center relative z-10">
                                            <span>Decouvrir plus</span>
                                            <div class="ml-1 transition duration-500 group-hover:rotate-[360deg]">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                     class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                                                </svg>

                                            </div>
                                        </div>
                                        <span data-btn-layer class=" before:bg-primary-600"></span>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div
                                class="w-full flex flex-col items-center px-5 sm:px-10 py-8 sm:py-16 lg:py-20 lg:px-16">
                                <svg class="mx-auto" xmlns="http://www.w3.org/2000/svg" width="168" height="164"
                                     viewBox="0 0 168 164" fill="none">
                                    <g filter="url(#filter0_d_14133_736)">
                                        <path
                                            d="M3.99988 81.0083C3.99988 36.7097 39.9078 1 84.0081 1C128.042 1 164 36.6932 164 81.0083C164 99.8046 157.525 117.098 146.657 130.741C131.676 149.653 108.784 161 84.0081 161C59.0675 161 36.3071 149.57 21.3427 130.741C10.4745 117.098 3.99988 99.8046 3.99988 81.0083Z"
                                            fill="#ECFDF5"/>
                                    </g>
                                    <path
                                        d="M145.544 77.4619H146.044V76.9619V48.9851C146.044 43.424 141.543 38.9227 135.982 38.9227H67.9223C64.839 38.9227 61.9759 37.3578 60.3174 34.7606L60.3159 34.7583L56.8477 29.3908L56.8472 29.3901C54.9884 26.5237 51.8086 24.7856 48.3848 24.7856H26.4195C20.8584 24.7856 16.3571 29.287 16.3571 34.848V76.9619V77.4619H16.8571H145.544Z"
                                        fill="#D1FAE5" stroke="#6EE7B7"/>
                                    <path
                                        d="M63.9999 26.2856C63.9999 25.7334 64.4476 25.2856 64.9999 25.2856H141.428C143.638 25.2856 145.428 27.0765 145.428 29.2856V33.8571H67.9999C65.7907 33.8571 63.9999 32.0662 63.9999 29.8571V26.2856Z"
                                        fill="#6EE7B7"/>
                                    <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                             transform="matrix(-1 0 0 1 46.8571 31)" fill="#10B981"/>
                                    <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                             transform="matrix(-1 0 0 1 38.2859 31)" fill="#10B981"/>
                                    <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                             transform="matrix(-1 0 0 1 29.7141 31)" fill="#10B981"/>
                                    <path
                                        d="M148.321 126.907L148.321 126.906L160.559 76.3043C162.7 67.5161 156.036 59.0715 147.01 59.0715H14.5902C5.56258 59.0715 -1.08326 67.5168 1.04059 76.3034L1.04064 76.3036L13.2949 126.906C14.9181 133.621 20.9323 138.354 27.8354 138.354H133.764C140.685 138.354 146.681 133.621 148.321 126.907Z"
                                        fill="#FFFFFF" stroke="#D1FAE5"/>
                                    <path
                                        d="M86.3858 109.572C85.2055 109.572 84.2268 108.593 84.2268 107.384C84.2268 102.547 76.9147 102.547 76.9147 107.384C76.9147 108.593 75.9359 109.572 74.7269 109.572C73.5466 109.572 72.5678 108.593 72.5678 107.384C72.5678 96.7899 88.5737 96.8186 88.5737 107.384C88.5737 108.593 87.5949 109.572 86.3858 109.572Z"
                                        fill="#10B981"/>
                                    <path
                                        d="M104.954 91.0616H95.9144C94.7053 91.0616 93.7265 90.0829 93.7265 88.8738C93.7265 87.6935 94.7053 86.7147 95.9144 86.7147H104.954C106.163 86.7147 107.141 87.6935 107.141 88.8738C107.141 90.0829 106.163 91.0616 104.954 91.0616Z"
                                        fill="#10B981"/>
                                    <path
                                        d="M65.227 91.0613H56.1877C54.9787 91.0613 53.9999 90.0825 53.9999 88.8734C53.9999 87.6931 54.9787 86.7144 56.1877 86.7144H65.227C66.4073 86.7144 67.3861 87.6931 67.3861 88.8734C67.3861 90.0825 66.4073 91.0613 65.227 91.0613Z"
                                        fill="#10B981"/>
                                    <circle cx="142.572" cy="121" r="24.7857" fill="#D1FAE5" stroke="#6EE7B7"/>
                                    <path
                                        d="M152.214 130.643L149.535 127.964M150.071 119.928C150.071 115.195 146.234 111.357 141.5 111.357C136.766 111.357 132.928 115.195 132.928 119.928C132.928 124.662 136.766 128.5 141.5 128.5C143.858 128.5 145.993 127.548 147.543 126.007C149.104 124.455 150.071 122.305 150.071 119.928Z"
                                        stroke="#10B981" stroke-width="1.6" stroke-linecap="round"/>
                                    <defs>
                                        <filter id="filter0_d_14133_736" x="1.99988" y="0" width="164"
                                                height="164" filterUnits="userSpaceOnUse"
                                                color-interpolation-filters="sRGB">
                                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                            <feColorMatrix in="SourceAlpha" type="matrix"
                                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                                           result="hardAlpha"/>
                                            <feOffset dy="1"/>
                                            <feGaussianBlur stdDeviation="1"/>
                                            <feComposite in2="hardAlpha" operator="out"/>
                                            <feColorMatrix type="matrix"
                                                           values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.05 0"/>
                                            <feBlend mode="normal" in2="BackgroundImageFix"
                                                     result="effect1_dropShadow_14133_736"/>
                                            <feBlend mode="normal" in="SourceGraphic"
                                                     in2="effect1_dropShadow_14133_736" result="shape"/>
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
                @forelse($trainings as $training)
                    <div wire:key="{{ $training->id }}"
                         class="bg-bg border border-border-light overflow-hidden shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50 rounded-lg">
                        <div class="rounded bg-bg-light aspect-video">
                            <img src="{{ asset('storage/' . $training->images) }}" alt="{{ $training->title }}"
                                 width="2000" height="1333" class="w-full h-full rounded-md object-cover">
                        </div>
                        <div class="p-6">
                            <h2>
                                <a href="{{ route('formation-details', $training) }}" wire:navigate
                                   class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                    {{ $training->title }}
                                </a>
                            </h2>
                            <p class="my-4 text-fg-subtext line-clamp-1">
                                {!! str($training->description)->limit(80) !!}
                            </p>
                            <a href="{{ route('formation-details', $training) }}" wire:navigate
                               class="btn btn-md rounded w-full justify-center mt-7 btn-solid bg-primary-600 text-white group">
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
                                        fill="#ECFDF5"/>
                                </g>
                                <path
                                    d="M145.544 77.4619H146.044V76.9619V48.9851C146.044 43.424 141.543 38.9227 135.982 38.9227H67.9223C64.839 38.9227 61.9759 37.3578 60.3174 34.7606L60.3159 34.7583L56.8477 29.3908L56.8472 29.3901C54.9884 26.5237 51.8086 24.7856 48.3848 24.7856H26.4195C20.8584 24.7856 16.3571 29.287 16.3571 34.848V76.9619V77.4619H16.8571H145.544Z"
                                    fill="#D1FAE5" stroke="#6EE7B7"/>
                                <path
                                    d="M63.9999 26.2856C63.9999 25.7334 64.4476 25.2856 64.9999 25.2856H141.428C143.638 25.2856 145.428 27.0765 145.428 29.2856V33.8571H67.9999C65.7907 33.8571 63.9999 32.0662 63.9999 29.8571V26.2856Z"
                                    fill="#6EE7B7"/>
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                         transform="matrix(-1 0 0 1 46.8571 31)" fill="#10B981"/>
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                         transform="matrix(-1 0 0 1 38.2859 31)" fill="#10B981"/>
                                <ellipse cx="1.42857" cy="1.42857" rx="1.42857" ry="1.42857"
                                         transform="matrix(-1 0 0 1 29.7141 31)" fill="#10B981"/>
                                <path
                                    d="M148.321 126.907L148.321 126.906L160.559 76.3043C162.7 67.5161 156.036 59.0715 147.01 59.0715H14.5902C5.56258 59.0715 -1.08326 67.5168 1.04059 76.3034L1.04064 76.3036L13.2949 126.906C14.9181 133.621 20.9323 138.354 27.8354 138.354H133.764C140.685 138.354 146.681 133.621 148.321 126.907Z"
                                    fill="#FFFFFF" stroke="#D1FAE5"/>
                                <path
                                    d="M86.3858 109.572C85.2055 109.572 84.2268 108.593 84.2268 107.384C84.2268 102.547 76.9147 102.547 76.9147 107.384C76.9147 108.593 75.9359 109.572 74.7269 109.572C73.5466 109.572 72.5678 108.593 72.5678 107.384C72.5678 96.7899 88.5737 96.8186 88.5737 107.384C88.5737 108.593 87.5949 109.572 86.3858 109.572Z"
                                    fill="#10B981"/>
                                <path
                                    d="M104.954 91.0616H95.9144C94.7053 91.0616 93.7265 90.0829 93.7265 88.8738C93.7265 87.6935 94.7053 86.7147 95.9144 86.7147H104.954C106.163 86.7147 107.141 87.6935 107.141 88.8738C107.141 90.0829 106.163 91.0616 104.954 91.0616Z"
                                    fill="#10B981"/>
                                <path
                                    d="M65.227 91.0613H56.1877C54.9787 91.0613 53.9999 90.0825 53.9999 88.8734C53.9999 87.6931 54.9787 86.7144 56.1877 86.7144H65.227C66.4073 86.7144 67.3861 87.6931 67.3861 88.8734C67.3861 90.0825 66.4073 91.0613 65.227 91.0613Z"
                                    fill="#10B981"/>
                                <circle cx="142.572" cy="121" r="24.7857" fill="#D1FAE5" stroke="#6EE7B7"/>
                                <path
                                    d="M152.214 130.643L149.535 127.964M150.071 119.928C150.071 115.195 146.234 111.357 141.5 111.357C136.766 111.357 132.928 115.195 132.928 119.928C132.928 124.662 136.766 128.5 141.5 128.5C143.858 128.5 145.993 127.548 147.543 126.007C149.104 124.455 150.071 122.305 150.071 119.928Z"
                                    stroke="#10B981" stroke-width="1.6" stroke-linecap="round"/>
                                <defs>
                                    <filter id="filter0_d_14133_736" x="1.99988" y="0" width="164" height="164"
                                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                        <feColorMatrix in="SourceAlpha" type="matrix"
                                                       values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                                       result="hardAlpha"/>
                                        <feOffset dy="1"/>
                                        <feGaussianBlur stdDeviation="1"/>
                                        <feComposite in2="hardAlpha" operator="out"/>
                                        <feColorMatrix type="matrix"
                                                       values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.05 0"/>
                                        <feBlend mode="normal" in2="BackgroundImageFix"
                                                 result="effect1_dropShadow_14133_736"/>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_14133_736"
                                                 result="shape"/>
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
