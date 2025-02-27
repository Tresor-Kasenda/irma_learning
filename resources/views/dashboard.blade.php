<div>
    <div class="absolute inset-x-0 -top-2 h-56 bg-bg-lighter rounded-b-xl"></div>

    <div class="px-4 sm:px-10 lg:px-5 xl:px-8 xl:max-w-[88rem] w-full mx-auto">
        <!-- Content -->
        <div class="relative pt-8 grid gap-8">
            <div class="flex flex-col">
                <h1 class="text-xl font-semibold text-fg-subtitle">Mes statistiques</h1>
            </div>


            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Mes formations</h3>
                        <div class="p-2 bg-primary-100 rounded-lg">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">
                            {{ $this->statistics()['total'] }}</p>
                        <p class="text-fg-subtext">Participations</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Masterclass en cours</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">
                            {{ $this->statistics()['in_progress'] }}</p>
                        <p class="text-fg-subtext">En cours</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Masterclass completees</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">
                            {{ $this->statistics()['completed'] }}</p>
                        <p class="text-fg-subtext">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-16 grid gap-8">

            <div class="flex justify-between items-center">
                <div class="text-sm md:text-base text-fg-subtext">
                    <h1 class="text-xl font-semibold text-fg-subtitle">Liste des cours</h1>
                </div>
                <div class="flex gap-3 items-center">
                    <div class="relative w-[230px] sm:w-80">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                            fill="currentColor"
                            class="size-4 text-fg-subtext absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            viewBox="0 0 256 256">
                            <path
                                d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z">
                            </path>
                        </svg>
                        <input type="text" placeholder="Rechercher"
                            class="ui-form-input px-4 h-9 rounded-md peer w-full ps-9" />
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6" wire:poll.keep-alive>
                @forelse($this->masterClasses() as $masterClass)
                    <div wire:key="{{ $masterClass->id }}"
                        class="bg-bg border border-border-light rounded-lg p-0.5 shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50">
                        <div class="p-4 sm:p-5">
                            <h2
                                class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                {{ $masterClass->title }}
                            </h2>

                            <div class="flex mt-3">
                                <span class="flex items-center gap-1 text-sm text-fg-subtext">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ $masterClass->chapters_count }} Chapitres
                                </span>
                            </div>

                            @if (auth()->user()->isSubscribedTo($masterClass))
                                <div class="mt-3 flex flex-col gap-1.5">
                                    <div class="justify-between w-full flex items-center text-sm text-fg-subtext">
                                        <span>{{ $masterClass->completed_chapters_count }}/{{ $masterClass->chapters_count }}</span>
                                        <span>{{ $masterClass->progress }}%</span>
                                    </div>
                                    <div class="w-full flex bg-bg-high rounded-full h-1">
                                        <span class="bg-primary-600 h-full rounded-full"
                                            style="width: {{ $masterClass->progress }}%;"></span>
                                    </div>
                                </div>
                                <div class="flex w-full mt-7 pb-2">
                                    @if ($masterClass->progress < 100)
                                        <a href="{{ route('learning-course-student', $masterClass) }}" wire:navigate
                                            class="w-full btn btn-md justify-center before:bg-primary-600 btn-styled-y group rounded before:rounded border border-border-light shadow-lg shadow-gray-50 text-fg-subtitle hover:text-white">
                                            <span class="relative">Continuer</span>
                                        </a>
                                    @else
                                        @if ($masterClass->examinations)
                                            @if ($masterClass->certifiable)
                                                <a href="{{ route('certificate', $masterClass) }}"
                                                    class="w-full btn btn-md justify-center before:bg-primary-600 btn-styled-y group rounded before:rounded border border-border-light shadow-lg shadow-gray-50 text-fg-subtitle hover:text-white">
                                                    <span class="relative">Voir le certificat</span>
                                                </a>
                                            @else
                                                <a href="{{ route('learning-course-student', $masterClass) }}"
                                                    wire:navigate
                                                    class="w-full btn btn-md justify-center btn-styled-y text-fg-subtitle">
                                                    Cours terminé
                                                </a>
                                            @endif
                                        @else
                                            <a href="#"
                                                class="w-full btn btn-md justify-center before:bg-primary-600 btn-styled-y group rounded before:rounded border border-border-light shadow-lg shadow-gray-50 text-fg-subtitle hover:text-white">
                                                <span class="relative">Passer l'examen</span>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            @else
                                <div class="flex w-full mt-7 pb-2">
                                    <a href="{{ route('master-class', $masterClass) }}"
                                        class="w-full btn btn-md justify-center before:bg-primary-600 btn-styled-y group rounded before:rounded border border-border-light shadow-lg shadow-gray-50 text-fg-subtitle hover:text-white">
                                        <span class="relative">S'inscrire</span>
                                    </a>
                                </div>
                            @endif
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
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
