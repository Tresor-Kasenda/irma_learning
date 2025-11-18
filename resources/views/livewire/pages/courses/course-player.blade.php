<div class="min-h-screen bg-gray-900 flex flex-col">
    {{-- En-tête du cours --}}
    <header class="bg-gray-950 border-b border-gray-800 sticky top-0 z-50">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                {{-- Gauche: Logo et titre --}}
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <a href="{{ route('dashboard') }}" class="text-white hover:text-primary-400 transition-colors flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-white font-semibold truncate text-sm md:text-base">
                            {{ $formation->title }}
                        </h1>
                        <p class="text-gray-400 text-xs hidden md:block">
                            Chapitre {{ $currentChapterIndex + 1 }} sur {{ count($allChapters) }}
                        </p>
                    </div>
                </div>

                {{-- Droite: Barre de progression --}}
                <div class="flex items-center gap-4 flex-shrink-0">
                    <div class="hidden md:flex items-center gap-3">
                        <span class="text-gray-400 text-sm">
                            {{ round(($enrollment->progress_percentage ?? 0), 0) }}% complété
                        </span>
                        <div class="w-32 bg-gray-800 rounded-full h-2">
                            <div class="bg-primary-600 h-full rounded-full transition-all duration-500"
                                 style="width: {{ $enrollment->progress_percentage ?? 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Contenu principal --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Zone de contenu du chapitre --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-5xl mx-auto p-6 md:p-8">
                {{-- Titre du chapitre --}}
                <div class="mb-6">
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                        {{ $currentChapter->title }}
                    </h2>
                    @if($currentChapter->description)
                    <p class="text-gray-400">
                        {{ $currentChapter->description }}
                    </p>
                    @endif
                </div>

                {{-- Contenu du chapitre --}}
                @if($currentChapter->content_type === 'video' && $currentChapter->media_url)
                    {{-- Lecteur vidéo --}}
                    <div class="mb-8 rounded-lg overflow-hidden bg-black aspect-video">
                        <video
                            controls
                            class="w-full h-full"
                            poster="{{ $currentChapter->thumbnail ?? '' }}"
                            controlsList="nodownload"
                        >
                            <source src="{{ Storage::url($currentChapter->media_url) }}" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    </div>
                @elseif($currentChapter->content_type === 'text' || $currentChapter->content)
                    {{-- Contenu texte converti en HTML --}}
                    <div class="mb-8">
                        <div class="bg-gray-800 rounded-lg p-6">
                            {!! $htmlContent !!}
                        </div>
                    </div>
                @endif

                {{-- Documents/Ressources additionnelles --}}
                @if($currentChapter->media_url && $currentChapter->content_type === 'document')
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-white mb-4">Ressources</h3>
                    <div class="bg-gray-800 rounded-lg p-4">
                        <a href="{{ Storage::url($currentChapter->media_url) }}"
                           target="_blank"
                           class="flex items-center gap-3 text-primary-400 hover:text-primary-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Télécharger le document</span>
                        </a>
                    </div>
                </div>
                @endif

                {{-- Boutons de navigation --}}
                <div class="flex items-center justify-between gap-4 pt-6 border-t border-gray-800">
                    <button
                        wire:click="previousChapter"
                        @if($currentChapterIndex === 0) disabled @endif
                        class="flex items-center gap-2 px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Précédent
                    </button>

                    <button
                        wire:click="markChapterAsCompleted"
                        class="flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Marquer comme terminé
                    </button>

                    <button
                        wire:click="nextChapter"
                        @if($currentChapterIndex >= count($allChapters) - 1) disabled @endif
                        class="flex items-center gap-2 px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Suivant
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Sidebar du curriculum --}}
        <aside class="w-80 lg:w-96 bg-gray-950 border-l border-gray-800 overflow-y-auto hidden md:block">
            <div class="p-4 border-b border-gray-800 sticky top-0 bg-gray-950 z-10">
                <h3 class="text-white font-semibold text-lg">Contenu du cours</h3>
                <p class="text-gray-400 text-sm mt-1">
                    {{ count($completedChapters) }} / {{ count($allChapters) }} chapitres complétés
                </p>
            </div>

            <div class="p-4">
                @foreach($formation->modules as $module)
                <div class="mb-4">
                    <h4 class="text-white font-semibold mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        {{ $module->title }}
                    </h4>

                    @foreach($module->sections as $section)
                    <div class="ml-4 mb-3">
                        <h5 class="text-gray-300 text-sm font-medium mb-2">{{ $section->title }}</h5>

                        <div class="space-y-1">
                            @foreach($section->chapters as $chapter)
                            <button
                                wire:click="selectChapter({{ $chapter->id }})"
                                class="w-full text-left px-3 py-2 rounded-lg transition-colors flex items-center gap-3 group
                                    {{ $currentChapter?->id === $chapter->id ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                {{-- Icône de statut --}}
                                @if(in_array($chapter->id, $completedChapters))
                                    <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($currentChapter?->id === $chapter->id)
                                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 flex-shrink-0 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate">{{ $chapter->title }}</p>
                                    @if($chapter->duration_minutes)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $chapter->duration_minutes }} min</p>
                                    @endif
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </aside>
    </div>

    {{-- Bouton mobile pour afficher le curriculum --}}
    <div class="md:hidden fixed bottom-4 right-4 z-50">
        <button
            x-data
            @click="$dispatch('open-curriculum-modal')"
            class="bg-primary-600 text-white p-4 rounded-full shadow-lg hover:bg-primary-700 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>
</div>
