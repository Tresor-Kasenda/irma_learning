<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Bienvenue, {{ auth()->user()->name }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span
                            class="text-sm text-gray-500">Dernière connexion : {{ auth()->user()->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            wire:click="setActiveTab('overview')"
                            class="py-2 px-1 border-b-2 font-medium text-base {{ $activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        >
                            Vue d'ensemble
                        </button>
                        <button
                            wire:click="setActiveTab('formations')"
                            class="py-2 px-1 border-b-2 font-medium text-base {{ $activeTab === 'formations' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        >
                            Mes formations
                        </button>
                        <button
                            wire:click="setActiveTab('progress')"
                            class="py-2 px-1 border-b-2 font-medium text-base {{ $activeTab === 'progress' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        >
                            Progression
                        </button>
                        <button
                            wire:click="setActiveTab('certificates')"
                            class="py-2 px-1 border-b-2 font-medium text-base {{ $activeTab === 'certificates' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        >
                            Certifications
                        </button>
                    </nav>
                    <div class="flex items-center gap-4">
                        <div class="relative w-[230px] sm:w-80">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                 fill="currentColor"
                                 class="size-4 text-fg-subtext absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                 viewBox="0 0 256 256">
                                <path
                                    d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z">
                                </path>
                            </svg>
                            <input
                                type="text"
                                placeholder="Rechercher"
                                wire:model="search"
                                class="ui-form-input px-4 py-5 h-9 rounded-md peer w-full ps-9"
                            />
                        </div>
                        <div class="flex gap-3 items-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($activeTab === 'overview')
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-4">
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Formations inscrites</h3>
                        <div class="p-2 bg-primary-100 rounded-lg">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">{{ $this->stats['total_enrollments'] }}</p>
                        <p class="text-fg-subtext">Formations inscrites</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Formations terminées</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">
                            {{ $this->stats['completed_formations'] }}
                        </p>
                        <p class="text-fg-subtext">Formations terminées</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">En cours</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">{{ $this->stats['in_progress'] }}</p>
                        <p class="text-fg-subtext">En cours</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Certifications</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">{{ $this->stats['certificates_earned'] }}</p>
                        <p class="text-fg-subtext">Certifications</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6 min-h-[260px] flex flex-col">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Formations récentes</h3>
                        <div class="space-y-4 flex-grow">
                            @forelse($this->enrollments->take(5) as $enrollment)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $enrollment->formation->title }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Inscrit le {{ $enrollment->enrollment_date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                 style="width: {{ $enrollment->progress_percentage }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ round($enrollment->progress_percentage) }}%</span>
                                    </div>
                                </div>
                            @empty
                                <div class="h-full flex items-center justify-center">
                                    <p class="text-gray-500 text-sm">Aucune formation pour le moment.</p>
                                </div>
                            @endforelse
                        </div>
                        @if($this->enrollments->count() > 5)
                            <div class="mt-4">
                                <button wire:click="setActiveTab('formations')"
                                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Voir toutes les formations →
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Activité récente</h3>
                        <div class="space-y-4">
                            @forelse($this->recentActivity as $activity)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                                    <div class="ml-3">
                                        <p class="text-sm text-gray-900">
                                            @if($activity->status === 'completed')
                                                <span
                                                    class="font-medium">Terminé :</span> {{ $activity->trackable->title ?? 'Contenu' }}
                                            @else
                                                <span
                                                    class="font-medium">En cours :</span> {{ $activity->trackable->title ?? 'Contenu' }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $activity->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">Aucune activité récente.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Onglet Formations -->
        @if($activeTab === 'formations')
            <div class="space-y-6">
                @forelse($this->enrollments as $enrollment)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ $enrollment->formation->title }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $enrollment->formation->modules->count() }} modules •
                                        Niveau {{ $enrollment->formation->difficulty_level->label() }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <div
                                            class="text-sm text-gray-900 font-medium">{{ round($enrollment->progress_percentage) }}
                                            % terminé
                                        </div>
                                        <div class="w-32 bg-gray-200 rounded-full h-2 mt-1">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                 style="width: {{ $enrollment->progress_percentage }}%"></div>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('formation.show', $enrollment->formation->slug) }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200"
                                    >
                                        Continuer
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4">
                            <livewire:formation-progress :enrollment="$enrollment" :key="'progress-'.$enrollment->id"/>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.712-3.714M14 40v-4a9.971 9.971 0 01.712-3.714M28 16a4 4 0 11-8 0 4 4 0 018 0zM24 20a6 6 0 100 12 6 6 0 000-12zM40 16a4 4 0 11-8 0 4 4 0 018 0zM8 16a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune formation</h3>
                        <p class="mt-1 text-sm text-gray-500">Vous n'êtes inscrit à aucune formation pour le moment.</p>
                        <div class="mt-6">
                            <a href="{{ route('formations-lists') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Parcourir les formations
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        <!-- Onglet Progression -->
        @if($activeTab === 'progress')
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Détails de progression</h3>
                <p class="text-gray-500">Contenu en cours de développement...</p>
            </div>
        @endif

        <!-- Onglet Certifications -->
        @if($activeTab === 'certificates')
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Mes certifications</h3>
                <p class="text-gray-500">Contenu en cours de développement...</p>
            </div>
        @endif
    </div>
</div>
