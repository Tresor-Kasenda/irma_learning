@php use App\Enums\MasterClassResourceEnum; @endphp
<div>
    <div aria-hidden="true" data-overlay-slid-chapter
         class="fixed inset-0 bg-fg-title/50 z-[70] backdrop-blur-sm lg:hidden lg:invisible invisible opacity-0 fx-open:opacity-100 fx-open:visible">
    </div>
    <div class="max-w-[95rem] grid lg:grid-cols-[350px_minmax(0,1fr)] w-full mx-auto">
        <aside class="side-nav-course lg:border-r border-border">
            <div
                class="h-16 border-b border-border px-4 flex items-center justify-between sticky lg:static top-0 bg-bg">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate aria-label="Retour dashboard"
                       class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 flex items-center justify-center rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                             class="size-5"
                             viewBox="0 0 256 256">
                            <path
                                d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z">
                            </path>
                        </svg>
                    </a>
                    <a href="{{ route('profile') }}" wire:navigate aria-label="Retour dashboard"
                       class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 hidden sm:flex items-center justify-center rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                             class="size-5"
                             viewBox="0 0 256 256">
                            <path
                                d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z">
                            </path>
                        </svg>
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-gray-600">Progress: {{ $this->getProgressPercentage() }}%</div>
                        <div class="w-20 h-2 bg-bg-high rounded-full">
                            <div class="h-full bg-primary rounded-full"
                                 style="width: {{ $this->getProgressPercentage() }}%;"></div>
                        </div>
                    </div>
                    <div class="flex lg:hidden">
                        <button aria-label="Afficher le menu" data-trigger-slid-chapter
                                class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 flex items-center justify-center rounded-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                 class="size-5"
                                 viewBox="0 0 256 256">
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
                        @foreach($masterClass->chapters as $chapter)
                            <button
                                wire:click.prevent="setActiveChapter({{ $chapter->id }})"
                                wire:key="{{ $chapter->id }}"
                                @class([
                                    'course-chater-item w-full text-left p-4 rounded-lg transition-all',
                                    'active' => $activeChapter?->id === $chapter->id,
                                    'disabled cursor-not-allowed' => !$chapter->isCompleted() && $loop->index > 0 && !$masterClass->chapters[$loop->index - 1]->isCompleted() && !$this->canAccessChapter($chapter),
                                    'hover:bg-gray-50' => $activeChapter?->id !== $chapter->id,
                                ])
                                @if(!$this->canAccessChapter($chapter))
                                    disabled
                                @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                    @class([
                                        'h-4 w-4',
                                        'text-primary' => $chapter->isCompleted(),
                                        'text-success' => $activeChapter?->id === $chapter->id,
                                        'text-gray-400' => !$chapter->isCompleted() && $activeChapter?->id !== $chapter->id
                                    ])>
                                    @if($chapter->isCompleted())
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
                                    'text-gray-600' => $activeChapter?->id !== $chapter->id
                                ])>
                                    {{ str($chapter->title)->ucfirst() }}
                                    @if(!$this->canAccessChapter($chapter))
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-gray-400"
                                             viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="h-16 flex items-center w-full p-4 border-t border-gray-200 bg-bg">
                    @if($masterClass->chapters->every(fn ($chapter) => $chapter->isCompleted()))
                        <button
                            class="w-full bg-primary-600 text-white btn btn-md rounded-lg flex items-center justify-center gap-2 hover:bg-primary-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-award h-5 w-5">
                                <circle cx="12" cy="8" r="6"></circle>
                                <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
                            </svg>
                            View Certificate
                        </button>
                    @endif
                </div>
            </div>
        </aside>
        <main class="min-h-screen bg-bg py-8 px-4 sm:px-10 lg:px-5 xl:px-8">
            <div class="max-w-4xl mx-auto">
                @if(!$activeChapter)
                    <h1 class="text-4xl font-bold text-fg-title mb-6">{{ $masterClass->title }}</h1>
                    <div class="markdow-content-block max-w-none mt-28 flex flex-col">
                        <h2>Introduction</h2>
                        <p>{!! $masterClass->presentation !!}</p>
                        <h2>About this Masterclass</h2>
                        <p>{!! $masterClass->description !!}</p>
                        <div class="alert-message">
                            ⚡️ Pro Tip: Start with the first chapter and progress sequentially through the course
                            for the best learning experience.
                        </div>
                    </div>
                @else
                    <h1 class="text-4xl font-bold text-fg-title mb-8">{{ $activeChapter->title }}</h1>

                    <h2 class="text-2xl font-bold text-fg-title">Presentation</h2>

                    <div @class([
                        'max-w-none markdow-content-block max-w-none mt-3 flex flex-col prose prose-invert',
                    ])>
                        {!! $activeChapter->content !!}
                    </div>


                    <h3 class="text-2xl font-bold text-fg-title mb-6">Dedscription</h3>

                    <div @class([
                        'max-w-none markdow-content-block max-w-none mt-3 flex flex-col prose prose-invert',
                        'blur-sm pointer-events-none' => !$this->canAccessChapter($activeChapter)
                    ])>
                        {!! $activeChapter->description !!}
                    </div>

                    <div @class([
                        'max-w-none markdow-content-block max-w-none mt-3 flex flex-col prose prose-invert',
                        'blur-sm pointer-events-none' => !$this->canAccessChapter($activeChapter)
                    ])>
                        <iframe
                            src="{{ asset('storage/' . $activeChapter->path)  }}"
                            type="application/pdf"
                            width="100%"
                            height="860px"
                            allow="publickey-credentials-get; publickey-credentials-create"
                            class="w-full border border-border rounded-lg"
                            loading="lazy"
                        ></iframe>
                    </div>

                    <div class="flex flex-col space-y-4 mt-4">
                        <h2 class="text-2xl font-semibold">Ressources</h2>
                        <div class="flex items-center flex-row gap-4">
                            @foreach($masterClass->resources as $resource)
                                <a href="{{ asset('storage/'. $resource->file_path) }}"
                                   wire:key="{{ $resource->id }}"
                                   download="{{ $resource->type === MasterClassResourceEnum::PDF }}"
                                   class="flex items-center w-max mt-5 bg-bg-lighter p-2 rounded-md pr-3 border border-border-lighter hover:border-border hover:bg-bg-high/70 ease-linear duration-300">
                                    @if($resource->type === MasterClassResourceEnum::VIDEO)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                             fill="currentColor"
                                             viewBox="0 0 256 256" class="size-6 mr-4 text-blue-600">
                                            <path
                                                d="M216,72V184a16,16,0,0,1-16,16H56a16,16,0,0,1-16-16V72A16,16,0,0,1,56,56H200A16,16,0,0,1,216,72Zm-16,0H56V184H200Zm-72,28a32,32,0,0,0-13.09,61.4,8,8,0,0,0,6.18-14.72A16,16,0,1,1,128,116a15.91,15.91,0,0,1,9.6,3.2L128,128a8,8,0,0,0,5.66,13.66A8.25,8.25,0,0,0,136,141.33l14.4-13.12A8,8,0,0,0,152,120,32,32,0,0,0,128,100Z"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                             fill="currentColor"
                                             viewBox="0 0 256 256" class="size-6 mr-4 text-red-600">
                                            <path
                                                d="M224,152a8,8,0,0,1-8,8H192v16h16a8,8,0,0,1,0,16H192v16a8,8,0,0,1-16,0V152a8,8,0,0,1,8-8h32A8,8,0,0,1,224,152ZM92,172a28,28,0,0,1-28,28H56v8a8,8,0,0,1-16,0V152a8,8,0,0,1,8-8H64A28,28,0,0,1,92,172Zm-16,0a12,12,0,0,0-12-12H56v24h8A12,12,0,0,0,76,172Zm88,8a36,36,0,0,1-36,36H112a8,8,0,0,1-8-8V152a8,8,0,0,1,8-8h16A36,36,0,0,1,164,180Zm-16,0a20,20,0,0,0-20-20h-8v40h8A20,20,0,0,0,148,180ZM40,112V40A16,16,0,0,1,56,24h96a8,8,0,0,1,5.66,2.34l56,56A8,8,0,0,1,216,88v24a8,8,0,0,1-16,0V96H152a8,8,0,0,1-8-8V40H56v72a8,8,0,0,1-16,0ZM160,80h28.69L160,51.31Z"/>
                                        </svg>
                                    @endif
                                    <div class="flex flex-col flex-1">
                                        <span
                                            class="text-sm font-semibold text-fg-title">{{ str($resource->title)->ucfirst() }}</span>
                                        <span class="text-sm text-gray-500">
                                            {{ $resource->type === MasterClassResourceEnum::VIDEO ? 'Cliquer pour regarder' : 'Cliquer pour télécharger' }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        @if(!$this->canSubmitExam())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700">
                                <p>Pour soumettre l'examen et accéder aux autres chapitres, vous devez avoir un code de
                                    référence.</p>
                                <a href="#"
                                   class="mt-2 inline-flex items-center text-yellow-800 hover:text-yellow-900">
                                    Obtenir un code de référence
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <div class="markdow-content-block max-w-none flex flex-col space-y-4">
                                @if($activeChapter->examination)
                                    <h2 class="text-2xl font-semibold">Passation d'evaluation</h2>
                                    <p>{!! $activeChapter->examination?->description !!}</p>

                                    @if ($activeChapter->examination?->deadline && now()->isAfter($activeChapter->examination?->deadline))
                                        <div class="alert-message">
                                            The submission deadline has passed.
                                        </div>
                                    @elseif (!$this->hasSubmittedExam())
                                        <a
                                            href="{{ asset('storage/' . $activeChapter->examination?->path) }}"
                                            download
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-bg-lighter text-fg hover:bg-bg-light rounded-lg transition-colors mt-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                            </svg>
                                            Telecharge l'examen
                                        </a>

                                        <div class="alert-message">
                                            Apres téléchargement veillez travailler sur et soumettre
                                            votre examen dans le formulaire sous-dessous
                                        </div>

                                        <div>
                                            <form wire:submit="submitExam" class="space-y-3">
                                                <x-filepond::upload
                                                    wire:model="file_path"
                                                />

                                                <button type="submit"
                                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg"
                                                        wire:loading.attr="disabled" wire:target="submitExam"
                                                    {{ $file_path ? '' : 'disabled' }}>
                                                    <span wire:loading.remove>Soumettre votre examen</span>
                                                    <span wire:loading>
                                                    <svg class="animate-spin mr-2 h-5 w-5 text-white inline-block"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                    </svg>
                                                    Chargement...
                                                </span>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="alert-message">
                                            Votre examen a été soumis avec succès.
                                        </div>

                                        @if(!$activeChapter->isCompleted())
                                            <button
                                                x-data
                                                wire:click.prevent="completeChapter({{ $activeChapter->id }})"
                                                x-on:click="
                                                    confetti({
                                                        particleCount: 1000,
                                                        spread: 70,
                                                        origin: { y: 0.6 }
                                                    });
                                                "
                                                class="w-full bg-primary-600 text-white btn btn-md rounded-lg flex items-center justify-center gap-2 hover:bg-primary-700 transition-colors mt-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                     viewBox="0 0 24 24"
                                                     fill="none" stroke="currentColor" stroke-width="2"
                                                     stroke-linecap="round" stroke-linejoin="round"
                                                     class="lucide lucide-award h-5 w-5">
                                                    <circle cx="12" cy="8" r="6"></circle>
                                                    <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
                                                </svg>
                                                Mark as Completed
                                            </button>
                                        @endif
                                    @endif
                                @else
                                    <div class="alert-message">
                                        Ce Chapitre n'a pas d'examen disponible.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="mt-12 grid grid-cols-2 gap-4">
                        <button
                            wire:click.prevent="setPreviousChapter"
                            @class([
                                'flex items-center gap-2 transition-colors',
                                'text-fg-subtext hover:text-fg-title cursor-pointer' => !($activeChapter && $activeChapter->id === $masterClass->chapters->first()->id) && $this->canAccessChapter($activeChapter),
                                'text-fg-subtext/50 cursor-not-allowed' => $activeChapter && $activeChapter->id === $masterClass->chapters->first()->id,
                                'opacity-50 cursor-not-allowed' => !$this->canAccessChapter($activeChapter)
                            ])
                            @if($activeChapter && $activeChapter->id === $masterClass->chapters->first()->id)
                                disabled
                            wire:loading.attr="disabled"
                            @endif

                            @if(!$this->canAccessChapter($activeChapter))
                                disabled
                            @endif
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round"
                                 class="lucide lucide-arrow-left h-5 w-5">
                                <path d="m12 19-7-7 7-7"></path>
                                <path d="M19 12H5"></path>
                            </svg>
                            Previous Chapter
                        </button>

                        <button
                            wire:click.prevent="setNextChapter"
                            @class([
                                'flex items-center justify-end gap-2 transition-colors',
                                'text-fg-subtext hover:text-fg-title cursor-pointer' => $activeChapter->isCompleted() && $activeChapter->id !== $masterClass->chapters->last()->id && $this->canAccessChapter($activeChapter),
                                'text-fg-subtext/50 cursor-not-allowed' => !$activeChapter->isCompleted() || $activeChapter->id === $masterClass->chapters->last()->id,
                                'opacity-50 cursor-not-allowed' => !$this->canAccessChapter($activeChapter)
                            ])
                            @if(!$activeChapter->isCompleted() || $activeChapter->id === $masterClass->chapters->last()->id)
                                disabled wire:loading.attr="disabled"
                            @endif
                            @if(!$this->canAccessChapter($activeChapter))
                                disabled
                            @endif
                        >
                            Next Chapter
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round"
                                 class="lucide lucide-arrow-right h-5 w-5">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
        </main>
    </div>
</div>
