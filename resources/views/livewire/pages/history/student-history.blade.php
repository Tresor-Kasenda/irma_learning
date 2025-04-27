<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold mb-4">Historique des examens</h2>

                <div class="mb-6 w-1/3">
                    <label for="search" class="block text-sm font-medium text-gray-700"></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input
                            wire:model.live="search"
                            type="text"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-3 pr-12 sm:text-sm border-gray-300 rounded-md"
                            placeholder="Rechercher par titre, description...">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->submissions as $submission)
                    <div class="bg-white rounded-lg border overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-medium text-gray-900">{{ $submission->examination->title }}</h3>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $submission->created_at->format('d/m/Y') }}
                                    </span>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex">
                                    <span class="text-gray-500 w-20">Cours:</span>
                                    <span class="text-gray-800 font-medium text-sm">
                                        {{ $submission->chapter->cours->title }}
                                    </span>
                                </div>
                                <div class="flex">
                                    <span class="text-gray-500 w-20">Chapitre:</span>
                                    <span class="text-gray-800 text-sm">{{ $submission->chapter->title }}</span>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center justify-between">
                                    @if($submission->file_path)
                                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                                           class="text-indigo-600 hover:text-indigo-900 text-sm flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                            </svg>
                                            Voir le fichier d'evaluation
                                        </a>
                                        <a href="{{ route('student.history.update', $submission->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Modifier
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-sm italic">Aucun fichier</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($this->submissions->isEmpty())
                <div class="bg-white rounded-lg border p-6 text-center items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun historique d'examen</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Vous n'avez soumis aucun examen pour l'instant.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
