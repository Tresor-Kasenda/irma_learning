<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-hidden overflow-y-auto bg-bg min-h-screen">
<span data-nav-overlay data-navbar-id="app-main" aria-hidden="true"
      class="flex invisible opacity-0 bg-gray-800/50 backdrop-blur-xl fx-open:visible fx-open:opacity-100 fixed inset-0 z-40 lg:invisible lg:hidden"></span>
<header class="flex items-center absolute top-0 w-full z-50 pt-5 px-5 xl:pt-7">
    <nav
        class="mx-auto w-full max-w-7xl flex items-center justify-between gap-10 px-5 bg-bg/10 border border-border-light/30 shadow shadow-bg-light/40 py-1.5 rounded-lg">
        <div class="lg:min-w-max flex">
            <a href="#" aria-label="Page Accueil du Site Betterlife" class="flex items-center w-max gap-1">
                <img src="{{ asset('images/irma-logo-base.svg') }}" alt="logo Irma" width="200" height="100"
                     class="h-12 w-auto">
                <img src="{{ asset('images/irma-text-primary.svg') }}" alt="Irma Text" width="131" height="51.53"
                     class="h-12 w-auto max-[500px]:hidden">
            </a>
        </div>
        <div data-main-navbar id="app-main"
             class="lg:flex-1 lg:justify-start flex items-center gap-3 rounded-xl p-3 lg:p-0 z-[80] lg:z-auto navbar-before navbar-base navbar-visibility navbar-opened lg:rounded-none navbar-content-scrollable">
            <ul class="flex items-center flex-col lg:flex-row gap-3 lg:gap-5 text-fg *:flex w-full h-max">
                <li class="relative flex w-full lg:w-max group">
                    <a href="./formations.html" aria-label="Lien vers la page : Accueil"
                       class="py-2 ease-linear duration-100 inline-flex hover:text-primary-700">
                        Formations
                    </a>
                </li>
                <li class="relative flex w-full lg:w-max group">
                    <a href="./certification.html" aria-label="Lien vers la page : Accueil"
                       class="py-2 ease-linear duration-100 inline-flex hover:text-primary-700">
                        Certification
                    </a>
                </li>
                <li class="relative flex w-full lg:w-max group">
                    <a href="./formation-continue.html" aria-label="Lien vers la page : Accueil"
                       class="py-2 ease-linear duration-100 inline-flex hover:text-primary-700">
                        Formation continue
                    </a>
                </li>
                <li class="relative flex w-full lg:w-max group">
                    <a target="_blank" href="#" aria-label="Lien vers la page : Accueil"
                       class="py-2 ease-linear duration-100 inline-flex hover:text-primary-700">
                        iRMA Association
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             class="size-3 ml-0.5 mb-2">
                            <path fill-rule="evenodd"
                                  d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Z"
                                  clip-rule="evenodd"/>
                            <path fill-rule="evenodd"
                                  d="M6.194 12.753a.75.75 0 0 0 1.06.053L16.5 4.44v2.81a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5a.75.75 0 0 0 0 1.5h2.553l-9.056 8.194a.75.75 0 0 0-.053 1.06Z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
        <div class="lg:min-w-max flex justify-end items-center gap-x-2">
            <a href="{{ route('login') }}"
               class="btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group">
          <span class="relative z-10">
            Se connecter
          </span>
                <span data-btn-layer class=" before:bg-primary-800"></span>
            </a>
            <div class="flex lg:hidden pr-0.5 py-1 border-l border-gray-200/80 -mr-2.5">
                <button data-nav-trigger data-toggle-nav="app-main" data-expanded="false"
                        class="px-2.5 relative z-[90] space-y-2 group" aria-label="toggle navbar">
            <span class="h-0.5 flex w-6 rounded bg-fg transition duration-300 group-aria-expanded:rotate-45" id="line-1"
                  aria-hidden="true"></span>
                    <span class="h-0.5 flex w-6 rounded bg-fg transition duration-300 group-aria-expanded:rotate-45"
                          id="line-2"
                          aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </nav>
</header>
<div class="absolute top-0 left-0 inset-x-0 h-40 flex">
    <span class="flex w-60 h-36 bg-gradient-to-tr from-primary rounded-full blur-2xl opacity-65"></span>
</div>
<main>
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
                    <a href="./adhesion.html" class="btn btn-md md:btn-lg btn-solid bg-primary-600
                          text-white group">
              <span class="relative z-10">
                Certification professionnelle
              </span>
                        <span data-btn-layer class=" before:bg-primary-800"></span>
                    </a>
                    <a href="#" class="btn btn-md md:btn-lg flex items-center gap-2">
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
            <div
                class="max-w-4xl flex lg:flex-1 relative before:absolute before:inset-y-3 md:before:inset-y-10 before:border-y before:border-border-light after:border-border-light before:w-full after:absolute after:inset-x-3 md:after:inset-x-10 after:border-x after:h-full">
                <div class="flex items-center justify-center relative px-4 py-6 w-full h-full">
                    <div
                        class="w-full flex flex-col max-w-sm bg-bg-lighter backdrop-blur-lg border border-border/30 rounded-lg shadow-sm shadow-gray-100/40 hover:shadow-gray-200/60 ease-linear p-6 relative z-[5]">
                        <img src="/image2.webp" alt="Image" width="2000" height="1333"
                             class="w-full aspect-video rounded-md object-cover">
                        <div class="mt-6">
                            <h1 class="font-semibold text-fg-title text-xl line-clamp-2">
                                Master Class Marketing
                            </h1>
                            <h2 class=" mt-4 line-clamp-2 text-fg">
                                Dans le cadre du partenariat et de la coopération entre Irma et l'Aarca, une formation
                                de renforcement
                                des capacités sera lancée sur l'étendue de la République.
                            </h2>
                            <div class="flex flex-col gap-1 mt-4">
                                <div class="flex items-center gap-3 text-fg-subtext">
                    <span class="text-fg-title text-sm font-light">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                           stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z"/>
                      </svg>
                    </span>
                                    <span class="text-sm">12/12/2022</span>
                                </div>
                                <div class="mt-5 flex w-full">
                                    <a href="./adhesion.html" class="w-full justify-center btn btn-sm sm:btn-md btn-solid bg-primary-600
                                        text-white group">
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
          <span aria-hidden="true" class="text-white bg-primary rounded size-12 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="size-7">
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
          <span aria-hidden="true" class="text-white bg-primary rounded size-12 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="size-7">
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
            <img src="/image2.webp" alt="event image" class="w-full h-full md:max-h-none object-cover">
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
            <a href="./formations.html"
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
                <img src="/logos/samara-logo.png" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="/logos/samara-logo.png" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="/logos/tfm-logo.png" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="/logos/yetu-logo.png" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="/logos/trading-logo.jpg" alt="Samara logo" class="h-14 w-auto">
            </a>
            <a href="#" target="_blank" class="flex">
                <img src="/logos/pmm-logo.png" alt="Samara logo" class="h-14 w-auto">
            </a>
        </div>
    </section>
</main>


<footer class="w-full mt-10 border-t border-border-light/80 pt-10">
    <div class="mx-auto max-w-7xl px-5 sm:px-10">
        <!--Grid-->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-10 py-10 max-sm:max-w-sm max-sm:mx-auto gap-y-8">
            <div class="col-span-full lg:col-span-3 lg:pr-16">
                <a href="./" class="flex justify-center lg:justify-start">

                </a>
                <a href="/" aria-label="Page Accueil du Site Betterlife" class="flex w-max gap-2 items-center">
                    <img src="/irma-logo-base.svg" alt="logo Irma" width="200" height="100" class="h-14 w-auto">
                    <img src="/irma-text-primary.svg" alt="Irma Text" width="131" height="51.53" class="h-12 w-auto">
                </a>
                <div
                    class="flex flex-col divide-y divide-border *:py-2 first:*:pt-0 last:*:pb-0 mt-8 p-4 bg-bg-lighter rounded-md">
                    <div class="flex items-center gap-3">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="text-sm text-fg">
                269, Av. KASONGO NYEMBO, Q/ Baudouin, Lubumbashi, RD Congo
              </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="text-sm text-fg">
                2, Avenue Père Boka, Commune de la Gombe, Kinshasa, RD Congo
              </span>
                    </div>
                    <a href="mailto:communication@irmardc.org" class="flex items-center gap-3 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z"/>
                        </svg>

                        <span class="text-sm text-fg group-hover:text-primary-600">
                communication@irmardc.org
              </span>
                    </a>
                    <a href="tel:+" class="flex items-center gap-3 group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                        </svg>
                        <span class="text-sm text-fg group-hover:text-primary-600">
                +243 819 742 171
              </span>
                    </a>
                </div>
            </div>


            <div class="col-span-2 flex flex-col space-y-7">
                <h4 class="text-lg text-fg-title font-medium">Navigation</h4>
                <ul class="text-sm transition-all duration-500 grid grid-cols-2 gap-x-8 gap-y-6 text-fg">
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                </ul>
            </div>
            <!--End Col-->
            <div class="flex flex-col space-y-7">
                <h4 class="text-lg text-fg-title font-medium">Ressources</h4>
                <ul class="text-sm  transition-all duration-500 grid gap-y-6 text-fg">
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            Certification
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            Formation
                        </a>
                    </li>
                    <li class="flex">
                        <a href="#" class="hover:text-fg-title">
                            A propos de nous
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!--Grid-->
        <div class="py-7 border-t border-gray-200">
            <div class="flex items-center justify-center flex-col lg:justify-between lg:flex-row">
          <span class="text-sm text-gray-500 ">© Irma <span data-current-year>2025</span>, All
            rights reserved.</span>
                <div class="flex mt-4 space-x-4 sm:justify-center lg:mt-0 ">
                    <a href="javascript:;"
                       class="w-9 h-9 rounded-full bg-gray-700 flex justify-center items-center hover:bg-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <g id="Social Media">
                                <path id="Vector"
                                      d="M11.3214 8.93666L16.4919 3.05566H15.2667L10.7772 8.16205L7.1914 3.05566H3.05566L8.47803 10.7774L3.05566 16.9446H4.28097L9.022 11.552L12.8088 16.9446H16.9446L11.3211 8.93666H11.3214ZM9.64322 10.8455L9.09382 10.0765L4.72246 3.95821H6.60445L10.1322 8.8959L10.6816 9.66481L15.2672 16.083H13.3852L9.64322 10.8458V10.8455Z"
                                      fill="white"/>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
