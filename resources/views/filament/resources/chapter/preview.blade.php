<div class="space-y-6">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $chapter->title }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Position {{ $chapter->order_position }} •
                    @if($chapter->duration_minutes)
                        {{ $chapter->duration_minutes }} minutes
                    @else
                        Durée non définie
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ match($chapter->content_type->value) {
                        'video' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'text' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'pdf' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'audio' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                        'interactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                    } }}"
                >
                    {{ $chapter->content_type->getLabel() }}
                </span>

                @if($chapter->is_free)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                        Gratuit
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                        Payant
                    </span>
                @endif

                @if($chapter->is_active)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Actif
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Inactif
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($chapter->description)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Description</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                {{ $chapter->description }}
            </p>
        </div>
    @endif

    @if($chapter->media_url && in_array($chapter->content_type->value, ['pdf', 'video', 'audio']))
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Fichier de contenu</h3>

            @if($chapter->content_type->value === 'pdf')
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 18h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ basename($chapter->media_url) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Type: PDF
                            </p>
                        </div>
                        <a href="{{ Storage::url($chapter->media_url) }}"
                           target="_blank"
                           class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                            Ouvrir PDF
                        </a>
                    </div>
                    <div class="mt-3">
                        <iframe src="{{ Storage::url($chapter->media_url) }}"
                                class="w-full h-96 border border-gray-300 rounded-lg"
                                title="Aperçu PDF">
                        </iframe>
                    </div>
                </div>
            @elseif($chapter->content_type->value === 'video')
                <div class="space-y-3">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                <path d="M8 9l3 2-3 2V9z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ basename($chapter->media_url) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Type: Vidéo
                            </p>
                        </div>
                    </div>
                    <video controls class="w-full max-h-96 bg-black rounded-lg">
                        <source src="{{ Storage::url($chapter->media_url) }}" type="video/mp4">
                        <source src="{{ Storage::url($chapter->media_url) }}" type="video/webm">
                        <source src="{{ Storage::url($chapter->media_url) }}" type="video/ogg">
                        Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                </div>
            @elseif($chapter->content_type->value === 'audio')
                <div class="space-y-3">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ basename($chapter->media_url) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Type: Audio
                            </p>
                        </div>
                    </div>
                    <audio controls class="w-full">
                        <source src="{{ Storage::url($chapter->media_url) }}" type="audio/mpeg">
                        <source src="{{ Storage::url($chapter->media_url) }}" type="audio/ogg">
                        <source src="{{ Storage::url($chapter->media_url) }}" type="audio/wav">
                        Votre navigateur ne supporte pas la lecture audio.
                    </audio>
                </div>
            @endif
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contenu du chapitre</h3>
        <div class="prose prose-sm max-w-none dark:prose-invert">
            {!! $chapter->content !!}
        </div>
    </div>

    @if($chapter->metadata && count($chapter->metadata) > 0)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Métadonnées</h3>
            <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                @foreach($chapter->metadata as $key => $value)
                    <div class="flex justify-between text-sm">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">{{ $key }}:</dt>
                        <dd class="text-gray-900 dark:text-white ml-2 break-all">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Informations techniques</h3>
        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <div class="flex justify-between text-sm">
                <dt class="font-medium text-gray-500 dark:text-gray-400">ID:</dt>
                <dd class="text-gray-900 dark:text-white">{{ $chapter->id }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Section:</dt>
                <dd class="text-gray-900 dark:text-white">{{ $chapter->section->title }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Créé le:</dt>
                <dd class="text-gray-900 dark:text-white">{{ $chapter->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Modifié le:</dt>
                <dd class="text-gray-900 dark:text-white">{{ $chapter->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>
