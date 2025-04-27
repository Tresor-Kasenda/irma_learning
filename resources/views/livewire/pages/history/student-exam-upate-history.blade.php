<div class="min-h-screen py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6 border">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Modifier le fichier d'examen</h2>
            </div>

            <div class="mb-6 space-y-2 border-b pb-6">
                <div class="flex">
                    <span class="text-gray-500 w-32">Examen:</span>
                    <span class="text-gray-900 font-medium">{{ $submission->examination->title }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-500 w-32">Cours:</span>
                    <span class="text-gray-900">{{ $submission->chapter->cours->title }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-500 w-32">Chapitre:</span>
                    <span class="text-gray-900">{{ $submission->chapter->title }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-500 w-32">Soumis le:</span>
                    <span class="text-gray-900">{{ $submission->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            @if($submission->file_path)
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Fichier actuel:</h3>
                    <div class="flex items-center bg-gray-50 p-3 rounded-lg border">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 mr-2" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span
                            class="flex-1 truncate text-sm text-gray-600">{{ basename($submission->file_path) }}</span>
                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium ml-2">
                            Voir
                        </a>
                    </div>
                </div>
            @endif
            <div class="text-sm text-gray-500 mt-3 mb-3">
                <span class="font-medium">Note:</span> Seuls les fichiers PDF sont acceptés (max 10MB)
            </div>
            <form wire:submit.prevent="update" class="space-y-6">
                {{ $this->form }}

                <div class="flex items-center justify-end pt-4 border-t">
                    <button type="submit"
                            class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
