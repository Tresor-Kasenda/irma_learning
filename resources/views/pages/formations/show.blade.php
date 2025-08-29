<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- En-tête de la formation --}}
                <div class="relative h-64">
                    @if($formation->cover_image)
                        <img src="{{ Storage::url($formation->cover_image) }}" 
                             alt="{{ $formation->title }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-200"></div>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-6">
                        <h1 class="text-3xl font-bold text-white">{{ $formation->title }}</h1>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Informations de la formation --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <div class="prose max-w-none">
                                <h2 class="text-2xl font-semibold mb-4">Description</h2>
                                {!! $formation->description !!}
                            </div>

                            {{-- Aperçu des modules --}}
                            <div class="mt-8">
                                <h2 class="text-2xl font-semibold mb-4">Contenu de la formation</h2>
                                <div class="space-y-4">
                                    @foreach($formation->modules as $module)
                                        <div class="border rounded-lg p-4">
                                            <h3 class="text-lg font-medium">{{ $module->title }}</h3>
                                            <p class="text-gray-600 mt-2">{{ $module->description }}</p>
                                            <div class="mt-2 text-sm text-gray-500">
                                                {{ $module->estimated_duration }} minutes • 
                                                {{ $module->sections_count }} sections
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Carte d'inscription --}}
                        <div class="md:col-span-1">
                            <div class="border rounded-lg p-6 sticky top-6">
                                <div class="text-center">
                                    <div class="text-3xl font-bold">{{ $formation->price }} €</div>
                                    <div class="text-gray-600 mt-2">
                                        {{ $formation->getTotalChaptersCount() }} chapitres •
                                        {{ $formation->getEstimatedDuration() }} minutes
                                    </div>
                                </div>

                                <div class="mt-6">
                                    @auth
                                        @if(auth()->user()->isEnrolledIn($formation))
                                            <a href="{{ route('student.learning', $formation) }}"
                                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                                Continuer l'apprentissage
                                            </a>
                                        @else
                                            <livewire:formations.enroll-button :formation="$formation" />
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}"
                                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            Se connecter pour s'inscrire
                                        </a>
                                    @endauth
                                </div>

                                {{-- Statistiques --}}
                                <div class="mt-6 space-y-4 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Étudiants inscrits</span>
                                        <span class="font-medium">{{ $formation->getEnrollmentCount() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Certifications délivrées</span>
                                        <span class="font-medium">{{ $formation->getCertifiedStudentsCount() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
