<div>
    <section class="pt-32">
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col lg:flex-row gap-16">
            <div
                class="lg:w-1/2 lg:py-12 xl:py-20 space-y-7 flex flex-col items-center lg:items-start text-center lg:text-left">
                <div
                    class="mx-auto lg:mx-0 w-max flex py-1 shadow shadow-bg-light/60 border border-border/60 rounded-full pr-2 pl-1.5 bg-bg text-fg-subtitle">
                    <div class="flex items-center -space-x-2 *:size-6 *:object-cover *:rounded-full">
                        <img src="{{ asset('images/avatar.webp') }}" width="200" height="200" alt="Avatar ise" class="">
                        <img src="{{ asset('images/avatar.webp') }}" width="200" height="200" alt="Avatar ise" class="">
                        <img src="{{ asset('images/avatar.webp') }}" width="200" height="200" alt="Avatar ise" class="">
                    </div>
                    <span class="ml-1 text-sm text-fg flex items-center">
                        300+ Certifies
                    </span>
                </div>
                <h1
                    class="text-fg-title text-4xl/tight md:text-5xl/tight xl:text-6xl/tight max-w-3xl lg:max-w-none mx-auto lg:mx-0">
                    Devenez Leader du Risque en RD Congo avec l’iRMA
                </h1>
                <p class="text-fg max-w-lg mx-auto lg:mx-0">
                    Des formations de haut niveau, délivrées par des professionnels pour des professionnels.
                </p>
                <div class="flex items-center justify-center lg:justify-start flex-wrap gap-3">
                    <a
                        href="{{ route('login') }}"
                        wire:navigate
                        class="btn btn-md md:btn-lg btn-solid bg-primary-600 text-white group">
                        <span class="relative z-10">
                            Certification professionnelle
                        </span>
                        <span data-btn-layer class=" before:bg-primary-800"></span>
                    </a>
                    <a href="{{ route('formations') }}" wire:navigate
                       class="btn btn-md md:btn-lg flex items-center gap-2">
                        Formation continue
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                             class="ml-1 size-5"
                             viewBox="0 0 256 256">
                            <path
                                d="M221.66,133.66l-72,72a8,8,0,0,1-11.32-11.32L196.69,136H40a8,8,0,0,1,0-16H196.69L138.34,61.66a8,8,0,0,1,11.32-11.32l72,72A8,8,0,0,1,221.66,133.66Z">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>
            @if($course)
                <div
                    class="max-w-4xl flex lg:flex-1 relative before:absolute before:inset-y-3 md:before:inset-y-10 before:border-y before:border-border-light after:border-border-light before:w-full after:absolute after:inset-x-3 md:after:inset-x-10 after:border-x after:h-full">
                    <div class="flex items-center justify-center relative px-4 py-6 w-full h-full">
                        <div
                            class="w-full flex flex-col max-w-sm bg-bg-lighter backdrop-blur-lg border border-border/30 rounded-lg shadow-sm shadow-gray-100/40 hover:shadow-gray-200/60 ease-linear p-6 relative z-[5]">
                            <img
                                src="{{ asset('storage', $course->path) }}"
                                alt="{{ $course->title }}"
                                width="2000"
                                height="1333"
                                class="w-full aspect-video rounded-md object-cover">
                            <div class="mt-6">
                                <h1 class="font-semibold text-fg-title text-xl line-clamp-2">
                                    {{ $course->title }}
                                </h1>
                                <h2 class=" mt-4 line-clamp-2 text-fg">
                                    {!! str($course->description)->limit(88) !!}
                                </h2>
                                <div class="flex flex-col gap-1 mt-4">
                                    <div class="flex items-center gap-3 text-fg-subtext">
                                    <span class="text-fg-title text-sm font-light">
                                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                               stroke-width="1.5"
                                               stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z"/>
                                          </svg>
                                    </span>
                                        <span class="text-sm">{{ $course->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="mt-5 flex w-full">
                                        <a
                                            href="{{ route('master-class', ['masterClass' => $course]) }}"
                                            wire:navigate
                                            class="w-full justify-center btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group">
                                      <span class="relative z-10">
                                        En savoir plus
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

    <section class="my-32 mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col gap-16">
        <div class="flex flex-col md:flex-row md:justify-between gap-12 mx-auto w-full">
            <div class="flex flex-col relative max-w-xl">
                <h2
                    class="font-medium relative text-3xl md:text-4xl text-fg-title  before:absolute before:h-1 before:w-20 before:top-0 before:left-0 before:bg-primary-900 before:rounded-full pt-5">
                    Nos services principaux
                </h2>
            </div>
            <div class="max-w-md">
                <p class="text-fg">
                    Une approche complète de la gestion des risques et de la conformité
                </p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
                class="flex flex-col bg-bg border border-border-light rounded-md shadow-sm shadow-gray-100/40 p-5 md:p-6 xl:p-8">
          <span aria-hidden="true" class="text-white bg-primary rounded size-12 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="size-7">
              <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
              <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
              <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
              <path d="M10 6h4"/>
              <path d="M10 10h4"/>
              <path d="M10 14h4"/>
              <path d="M10 18h4"/>
            </svg>
          </span>
                <h2 class="text-lg font-semibold text-fg-title flex flex-1 mt-6">
                    Assurances
                </h2>
                <p class="mt-3 text-fg">
                    Des formations de haut niveau, délivrées par des professionnels pour des professionnels en
                    assurances,
                    offrant une grande accessibilité, moins de contraintes espace-temps et une meilleure gestion
                    étude-travail-vie personnelle.
                </p>
            </div>
            <div
                class="flex flex-col bg-bg border border-border-light rounded-md shadow-sm shadow-gray-100/40 p-5 md:p-6 xl:p-8">
                  <span aria-hidden="true"
                        class="text-white bg-primary rounded size-12 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"
                         class="size-7">
                      <circle cx="16" cy="20" r="2"/>
                      <path
                          d="M10 20H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H20a2 2 0 0 1 2 2v2"/>
                      <path d="m22 14-4.5 4.5"/>
                      <path d="m21 15 1 1"/>
                    </svg>
                  </span>
                <h2 class="text-lg font-semibold text-fg-title flex flex-1 mt-6">
                    Risk Management
                </h2>
                <p class="mt-3 text-fg">
                    Des formations de haut niveau, délivrées par des professionnels pour des professionnels en Risk
                    Management,
                    offrant une grande accessibilité, moins de contraintes espace-temps et une meilleure gestion
                    étude-travail-vie personnelle.
                </p>
            </div>
            <div
                class="flex flex-col bg-bg border border-border-light rounded-md shadow-sm shadow-gray-100/40 p-5 md:p-6 xl:p-8">
                  <span aria-hidden="true"
                        class="text-white bg-primary rounded size-12 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"
                         class="size-7">
                      <path d="m16 16 2 2 4-4"/>
                      <path
                          d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/>
                      <path d="m7.5 4.27 9 5.15"/>
                      <polyline points="3.29 7 12 12 20.71 7"/>
                      <line x1="12" x2="12" y1="22" y2="12"/>
                    </svg>
                  </span>
                <h2 class="text-lg font-semibold text-fg-title flex flex-1 mt-6">
                    Compliance
                </h2>
                <p class="mt-3 text-fg">
                    Des formations de haut niveau, délivrées par des professionnels pour des professionnels en
                    Compliance,
                    offrant une grande accessibilité, moins de contraintes espace-temps et une meilleure gestion
                    étude-travail-vie personnelle.
                </p>
            </div>
        </div>
    </section>

    <section class="my-32 mx-auto max-w-7xl w-full px-5 sm:px-10 flex-col flex md:flex-row">
        <div class="md:w-1/2 lg:pr-10 xl:pr-16 relative">
            <img src="{{ asset('images/image2.webp') }}" alt="event image"
                 class="w-full h-full md:max-h-none object-cover">
        </div>
        <div class="flex-1 flex flex-col md:py-5 lg:py-8">
            <h2
                class="font-medium relative text-3xl md:text-4xl text-fg-title  before:absolute before:h-1 before:w-20 before:top-0 before:left-0 before:bg-primary-900 before:rounded-full pt-5">
                Formations d’excellence, flexibles et accessibles pour les professionnels
            </h2>
            <p class="mt-4 text-fg">L'iRMA offre 3 types de formation</p>
            <ul class="mt-5 text-fg-subtext grid gap-y-4">
                <li class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-5 mr-3 mt-0.5 text-primary rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="flex flex-1">Les certifications professionnelles à travers des formations menant à des Titres
              Professionnels qui témoignent de l’autorité et de la crédibilité professionnelles des membres. Chaque
              membre doit totaliser au moins 25 points DPC par période de douze mois afin de conserver son titre
              professionnel.</span>
                </li>
                <li class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-5 mr-3 mt-0.5 text-primary rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="flex flex-1">
              La Formation Continue à travers de courts programmes qui permettent aux membres d’acquérir, d’actualiser
              ou d’améliorer rapidement leur compétence tout au long de leur vie professionnelle. Chaque membre doit
              cumuler au moins 20 Unités de Formation Continue (UFC) par période de référence de 12 mois ans allant du
              1er janvier.
            </span>
                </li>
                <li class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-5 mr-3 mt-0.5 text-primary rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="flex flex-1">
              La Formation en Entreprise livrées en respectant les besoins spécifiques des sociétés à travers une
              démarche partenariale sur mesure.
            </span>
                </li>
            </ul>
            <a href="{{ route('formations') }}"
               class="mt-8 btn btn-md md:btn-lg btn-solid bg-primary-600 w-max text-white group">
                  <span class="relative z-10">
                    Voir nos formations
                  </span>
                <span data-btn-layer class=" before:bg-primary-800"></span>
            </a>
        </div>
    </section>

    <section class="my-32 mx-auto max-w-7xl w-full px-5 sm:px-10 grid md:grid-cols-2 gap-16">
        <div
            class="bg-bg/90 backdrop-blur-sm border border-border-light/60 shadow-xl shadow-bg-light/40 rounded-lg p-5 md:p-6 lg:p-10 xl:p-16 max-w-2xl lg:max-w-none mx-auto lg:mx-0 flex flex-col">
            <h2 class="font-medium text-xl sm:text-2xl/snug text-fg-title">
                Modes des formations
            </h2>
            <p class="mt-4 text-fg-subtext">
                L’iRMA tient à tirer un maximum d’avantage de la transformation digitale de la formation professionnelle
                en
                offrant à ses membres des formations virtuelles et hybrides qui mélangent des sessions en présentiel et
                virtuelles en direct avec instructeur ou à la demande.
            </p>
            <p class="mt-2 text-fg-subtext">
                Le but est de répondre aux contraintes à l’acquisition et à l’amélioration des compétences et des
                connaissances des membres en réalisant plusieurs objectifs, notamment :
            </p>
            <ul class="mt-6 text-fg-subtext space-y-2">
                <li class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-4 mt-1.5 text-primary-600 rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="inline-flex flex-1">
              Limiter l’impact des contraintes temporelles, logistiques et géographiques;
            </span>
                </li>
                <li class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-4 mt-1.5 text-primary-600 rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="inline-flex flex-1">
              Proposer des modes d’apprentissages innovants et plus engageants ;
            </span>
                </li>
                <li class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-4 mt-1.5 text-primary-600 rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="inline-flex flex-1">
              Optimiser le coût et le temps de formation en entreprise ;
            </span>
                </li>
                <li class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                         stroke="currentColor" class="size-4 mt-1.5 text-primary-600 rotate-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="inline-flex flex-1">
              Maintenir la productivité des employés.
            </span>
                </li>
            </ul>
        </div>
        <div
            class="bg-bg/90 backdrop-blur-sm border border-border-light/60 shadow-xl shadow-bg-light/40 rounded-lg p-5 md:p-6 lg:p-10 xl:p-16 max-w-2xl lg:max-w-none mx-auto lg:mx-0">
            <h2 class="font-medium text-xl sm:text-2xl/snug text-fg-title">
                Mode de passage des examens
            </h2>
            <p class="mt-4 text-fg-subtext">
                Les examens sont organisés en télésurveillance ou en personne dans l’un des centres d’examen de l’iRMA
                repartis sur l’étendue du pays.
            </p>
            <p class="text-fg-subtext mt-2">
                La télésurveillance consiste à passer un examen au moyen de l’ordinateur de bureau ou portable dans un
                endroit
                privé. A l'aide de la webcaméra et du microphone de l’ordinateur, une personne surveille le participant
                pendant qu’il passe l’examen. L’intégralité de la séance d’examen sera enregistrée, et tout comportement
                inhabituel sera signalé. Un candidat qui échoue à un examen a droit à deux sessions de rattrapage.
            </p>
        </div>
    </section>

    <section class="my-10 mx-auto max-w-7xl w-full px-5 sm:px-10">
        <div class="w-full flex flex-wrap justify-center gap-6 p-4 rounded-md bg-gradient-to-tr from-lighter">
            <a href="#" target="_blank" class="flex">
                <img src="{{ asset('images/logos/samara-logo.png') }}" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="{{ asset('images/logos/tfm-logo.png') }}" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="{{ asset('images/logos/yetu-logo.png') }}" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="{{ asset('images/logos/trading-logo.jpg') }}" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="{{ asset('images/logos/pmm-logo.png') }}" alt="Samara logo" class="h-14 w-auto">
            </a>
        </div>
    </section>
</div>
