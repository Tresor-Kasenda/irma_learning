<div>
    <div aria-hidden="true" data-overlay-slid-chapter
         class="fixed inset-0 bg-fg-title/50 z-[70] backdrop-blur-sm lg:hidden lg:invisible invisible opacity-0 fx-open:opacity-100 fx-open:visible">
    </div>
    <div class="max-w-[95rem] grid lg:grid-cols-[350px_minmax(0,1fr)] w-full mx-auto">
        <aside class="side-nav-course lg:border-r border-border">
            <div
                class="h-16 border-b border-border px-4 flex items-center justify-between sticky lg:static top-0 bg-bg">
                <div class="flex items-center gap-2">
                    <a href="./" aria-label="Retour dashboard"
                       class="text-fg hover:bg-bg-light ease-linear duration-100 size-10 flex items-center justify-center rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                             class="size-5"
                             viewBox="0 0 256 256">
                            <path
                                d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z">
                            </path>
                        </svg>
                    </a>
                    <a href="./profile.html" aria-label="Retour dashboard"
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
                        <div class="text-sm text-gray-600">Progress: 40%</div>
                        <div class="w-20 h-2 bg-bg-high rounded-full">
                            <div class="h-full bg-primary rounded-full" style="width: 40%;"></div>
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
                            <a href="#"
                               wire:click.prevent="setActiveChapter({{ $chapter->id }})"
                               data-state="{{ $activeChapter?->id === $chapter->id ? 'active' : 'inactive' }}"
                               class="course-chater-item {{ $activeChapter?->id === $chapter->id ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round"
                                     class="h-4 w-4 {{ $activeChapter?->id === $chapter->id ? 'text-primary' : 'text-gray-400' }}">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                </svg>
                                <span
                                    class="text-sm {{ $activeChapter?->id === $chapter->id ? 'text-fg-title font-medium' : 'text-gray-600' }}">
                                    {{ $chapter->title }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="h-16 flex items-center w-full p-4 border-t border-gray-200 bg-bg">
                    <button
                        class="w-full bg-primary-600 text-white btn btn-md rounded-lg flex items-center justify-center gap-2 hover:bg-primary-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="lucide lucide-award h-5 w-5">
                            <circle cx="12" cy="8" r="6"></circle>
                            <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
                        </svg>
                        View Certificate
                    </button>
                </div>
            </div>
        </aside>
        <main class="min-h-screen bg-bg py-8 px-4 sm:px-10 lg:px-5 xl:px-8" wire:poll.keep-alive>
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
                    <h1 class="text-4xl font-bold text-fg-title mb-6">{{ $activeChapter->title }}</h1>

                    <h2 class="text-2xl font-bold text-fg-title mb-6">Presentation</h2>

                    <p class="markdow-content-block max-w-none mt-8 flex flex-col prose prose-invert">
                        {{ $activeChapter->content }}
                    </p>

                    <h3 class="text-2xl font-bold text-fg-title mb-6">Dedscription</h3>

                    <div class="markdow-content-block max-w-none mt-8 flex flex-col prose prose-invert space-y-2">
                        {!! $activeChapter->description !!}
                    </div>

                    <div>
                        <embed
                            src="{{ asset('storage/'. $activeChapter->path)  }}"
                            type="application/pdf"
                            width="100%"
                            height="800px"
                            class="w-full border border-border rounded-lg"
                            pluginspage="http://www.adobe.com/products/acrobat/readstep2.html"
                        />
                    </div>
                    <div class="mt-12 grid grid-cols-2 gap-4">

                        <button
                            class="flex items-center gap-2 text-fg-subtext hover:text-fg-title transition-colors">
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
                            class="flex items-center justify-end gap-2 text-fg-subtext hover:text-fg-title transition-colors">
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
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
