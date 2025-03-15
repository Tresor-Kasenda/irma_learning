<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-fg-title mb-6">Examen Final</h1>

    <div class="bg-white rounded-lg shadow p-6">
        @if($this->hasSubmittedFinalExam())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Vous avez déjà soumis votre examen final. Une seule soumission est autorisée.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <div class="prose max-w-none mb-6">
            <h2 class="text-xl font-semibold mb-4">{{ $masterClass->finalExam->title }}</h2>
            <div class="text-gray-600">
                {!! $masterClass->finalExam->description !!}
            </div>
        </div>

        <div class="space-y-6">
            @if(!$this->hasSubmittedFinalExam())
                <div>
                    <h3 class="font-medium mb-2">1. Télécharger l'examen</h3>
                    <a href="{{ asset('storage/' . $masterClass->finalExam->path) }}"
                       download
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                        Télécharger l'examen final
                    </a>
                </div>

                <div>
                    <h3 class="font-medium mb-2">2. Fiche de soumission</h3>
                    @foreach($masterClass->finalExam->files as $file)
                        <a href="{{ asset('storage/' . $file) }}"
                           download
                           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Fiche de soumission
                        </a>
                    @endforeach
                </div>

                <div>
                    <h3 class="font-medium mb-2">2. Soumettre votre réponse</h3>
                    <form wire:submit="submitFinalExam" class="space-y-4">
                        {{ $this->form }}

                        <button type="submit"
                                class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>Soumettre l'examen final</span>
                            <span wire:loading>
                            <svg class="animate-spin h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Chargement...
                        </span>
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Vous avez déjà soumis votre examen final. Une seule soumission est autorisée.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
