<div>
    <section class="pt-32">
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col lg:flex-row gap-16">
            <div
                class="lg:w-1/2 lg:py-12 xl:py-20 space-y-7 flex flex-col items-center lg:items-start text-center lg:text-left">
                <h2
                    class="font-medium relative text-3xl md:text-4xl text-fg-title">
                    Devenez Leader du Risque en RD Congo avec l’iRMA
                </h2>
                <p class="mt-4 text-fg">L'iRMA offre 3 types de formation</p>
                <ul class="mt-5 text-fg-subtext grid gap-y-4">
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                            stroke="currentColor" class="size-5 mr-3 mt-0.5 text-primary rotate-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span class="flex flex-1">Les certifications professionnelles à travers des formations menant à
                            des
                            Titres
                            Professionnels qui témoignent de l’autorité et de la crédibilité professionnelles des
                            membres.
                            Chaque
                            membre doit totaliser au moins 25 points DPC par période de douze mois afin de conserver son
                            titre
                            professionnel.</span>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                            stroke="currentColor" class="size-5 mr-3 mt-0.5 text-primary rotate-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span class="flex flex-1">
                            La Formation Continue à travers de courts programmes qui permettent aux membres d’acquérir,
                            d’actualiser
                            ou d’améliorer rapidement leur compétence tout au long de leur vie professionnelle. Chaque
                            membre doit
                            cumuler au moins 20 Unités de Formation Continue (UFC) par période de référence de 12 mois
                            ans
                            allant du
                            1er janvier.
                        </span>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                            stroke="currentColor" class="size-5 mr-3 mt-0.5 text-primary rotate-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span class="flex flex-1">
                            La Formation en Entreprise livrées en respectant les besoins spécifiques des sociétés à
                            travers
                            une
                            démarche partenariale sur mesure.
                        </span>
                    </li>
                </ul>
                <a href="{{ route('certifications') }}"
                    class="mt-8 btn btn-md md:btn-lg btn-solid bg-primary-600 w-max text-white group">
                    <span class="relative z-10">
                        Voir Nos Certifications
                    </span>
                    <span data-btn-layer class=" before:bg-primary-800"></span>
                </a>
            </div>
            @if ($course)
                <div
                    class="max-w-4xl flex lg:flex-1 relative before:absolute before:inset-y-3 md:before:inset-y-10 before:border-y before:border-border-light after:border-border-light before:w-full after:absolute after:inset-x-3 md:after:inset-x-10 after:border-x after:h-full">
                    <div class="flex items-center justify-center relative px-4 py-6 w-full h-full">
                        <div
                            class="w-full flex flex-col max-w-sm bg-bg-lighter backdrop-blur-lg border border-border/30 rounded-lg shadow-sm shadow-gray-100/40 hover:shadow-gray-200/60 ease-linear p-6 relative z-[5]">
                            <img src="{{ asset('storage/' . $course->path) }}" alt="{{ $course->title }}" width="2000"
                                height="1333" class="w-full aspect-video rounded-md object-cover">
                            <div class="mt-6">
                                <h1 class="font-semibold text-fg-title text-xl line-clamp-2">
                                    {{ $course->title }}
                                </h1>
                                <h2 class=" mt-4 line-clamp-2 text-fg">
                                    {!! str($course->description)->limit(98) !!}
                                </h2>
                                <div class="flex flex-col gap-1 mt-4">
                                    <div class="flex items-center gap-3 text-fg-subtext">
                                        <span class="text-fg-title text-sm font-light">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                                            </svg>
                                        </span>
                                        <span class="text-sm">{{ $course->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="mt-5 flex w-full">
                                        <a href="{{ route('master-class', ['masterClass' => $course]) }}" wire:navigate
                                            class="w-full justify-center btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group">
                                            <span class="relative z-10">
                                                En Savoir Plus
                                            </span>
                                            <span data-btn-layer class=" before:bg-primary-800"></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>



</div>
