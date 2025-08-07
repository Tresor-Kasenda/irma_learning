<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes Formations</h1>
        <p class="text-gray-600">Gérez vos inscriptions et découvrez de nouvelles formations</p>
    </div>

    <!-- Search and Filter -->
    <div class="mb-8 bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher une formation..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <select wire:model.live="filter" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="all">Toutes les inscriptions</option>
                    <option value="active">Actives</option>
                    <option value="completed">Complétées</option>
                    <option value="expired">Expirées</option>
                </select>
            </div>
        </div>
    </div>

    <!-- My Subscriptions -->
    @if($subscriptions->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Mes Formations Inscrites</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($subscriptions as $subscription)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                    {{ $subscription->masterClass->title }}
                                </h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($subscription->status->value === 'active') bg-green-100 text-green-800
                                    @elseif($subscription->status->value === 'completed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($subscription->status->value) }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $subscription->masterClass->description }}
                            </p>
                            
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Progression</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ number_format($subscription->getProgressPercentage(), 1) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $subscription->getProgressPercentage() }}%"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>Inscrit le {{ $subscription->started_at->format('d/m/Y') }}</span>
                                @if($subscription->completed_at)
                                    <span>Terminé le {{ $subscription->completed_at->format('d/m/Y') }}</span>
                                @endif
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="{{ route('student.course.learning', $subscription->masterClass) }}" 
                                   class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                    Continuer
                                </a>
                                <button class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $subscriptions->links() }}
            </div>
        </div>
    @endif

    <!-- Available Master Classes -->
    @if($availableMasterClasses->count() > 0)
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Formations Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($availableMasterClasses as $masterClass)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                {{ $masterClass->title }}
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $masterClass->description }}
                            </p>
                            
                            <div class="flex items-center justify-between mb-4">
                                @if($masterClass->isFree())
                                    <span class="text-lg font-bold text-green-600">Gratuit</span>
                                @else
                                    <span class="text-lg font-bold text-gray-900">{{ number_format($masterClass->price, 2) }} €</span>
                                @endif
                                
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $masterClass->chapters->count() }} chapitres
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                @if($masterClass->isFree())
                                    <button wire:click="subscribe({{ $masterClass->id }})" 
                                            class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                                        S'inscrire gratuitement
                                    </button>
                                @else
                                    <button class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                        Acheter
                                    </button>
                                @endif
                                
                                <a href="{{ route('master-class', $masterClass) }}" 
                                   class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                    Voir
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($subscriptions->count() === 0 && $availableMasterClasses->count() === 0)
        <div class="text-center py-12">
            <div class="max-w-md mx-auto">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune formation trouvée</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par explorer nos formations disponibles.</p>
                <div class="mt-6">
                    <a href="{{ route('formations-lists') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Explorer les formations
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('notify', (data) => {
        const message = data[0]?.message || data.message;
        const type = data[0]?.type || data.type;
        
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 5000);
    });
</script>
@endscript
