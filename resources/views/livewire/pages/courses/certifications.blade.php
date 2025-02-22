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
                            <a href="./" aria-label="Lien vers la page principale">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                </svg>
                            </a>
                            <div class="text-fg-subtext">
                                Certification
                            </div>
                        </div>
                        <h1 class="text-fg-title text-4xl/tight md:text-5xl/tight xl:text-6xl/tight">
                            Certification Professionnelle
                        </h1>
                        <p class="text-fg max-w-lg">
                            Développez vos compétences et validez votre expertise
                        </p>
                    </div>
                    <div
                        class="lg:w-1/2 lg:flex-1 flex flex-col p-4 xl:p-5 rounded-md bg-white shadow-sm sahdow-gray-100/20">
                        <span class="mb-4 flex text-fg-title font-semibold">Certification en cours</span>
                        <ul
                            class="flex flex-col gap-4 divide-y divide-gray-100/70 *:py-2 first:*:pt-0 last:*:pb-0 mb-5">
                            @foreach($formations as $masterClass)
                                <li class="flex-1 flex items-start gap-3" wire:key="{{ $masterClass->id }}">
                                    <div class="p-2 rounded bg-bg-light text-primary flex min-w-max">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                             fill="currentColor" viewBox="0 0 256 256">
                                            <path
                                                d="M128,136a8,8,0,0,1-8,8H72a8,8,0,0,1,0-16h48A8,8,0,0,1,128,136Zm-8-40H72a8,8,0,0,0,0,16h48a8,8,0,0,0,0-16Zm112,65.47V224A8,8,0,0,1,220,231l-24-13.74L172,231A8,8,0,0,1,160,224V200H40a16,16,0,0,1-16-16V56A16,16,0,0,1,40,40H216a16,16,0,0,1,16,16V86.53a51.88,51.88,0,0,1,0,74.94ZM160,184V161.47A52,52,0,0,1,216,76V56H40V184Zm56-12a51.88,51.88,0,0,1-40,0v38.22l16-9.16a8,8,0,0,1,7.94,0l16,9.16Zm16-48a36,36,0,1,0-36,36A36,36,0,0,0,232,124Z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                        <div class="mb-4 sm:mb-0 sm:mr-4">
                                            <h3 class="font-semibold text-fg-subtitle line-clamp-2">
                                                {{ $masterClass->title }}
                                            </h3>
                                            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                     fill="currentColor" class="size-4" viewBox="0 0 256 256">
                                                    <path
                                                        d="M232,136.66A104.12,104.12,0,1,1,119.34,24,8,8,0,0,1,120.66,40,88.12,88.12,0,1,0,216,135.34,8,8,0,0,1,232,136.66ZM120,72v56a8,8,0,0,0,8,8h56a8,8,0,0,0,0-16H136V72a8,8,0,0,0-16,0Zm40-24a12,12,0,1,0-12-12A12,12,0,0,0,160,48Zm36,24a12,12,0,1,0-12-12A12,12,0,0,0,196,72Zm24,36a12,12,0,1,0-12-12A12,12,0,0,0,220,108Z">
                                                    </path>
                                                </svg>
                                                <p>Fin le 30 Juin 2024</p>
                                            </div>
                                        </div>
                                        <div class="flex min-w-max">
                                            <a
                                                href="{{ route('learning-course-student', $masterClass) }}"
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
        </div>
    </section>
    <section id="certifications"
             class="my-32 scroll-mt-20 w-full flex flex-col gap-16">
        <div
            class="mx-auto max-w-7xl w-full px-5 sm:px-10 border border-border-lighter bg-bg/30 rounded-md h-14 sticky top-2 z-20 backdrop-blur-sm flex justify-between items-center">
            <div class="text-sm md:text-base text-fg-subtext">
                Nos formations
            </div>
            <div class="flex gap-3 items-center">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                         class="size-4 text-fg-subtext absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                         viewBox="0 0 256 256">
                        <path
                            d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z">
                        </path>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher"
                        class="ui-form-input px-4 h-9 rounded-md peer w-full ps-9"
                    />
                </div>
                <select class="ui-form-input px-4 h-9 rounded-md peer pe-9 text-fg">
                    <option>Type de formations</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                </select>
            </div>
        </div>
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 grid">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($formations as $formation)
                    <div
                        class="bg-bg border border-border-light overflow-hidden shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50 rounded-lg">
                        <div class="rounded bg-bg-light aspect-video">
                            <img
                                src="{{ asset('storage/'. $formation->path) }}"
                                alt="{{ $formation->title  }}"
                                width="2000"
                                height="1333"
                                class="w-full h-full rounded-md object-cover">
                        </div>
                        <div class="p-6">
                            <h2>
                                <a href="{{ route('master-class', ['masterClass' => $formation]) }}" wire:navigate
                                   class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                    {{ $formation->title }}
                                </a>
                            </h2>
                            <p class="my-4 text-fg-subtext line-clamp-1">
                                {!! str($formation->description)->limit(80) !!}
                            </p>
                            <div class="flex items-center justify-between text-sm text-fg-subtext">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                         class="size-4" viewBox="0 0 256 256">
                                        <path
                                            d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z">
                                        </path>
                                    </svg>
                                    {{ $formation->duration }}H
                                </div>
                                <div class="flex items-center gap-1 font-semibold text-fg-subtitle text-xl">
                                    {{ $formation?->price }}
                                </div>
                            </div>
                            <a href="{{ route('master-class', ['masterClass' => $formation]) }}" wire:navigate
                               class="btn btn-md rounded w-full justify-center mt-7 btn-solid bg-primary-600 text-white group">
                                <span class="relative z-10">
                                    Suivre la formation
                                </span>
                                <span data-btn-layer class=" before:bg-primary-800"></span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-bg border border-border-light overflow-hidden shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50 rounded-lg">
                        <div class="rounded bg-bg-light aspect-video">
                            <img src="/image1.webp" alt="Image" width="2000" height="1333"
                                 class="w-full h-full rounded-md object-cover">
                        </div>
                        <div class="p-6">
                            <h2>
                                <a href="./details.html"
                                   class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                    Formation title
                                </a>
                            </h2>
                            <p class="my-4 text-fg-subtext line-clamp-1">
                                Lorem ipsum dolor sit, amet consectetur adipisicing elit
                            </p>
                            <div class="flex items-center justify-between text-sm text-fg-subtext">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                         class="size-4" viewBox="0 0 256 256">
                                        <path
                                            d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z">
                                        </path>
                                    </svg>
                                    12H
                                </div>
                                <div class="flex items-center gap-1 font-semibold text-fg-subtitle text-xl">
                                    120$
                                </div>
                            </div>
                            <a href="./adhesion.html"
                               class="btn btn-md rounded w-full justify-center mt-7 btn-solid bg-primary-600 text-white group">
                                <span class="relative z-10">
                                    Suivre la formation
                                </span>
                                <span data-btn-layer class=" before:bg-primary-800"></span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
