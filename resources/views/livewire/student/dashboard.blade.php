<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="text-2xl font-semibold text-gray-900">Tableau de bord</h1>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <!-- Stats Cards -->
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Formations en cours
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $this->stats['total_formations'] - $this->stats['completed_formations'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Certifications obtenues
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $this->stats['certificates_earned'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Score moyen
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $this->stats['average_score'] }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formations en cours -->
            <div class="mt-8">
                <h2 class="text-lg font-medium text-gray-900">Formations en cours</h2>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($this->activeFormations as $formation)
                        <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
                            @if($formation->cover_image)
                                <img src="{{ Storage::url($formation->cover_image) }}" 
                                     alt="{{ $formation->title }}" 
                                     class="w-full h-32 object-cover">
                            @endif
                            
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $formation->title }}
                                </h3>
                                
                                <div class="mt-4">
                                    <div class="relative pt-1">
                                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                                            <div style="width: {{ $formation->pivot->progress_percentage }}%"
                                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-semibold inline-block text-blue-600">
                                                {{ round($formation->pivot->progress_percentage) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <dl class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Modules complétés</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $formation->getCompletedModulesCount(auth()->user()) }}/{{ $formation->modules->count() }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Sections complétées</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $formation->getCompletedSectionsCount(auth()->user()) }}/{{ $formation->getTotalSectionsCount() }}
                                        </dd>
                                    </div>
                                </dl>

                                <div class="mt-4">
                                    <a href="{{ route('student.learning', $formation) }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                        Continuer
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Dernières activités -->
            <div x-data="{ activeTab: 'exams' }" class="mt-8">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeTab = 'exams'"
                                :class="{'border-blue-500 text-blue-600': activeTab === 'exams',
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'exams'}"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Derniers examens
                        </button>
                        <button @click="activeTab = 'certificates'"
                                :class="{'border-blue-500 text-blue-600': activeTab === 'certificates',
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'certificates'}"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Certifications
                        </button>
                    </nav>
                </div>

                <div class="mt-4">
                    <div x-show="activeTab === 'exams'">
                        <livewire:student.recent-exams lazy />
                    </div>
                    <div x-show="activeTab === 'certificates'">
                        <livewire:student.certificates lazy />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
