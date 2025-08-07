<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes Formations</h1>
        <p class="text-gray-600">Gérez vos inscriptions et découvrez de nouvelles formations</p>
    </div>

    <!-- Search Bar -->
    <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            placeholder="Rechercher une formation..."
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-8">
        <nav class="flex space-x-8" aria-label="Tabs">
            <button 
                wire:click="setActiveTab('subscribed')"
                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'subscribed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Mes Inscriptions
                @if($subscribedMasterClasses->count() > 0)
                    <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs">
                        {{ $subscribedMasterClasses->total() }}
                    </span>
                @endif
            </button>
            
            <button 
                wire:click="setActiveTab('available')"
                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'available' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Formations Disponibles
                @if($availableMasterClasses->count() > 0)
                    <span class="ml-2 bg-green-100 text-green-600 py-0.5 px-2 rounded-full text-xs">
                        {{ $availableMasterClasses->total() }}
                    </span>
                @endif
            </button>
            
            <button 
                wire:click="setActiveTab('paid')"
                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'paid' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Formations Payées
                @if($paidMasterClasses->count() > 0)
                    <span class="ml-2 bg-purple-100 text-purple-600 py-0.5 px-2 rounded-full text-xs">
                        {{ $paidMasterClasses->total() }}
                    </span>
                @endif
            </button>
        </nav>
    </div>

    <!-- Subscribed Master Classes -->
    @if($activeTab === 'subscribed' && $subscribedMasterClasses->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Mes Formations Inscrites</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($subscribedMasterClasses as $masterClass)
                    @php
                        $subscription = $masterClass->subscriptions->first();
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                    {{ $masterClass->title }}
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
                                {{ $masterClass->description }}
                            </p>
                            
                            <!-- Progress Bar -->
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
                            
                            <!-- Course Info -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>{{ $masterClass->chapters->count() }} chapitres</span>
                                <span>Inscrit le {{ $subscription->started_at->format('d/m/Y') }}</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button wire:click="startLearning({{ $masterClass->id }})" 
                                        class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                    @if($subscription->progress > 0)
                                        Continuer
                                    @else
                                        Commencer
                                    @endif
                                </button>
                                <button wire:click="viewMasterClass({{ $masterClass->id }})" 
                                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $subscribedMasterClasses->links() }}
            </div>
        </div>
    @endif

    <!-- Available Master Classes -->
    @if($activeTab === 'available' && $availableMasterClasses->count() > 0)
        <div class="mb-12">
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
                            
                            <!-- Price and Info -->
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
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                @if($masterClass->isFree())
                                    <button wire:click="subscribe({{ $masterClass->id }})" 
                                            class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                                        S'inscrire gratuitement
                                    </button>
                                @else
                                    <button wire:click="subscribe({{ $masterClass->id }})" 
                                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                        Acheter {{ number_format($masterClass->price, 2) }} €
                                    </button>
                                @endif
                                
                                <button wire:click="viewMasterClass({{ $masterClass->id }})" 
                                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                    Voir
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $availableMasterClasses->links() }}
            </div>
        </div>
    @endif

    <!-- Paid Master Classes -->
    @if($activeTab === 'paid' && $paidMasterClasses->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Mes Formations Payées</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($paidMasterClasses as $masterClass)
                    @php
                        $subscription = $masterClass->subscriptions->first();
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                    {{ $masterClass->title }}
                                </h3>
                                <div class="flex flex-col items-end">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mb-1">
                                        Payée
                                    </span>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($masterClass->price, 2) }} €</span>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $masterClass->description }}
                            </p>
                            
                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Progression</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ number_format($subscription->getProgressPercentage(), 1) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $subscription->getProgressPercentage() }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Course Info -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>{{ $masterClass->chapters->count() }} chapitres</span>
                                <span>Acheté le {{ $subscription->started_at->format('d/m/Y') }}</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button wire:click="startLearning({{ $masterClass->id }})" 
                                        class="flex-1 bg-purple-600 text-white text-center py-2 px-4 rounded-md hover:bg-purple-700 transition-colors">
                                    @if($subscription->progress > 0)
                                        Continuer
                                    @else
                                        Commencer
                                    @endif
                                </button>
                                <button wire:click="viewMasterClass({{ $masterClass->id }})" 
                                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $paidMasterClasses->links() }}
            </div>
        </div>
    @endif

    <!-- Empty States -->
    @if(($activeTab === 'subscribed' && $subscribedMasterClasses->count() === 0) ||
        ($activeTab === 'available' && $availableMasterClasses->count() === 0) ||
        ($activeTab === 'paid' && $paidMasterClasses->count() === 0))
        <div class="text-center py-12">
            <div class="max-w-md mx-auto">
                @if($activeTab === 'subscribed')
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune inscription</h3>
                    <p class="mt-1 text-sm text-gray-500">Vous n'êtes inscrit à aucune formation pour le moment.</p>
                    <div class="mt-6">
                        <button wire:click="setActiveTab('available')" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Découvrir les formations
                        </button>
                    </div>
                @elseif($activeTab === 'available')
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune formation disponible</h3>
                    <p class="mt-1 text-sm text-gray-500">Aucune nouvelle formation n'est disponible actuellement.</p>
                @else
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune formation payée</h3>
                    <p class="mt-1 text-sm text-gray-500">Vous n'avez acheté aucune formation premium pour le moment.</p>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Subscription Modal Component -->
<livewire:components.subscription-modal />

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
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 5000);
    });

    $wire.on('redirect-to-payment', (data) => {
        const masterClassId = data[0]?.masterClassId || data.masterClassId;
        // Ici vous pouvez implémenter la redirection vers votre système de paiement
        // Par exemple: window.location.href = `/payment/master-class/${masterClassId}`;
        console.log('Redirection vers le paiement pour la master class:', masterClassId);
    });
</script>
@endscript
