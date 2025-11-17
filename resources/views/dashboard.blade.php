<div class="min-h-screen bg-gray-50">
    {{-- Hero Section avec fond décoratif --}}
    <div class="relative bg-gradient-to-br from-primary-600 to-primary-700 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="absolute right-0 top-0 h-full w-auto" viewBox="0 0 400 400" fill="none">
                <circle cx="300" cy="100" r="200" fill="white"/>
            </svg>
        </div>

        <div class="relative px-4 sm:px-6 lg:px-8 xl:max-w-7xl mx-auto py-8 sm:py-12">
            <div class="text-white">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2">
                    Bonjour, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-primary-100 text-sm sm:text-base">
                    Continuez votre parcours d'apprentissage
                </p>
            </div>

            {{-- Statistiques --}}
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Formations --}}
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Total formations</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalEnrollments }}</p>
                        </div>
                        <div class="ml-4 p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Formations en cours --}}
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">En cours</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $activeEnrollments }}</p>
                        </div>
                        <div class="ml-4 p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Formations complétées --}}
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Complétées</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $completedEnrollments }}</p>
                        </div>
                        <div class="ml-4 p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Progression moyenne --}}
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Progression</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $averageProgress }}%</p>
                        </div>
                        <div class="ml-4 p-3 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenu principal --}}
    <div class="px-4 sm:px-6 lg:px-8 xl:max-w-7xl mx-auto py-8 space-y-8">

        {{-- Mes formations en cours --}}
        @if($myFormations->count() > 0)
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Mes formations en cours</h2>
                    <p class="text-gray-600 text-sm mt-1">Reprenez là où vous vous êtes arrêté</p>
                </div>
                @if(Route::has('formations.index'))
                <a href="{{ route('formations.index') }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center gap-1">
                    Voir tout
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($myFormations as $enrollment)
                    @if($enrollment->formation)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        {{-- Image de la formation --}}
                        <div class="relative h-48 bg-gradient-to-br from-primary-500 to-primary-600 overflow-hidden">
                            @if($enrollment->formation->image)
                                <img src="{{ Storage::url($enrollment->formation->image) }}" alt="{{ $enrollment->formation->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <svg class="w-20 h-20 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif

                            {{-- Badge niveau --}}
                            <div class="absolute top-3 left-3">
                                <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold rounded-full text-gray-700">
                                    {{ $enrollment->formation->difficulty_level?->value ?? 'Débutant' }}
                                </span>
                            </div>
                        </div>

                        {{-- Contenu --}}
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                {{ $enrollment->formation->title }}
                            </h3>

                            <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                                {{ $enrollment->formation->short_description ?? 'Formation de qualité professionnelle' }}
                            </p>

                            {{-- Progression --}}
                            <div class="mb-4">
                                <div class="flex justify-between items-center text-sm mb-2">
                                    <span class="text-gray-600 font-medium">Progression</span>
                                    <span class="text-primary-600 font-bold">{{ round($enrollment->progress_percentage ?? 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-full rounded-full transition-all duration-500"
                                         style="width: {{ $enrollment->progress_percentage ?? 0 }}%">
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            @if(Route::has('formations.show'))
                            <a href="{{ route('formations.show', $enrollment->formation->slug) }}"
                               class="block w-full text-center bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 rounded-lg transition-colors duration-300">
                                Continuer
                            </a>
                            @else
                            <button disabled
                               class="block w-full text-center bg-gray-400 text-white font-semibold py-3 rounded-lg cursor-not-allowed">
                                Continuer
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </section>
        @endif

        {{-- Formations recommandées --}}
        <section>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Découvrir de nouvelles formations</h2>
                    <p class="text-gray-600 text-sm mt-1">Développez vos compétences avec nos meilleures formations</p>
                </div>

                {{-- Barre de recherche --}}
                <div class="relative w-full sm:w-80">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher une formation..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    >
                </div>
            </div>

            @if($availableFormations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($availableFormations as $formation)
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    {{-- Image --}}
                    <div class="relative h-48 bg-gradient-to-br from-gray-400 to-gray-500 overflow-hidden">
                        @if($formation->image)
                            <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="flex items-center justify-center h-full">
                                <svg class="w-20 h-20 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif

                        {{-- Badge niveau --}}
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold rounded-full text-gray-700">
                                {{ $formation->difficulty_level?->value ?? 'Débutant' }}
                            </span>
                        </div>

                        {{-- Badge nouveau --}}
                        <div class="absolute top-3 right-3">
                            <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                Nouveau
                            </span>
                        </div>
                    </div>

                    {{-- Contenu --}}
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                            {{ $formation->title }}
                        </h3>

                        <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                            {{ $formation->short_description ?? 'Formation de qualité professionnelle' }}
                        </p>

                        {{-- Infos --}}
                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                            @if($formation->duration_hours)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $formation->duration_hours }}h</span>
                            </div>
                            @endif
                        </div>

                        {{-- Prix et action --}}
                        <div class="flex items-center justify-between">
                            <div>
                                @if($formation->price > 0)
                                    <span class="text-2xl font-bold text-gray-900">{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                                @else
                                    <span class="text-2xl font-bold text-green-600">Gratuit</span>
                                @endif
                            </div>
                            @if(Route::has('formations.show'))
                            <a href="{{ route('formations.show', $formation->slug) }}"
                               class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-300 flex items-center gap-2">
                                S'inscrire
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @else
                            <button disabled
                               class="px-6 py-2.5 bg-gray-400 text-white font-semibold rounded-lg cursor-not-allowed flex items-center gap-2">
                                S'inscrire
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-xl p-12 text-center">
                <svg class="mx-auto w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune formation trouvée</h3>
                <p class="text-gray-600 mb-6">
                    @if($search)
                        Aucune formation ne correspond à votre recherche "{{ $search }}"
                    @else
                        Toutes les formations disponibles sont déjà dans votre bibliothèque !
                    @endif
                </p>
                @if($search)
                <button wire:click="$set('search', '')" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors">
                    Réinitialiser la recherche
                </button>
                @endif
            </div>
            @endif
        </section>
    </div>
</div>
