@php use Illuminate\Support\Str; @endphp
<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Convertisseur PDF vers Markdown</h1>

        <div class="mb-8">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path
                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="mt-4">
                        <label for="file-upload" class="cursor-pointer">
                            <span class="mt-2 block text-sm font-medium text-gray-900">
                                Sélectionnez un fichier PDF ou glissez-le ici
                            </span>
                            <input wire:model="pdfFile" id="file-upload" name="file-upload" type="file" class="sr-only"
                                   accept=".pdf">
                        </label>
                        <p class="mt-1 text-xs text-gray-500">PDF jusqu'à 20MB</p>
                    </div>
                </div>
            </div>
            @error('pdfFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-8 border rounded-lg p-6 bg-gray-50">
            <h3 class="text-lg font-semibold mb-4">Options de traitement</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="flex items-center space-x-3">
                    <input wire:model="extractImages" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="text-sm text-gray-700">Extraire les images</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input wire:model="extractTables" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="text-sm text-gray-700">Détecter les tableaux</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input wire:model="extractCode" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="text-sm text-gray-700">Détecter le code</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input wire:model="createTableOfContents" type="checkbox"
                           class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="text-sm text-gray-700">Créer table des matières</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input wire:model="generateCoverImage" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="text-sm text-gray-700">Image de couverture</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input wire:model="ignorePageNumbers" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="text-sm text-gray-700">Ignorer numéros de page</span>
                </label>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pages à ignorer (séparées par des virgules, ex: 1,5,10-15)
                </label>
                <input wire:model="ignoredPages" type="text"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       placeholder="ex: 1,2,10-15">
            </div>
        </div>

        <div class="mb-8">
            <button wire:click="convertPdf"
                    wire:loading.attr="disabled"
                    @disabled(!$pdfFile || $isProcessing)
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                <span wire:loading.remove wire:target="convertPdf">Convertir en Markdown</span>
                <span wire:loading wire:target="convertPdf">Conversion en cours...</span>
            </button>
        </div>

        @if($isProcessing)
            <div class="mb-8 bg-white border rounded-lg p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progression</span>
                    <span class="text-sm text-gray-500">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                         style="width: {{ $progress }}%"></div>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    @switch($processingStep)
                        @case('generating_cover')
                            Génération de l'image de couverture...
                            @break
                        @case('parsing')
                            Analyse du document PDF...
                            @break
                        @case('extracting_images')
                            Extraction des images...
                            @break
                        @case('processing_pages')
                            Traitement des pages ({{ $currentPage }}/{{ $totalPages }})...
                            @break
                        @case('creating_toc')
                            Création de la table des matières...
                            @break
                        @case('finalizing')
                            Finalisation du document...
                            @break
                        @case('completed')
                            Conversion terminée !
                            @break
                        @default
                            Initialisation...
                    @endswitch
                </div>
            </div>
        @endif

        @if(!empty($markdownContent) && !$isProcessing)
            <div class="space-y-6">
                @if(!empty($metadata))
                    <div class="bg-gray-50 border rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Informations du document</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            @if($metadata['title'])
                                <div><strong>Titre:</strong> {{ $metadata['title'] }}</div>
                            @endif
                            @if($metadata['author'])
                                <div><strong>Auteur:</strong> {{ $metadata['author'] }}</div>
                            @endif
                            @if($metadata['page_count'])
                                <div><strong>Pages:</strong> {{ $metadata['page_count'] }}</div>
                            @endif
                            @if($metadata['creation_date'])
                                <div><strong>Créé le:</strong> {{ $metadata['creation_date'] }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Image de couverture --}}
                @if($coverImagePath)
                    <div class="bg-white border rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Image de couverture générée</h3>
                        <img src="{{ asset($coverImagePath) }}" alt="Couverture" class="max-w-xs rounded-lg shadow-md">
                    </div>
                @endif

                {{-- Statistiques d'extraction --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if(count($extractedImages) > 0)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="text-green-800 font-semibold">{{ count($extractedImages) }}</div>
                            <div class="text-green-600 text-sm">Images extraites</div>
                        </div>
                    @endif

                    @if(count($extractedTables) > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="text-blue-800 font-semibold">{{ count($extractedTables) }}</div>
                            <div class="text-blue-600 text-sm">Tableaux détectés</div>
                        </div>
                    @endif

                    @if(count($tableOfContents) > 0)
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="text-purple-800 font-semibold">{{ count($tableOfContents) }}</div>
                            <div class="text-purple-600 text-sm">Sections détectées</div>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex space-x-4">
                    <button wire:click="saveMarkdown"
                            class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                        Sauvegarder le fichier
                    </button>

                    <button onclick="copyToClipboard()"
                            class="bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                        Copier le contenu
                    </button>

                    <button onclick="downloadMarkdown()"
                            class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        Télécharger .md
                    </button>
                </div>

                {{-- Prévisualisation --}}
                <div class="bg-white border rounded-lg">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Prévisualisation Markdown</h3>
                            <div class="flex space-x-2">
                                <button @click="activeTab = 'raw'"
                                        :class="activeTab === 'raw' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                                        class="px-3 py-1 rounded text-sm">
                                    Code source
                                </button>
                                <button @click="activeTab = 'preview'"
                                        :class="activeTab === 'preview' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                                        class="px-3 py-1 rounded text-sm">
                                    Aperçu
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div x-data="{ activeTab: 'raw' }">
                            {{-- Code source --}}
                            <div x-show="activeTab === 'raw'">
                            <textarea id="markdown-content"
                                      class="w-full h-96 p-4 border border-gray-300 rounded font-mono text-sm"
                                      readonly>{{ $markdownContent }}</textarea>
                            </div>

                            {{-- Aperçu rendu --}}
                            <div x-show="activeTab === 'preview'" class="prose max-w-none">
                                {!! Str::markdown($markdownContent) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Messages d'erreur --}}
        @if($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-red-800 font-semibold mb-2">Erreurs détectées:</div>
                <ul class="text-red-600 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<script>
    function copyToClipboard() {
        const content = document.getElementById('markdown-content');
        content.select();
        document.execCommand('copy');

        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.textContent = 'Contenu copié dans le presse-papier !';
        document.body.appendChild(toast);

        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }

    function downloadMarkdown() {
        const content = document.getElementById('markdown-content').value;
        const blob = new Blob([content], {type: 'text/markdown'});
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = 'converted_document_' + Date.now() + '.md';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('contentUpdated', () => {
            console.log('Contenu mis à jour');
        });

        Livewire.on('fileSaved', (event) => {
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            toast.textContent = `Fichier sauvegardé: ${event.filename}`;
            document.body.appendChild(toast);

            setTimeout(() => {
                document.body.removeChild(toast);
            }, 4000);
        });
    });

    // Drag and drop functionality
    document.addEventListener('DOMContentLoaded', function () {
        const dropZone = document.querySelector('[for="file-upload"]').parentElement.parentElement;

        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                document.getElementById('file-upload').files = files;
                document.getElementById('file-upload').dispatchEvent(new Event('change'));
            }
        });
    });
</script>
