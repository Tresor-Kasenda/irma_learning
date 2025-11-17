<div class="min-h-screen bg-gray-50">
    {{-- En-tête avec barre de recherche --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <div class="flex items-center gap-8">
                    <h1 class="text-xl font-bold text-gray-900">Mon apprentissage</h1>
                </div>

                {{-- Barre de recherche --}}
                <div class="flex-1 max-w-2xl mx-8">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Rechercher des formations..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >
                    </div>
                </div>

                {{-- Stats rapides --}}
                <div class="hidden lg:flex items-center gap-6 text-sm">
                    <div class="text-center">
                        <div class="font-bold text-gray-900">{{ $stats['totalEnrollments'] }}</div>
                        <div class="text-gray-500">Formations</div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-900">{{ $stats['certificatesEarned'] }}</div>
                        <div class="text-gray-500">Certificats</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Message de bienvenue --}}
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Bonjour, {{ auth()->user()->name }} 👋
            </h2>
            <p class="text-gray-600">Prêt à poursuivre votre apprentissage aujourd'hui ?</p>
        </div>

        {{-- Continuer à apprendre (si disponible) --}}
        @if($continueWatching && $continueWatching->trackable)
            @php
                $chapter = $continueWatching->trackable;
                $formation = $chapter->section->module->formation ?? null;
            @endphp
            @if($formation)
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Continuer à apprendre</h3>
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl overflow-hidden shadow-lg">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-2/5 relative h-64 md:h-auto">
                            @if($formation->image)
                                <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-primary-800 flex items-center justify-center">
                                    <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="md:w-3/5 p-8 text-white flex flex-col justify-center">
                            <div class="mb-2">
                                <span class="text-primary-200 text-sm font-medium">Dernier visionnement</span>
                            </div>
                            <h4 class="text-2xl font-bold mb-2">{{ $formation->title }}</h4>
                            <p class="text-primary-100 mb-4">{{ $chapter->title }}</p>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span>{{ round($continueWatching->progress_percentage ?? 0) }}% terminé</span>
                                </div>
                                <div class="w-full bg-primary-800 rounded-full h-2">
                                    <div class="bg-white h-full rounded-full transition-all" style="width: {{ $continueWatching->progress_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                @if(Route::has('course.player'))
                                <a href="{{ route('course.player', ['formation' => $formation->id]) }}"
                                   class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold px-6 py-3 rounded-lg hover:bg-primary-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Continuer
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
        @endif

        {{-- Mes formations --}}
        @if($myEnrollments->count() > 0)
        <section class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Mes formations</h3>
                @if(Route::has('formations-lists'))
                <a href="{{ route('formations-lists') }}" class="text-primary-600 hover:text-primary-700 font-semibold text-sm">
                    Voir tout
                </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($myEnrollments->take(4) as $enrollment)
                    @php $formation = $enrollment->formation; @endphp
                    @if($formation)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden group cursor-pointer">
                        {{-- Image --}}
                        <div class="relative h-32 bg-gray-200">
                            @if($formation->image)
                                <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="flex items-center justify-center h-full bg-gradient-to-br from-primary-500 to-primary-600">
                                    <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Contenu --}}
                        <div class="p-4">
                            <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 text-sm group-hover:text-primary-600 transition-colors">
                                {{ $formation->title }}
                            </h4>
                            <div class="text-xs text-gray-500 mb-3">
                                Par {{ $formation->creator->name ?? 'IRMA Learning' }}
                            </div>

                            {{-- Barre de progression --}}
                            <div class="mb-3">
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-primary-600 h-full rounded-full" style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600 font-medium">
                                {{ round($enrollment->progress_percentage ?? 0) }}% terminé
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </section>
        @endif

        {{-- Statistiques d'apprentissage --}}
        <section class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Vos statistiques</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['activeEnrollments'] }}</div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">Formations en cours</div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['completedEnrollments'] }}</div>
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">Formations complétées</div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['certificatesEarned'] }}</div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">Certificats obtenus</div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $stats['averageProgress'] }}%</div>
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">Progression moyenne</div>
                </div>
            </div>
        </section>

        {{-- Formations recommandées --}}
        @if($recommendedFormations->count() > 0)
        <section class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Recommandé pour vous</h3>
                    <p class="text-gray-600 text-sm mt-1">Basé sur votre activité d'apprentissage</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recommendedFormations as $formation)
                <a href="{{ route('formation.show', $formation->slug) }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
                    {{-- Image --}}
                    <div class="relative h-32 bg-gray-200">
                        @if($formation->image)
                            <img src="{{ Storage::url($formation->image) }}" alt="{{ $formation->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="flex items-center justify-center h-full bg-gradient-to-br from-gray-400 to-gray-500">
                                <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif
                        @if($formation->is_featured)
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded">
                                Populaire
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Contenu --}}
                    <div class="p-4">
                        <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 text-sm group-hover:text-primary-600 transition-colors">
                            {{ $formation->title }}
                        </h4>
                        <div class="text-xs text-gray-500 mb-3">
                            Par {{ $formation->creator->name ?? 'IRMA Learning' }}
                        </div>

                        {{-- Infos --}}
                        <div class="flex items-center gap-3 text-xs text-gray-600 mb-3">
                            @if($formation->duration_hours)
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $formation->duration_hours }}h</span>
                            </div>
                            @endif
                            @if($formation->difficulty_level)
                            <div class="flex items-center">
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">
                                    {{ $formation->difficulty_level->value ?? 'Débutant' }}
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Prix --}}
                        <div class="flex items-center justify-between">
                            @if($formation->price > 0)
                                <span class="text-lg font-bold text-gray-900">{{ number_format($formation->price, 0, ',', ' ') }} FCFA</span>
                            @else
                                <span class="text-lg font-bold text-green-600">Gratuit</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Catégories populaires --}}
        <section class="mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Catégories populaires</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($popularCategories as $category)
                <a href="{{ route('home-page') }}" class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-all hover:scale-105 duration-300 text-center group">
                    <div class="text-lg font-bold text-gray-900 mb-1 group-hover:text-primary-600 transition-colors">
                        {{ $category['name'] }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $category['count'] }} formation{{ $category['count'] > 1 ? 's' : '' }}
                    </div>
                </a>
                @endforeach
            </div>
        </section>

        {{-- Call to action --}}
        <section class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-8 text-center text-white">
            <h3 class="text-2xl font-bold mb-3">Développez vos compétences dès aujourd'hui</h3>
            <p class="text-primary-100 mb-6 max-w-2xl mx-auto">
                Explorez notre catalogue de formations et trouvez celle qui correspond à vos objectifs professionnels.
            </p>
            <a href="{{ route('home-page') }}" class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold px-8 py-3 rounded-lg hover:bg-primary-50 transition-colors">
                Explorer les formations
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </section>
    </div>
</div>
