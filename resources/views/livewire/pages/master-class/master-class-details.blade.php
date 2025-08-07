<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg text-white p-8 mb-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">{{ $masterClass->title }}</h1>
            @if($masterClass->sub_title)
                <p class="text-xl mb-4 opacity-90">{{ $masterClass->sub_title }}</p>
            @endif
            
            <div class="flex flex-wrap items-center gap-6 text-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ $masterClass->chapters->count() }} chapitres
                </div>
                
                @if($masterClass->duration)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        {{ $masterClass->duration }} minutes
                    </div>
                @endif
                
                <div class="flex items-center">
                    @if($masterClass->isFree())
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold">
                            Gratuit
                        </span>
                    @else
                        <span class="bg-white text-gray-900 px-3 py-1 rounded-full font-semibold">
                            {{ number_format($masterClass->price, 2) }} €
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Description -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Description</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-700 leading-relaxed">{{ $masterClass->description }}</p>
                    
                    @if($masterClass->presentation)
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Présentation</h3>
                            <p class="text-gray-700">{{ $masterClass->presentation }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chapters -->
            @if($masterClass->chapters->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Programme de la formation</h2>
                    <div class="space-y-3">
                        @foreach($masterClass->chapters as $index => $chapter)
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $chapter->title }}</h3>
                                    @if($chapter->description)
                                        <p class="text-gray-600 text-sm mt-1">{{ $chapter->description }}</p>
                                    @endif
                                </div>
                                @if($subscription && $chapter->progress)
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Terminé
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Resources -->
            @if($masterClass->resources->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Ressources</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($masterClass->resources as $resource)
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $resource->title }}</h3>
                                    <p class="text-gray-600 text-sm">{{ $resource->type->value }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                <!-- Subscription Status -->
                @if($subscription)
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Votre progression</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($subscription->status->value === 'active') bg-green-100 text-green-800
                                @elseif($subscription->status->value === 'completed') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($subscription->status->value) }}
                            </span>
                        </div>
                        
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
                        
                        <button wire:click="startLearning" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition-colors font-semibold">
                            @if($subscription->progress > 0)
                                Continuer la formation
                            @else
                                Commencer la formation
                            @endif
                        </button>
                    </div>
                @else
                    <!-- Subscription Action -->
                    <div class="mb-6">
                        <div class="text-center mb-4">
                            @if($masterClass->isFree())
                                <div class="text-3xl font-bold text-green-600 mb-2">Gratuit</div>
                                <p class="text-gray-600">Inscrivez-vous gratuitement à cette formation</p>
                            @else
                                <div class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($masterClass->price, 2) }} €</div>
                                <p class="text-gray-600">Achetez cette formation premium</p>
                            @endif
                        </div>

                        @auth
                            @if($canAccess)
                                <!-- Terms checkbox -->
                                <div class="mb-4">
                                    <label class="flex items-start">
                                        <input type="checkbox" wire:model="termsAccepted" class="mt-1 mr-2">
                                        <span class="text-sm text-gray-600">
                                            J'accepte les <a href="#" class="text-blue-600 hover:underline">conditions d'utilisation</a>
                                        </span>
                                    </label>
                                </div>

                                @if($masterClass->isFree())
                                    <button wire:click="subscribe" 
                                            class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 transition-colors font-semibold"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove>S'inscrire gratuitement</span>
                                        <span wire:loading>Inscription en cours...</span>
                                    </button>
                                @else
                                    <button wire:click="redirectToPayment" 
                                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition-colors font-semibold">
                                        Acheter maintenant
                                    </button>
                                @endif
                            @else
                                <div class="text-center">
                                    <p class="text-gray-600 mb-4">
                                        @if(auth()->user()->isStudent())
                                            Cette formation nécessite un paiement.
                                        @else
                                            Vous n'avez pas accès à cette formation.
                                        @endif
                                    </p>
                                    
                                    @if(auth()->user()->isStudent())
                                        <a href="{{ route('student.my-master-classes', ['activeTab' => 'available']) }}" 
                                           class="inline-block bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                                            Voir mes formations
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="text-center">
                                <p class="text-gray-600 mb-4">Connectez-vous pour accéder à cette formation</p>
                                <a href="{{ route('login') }}" 
                                   class="inline-block bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 transition-colors font-semibold">
                                    Se connecter
                                </a>
                            </div>
                        @endauth
                    </div>
                @endif

                <!-- Course Info -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Chapitres</span>
                            <span class="font-semibold">{{ $masterClass->chapters->count() }}</span>
                        </div>
                        
                        @if($masterClass->duration)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Durée totale</span>
                                <span class="font-semibold">{{ $masterClass->duration }} min</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Statut</span>
                            <span class="font-semibold">{{ ucfirst($masterClass->status->value) }}</span>
                        </div>
                        
                        @if($masterClass->certifiable)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Certification</span>
                                <span class="font-semibold text-green-600">Disponible</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('notify', (data) => {
        const message = data[0]?.message || data.message;
        const type = data[0]?.type || data.type;
        
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 5000);
    });
</script>
@endscript
