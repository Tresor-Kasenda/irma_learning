@if($showModal && $masterClass)
    <!-- Slide-over panel -->
    <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Background overlay -->
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 x-show="$wire.showModal"
                 x-transition:enter="ease-in-out duration-500"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 wire:click="closeModal"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="relative w-screen max-w-md"
                     x-show="$wire.showModal"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full">
                    
                    <!-- Close button -->
                    <div class="absolute top-0 left-0 -ml-8 pt-4 pr-2 flex sm:-ml-10 sm:pr-4">
                        <button wire:click="closeModal" 
                                class="rounded-md text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                            <span class="sr-only">Fermer</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Slide-over content -->
                    <div class="h-full flex flex-col bg-white shadow-xl overflow-y-scroll">
                        <!-- Header -->
                        <div class="px-4 sm:px-6 py-6 bg-gradient-to-r from-blue-600 to-purple-600">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-lg font-medium text-white" id="slide-over-title">
                                        @if($modalType === 'payment')
                                            Achat de formation
                                        @else
                                            Inscription à la formation
                                        @endif
                                    </h2>
                                    <p class="mt-1 text-sm text-blue-100">
                                        @if($modalType === 'payment')
                                            Procédez au paiement pour accéder à cette formation premium
                                        @else
                                            Inscrivez-vous pour commencer votre apprentissage
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="relative flex-1 px-4 sm:px-6 py-6">
                            <!-- Course Preview -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center mb-3">
                                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $masterClass->title }}</h3>
                                        @if($masterClass->sub_title)
                                            <p class="text-sm text-gray-600">{{ $masterClass->sub_title }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <p class="text-gray-700 text-sm mb-4 line-clamp-3">{{ $masterClass->description }}</p>
                                
                                <!-- Course stats -->
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $masterClass->chapters->count() }} chapitres
                                        </div>
                                        
                                        @if($masterClass->duration)
                                            <div class="flex items-center text-gray-500">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $masterClass->duration }} min
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="text-right">
                                        @if($masterClass->isFree())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Gratuit
                                            </span>
                                        @else
                                            <span class="text-lg font-bold text-gray-900">{{ number_format($masterClass->price, 2) }} €</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($modalType === 'payment')
                                <!-- Payment Mode -->
                                <div class="mb-6">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Formation Premium</h3>
                                                <p class="mt-1 text-sm text-yellow-700">
                                                    Cette formation coûte <strong>{{ number_format($masterClass->price, 2) }} €</strong>. 
                                                    Vous aurez un accès à vie après l'achat.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Payment benefits -->
                                    <div class="space-y-3 mb-6">
                                        <h4 class="font-medium text-gray-900">Ce qui est inclus :</h4>
                                        <ul class="space-y-2 text-sm text-gray-600">
                                            <li class="flex items-center">
                                                <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Accès à vie à la formation
                                            </li>
                                            <li class="flex items-center">
                                                <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $masterClass->chapters->count() }} chapitres complets
                                            </li>
                                            <li class="flex items-center">
                                                <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Ressources téléchargeables
                                            </li>
                                            @if($masterClass->certifiable)
                                                <li class="flex items-center">
                                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Certificat de completion
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @else
                                <!-- Free Subscription Mode -->
                                @if($masterClass->isFree())
                                    <div class="mb-6">
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                            <div class="flex">
                                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-green-800">Formation Gratuite</h3>
                                                    <p class="mt-1 text-sm text-green-700">
                                                        Cette formation est entièrement gratuite. Inscrivez-vous pour commencer votre apprentissage immédiatement.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Terms and Conditions -->
                                <div class="mb-6">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="flex items-start cursor-pointer">
                                            <input type="checkbox" 
                                                   wire:model="termsAccepted" 
                                                   class="mt-1 mr-3 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <div class="text-sm">
                                                <span class="text-gray-700">
                                                    J'accepte les 
                                                    <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">conditions d'utilisation</a> 
                                                    et la 
                                                    <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">politique de confidentialité</a>
                                                </span>
                                                <p class="mt-1 text-gray-500">
                                                    En vous inscrivant, vous acceptez de recevoir des communications relatives à votre formation.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Footer -->
                        <div class="flex-shrink-0 px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="space-y-3">
                                @if($modalType === 'payment')
                                    <button wire:click="redirectToPayment" 
                                            class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Procéder au paiement ({{ number_format($masterClass->price, 2) }} €)
                                    </button>
                                @else
                                    <button wire:click="subscribe" 
                                            :disabled="!$wire.termsAccepted"
                                            class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed
                                                @if($masterClass->isFree()) bg-green-600 hover:bg-green-700 focus:ring-green-500 @else bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 @endif"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            @if($masterClass->isFree())
                                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                S'inscrire gratuitement
                                            @else
                                                S'inscrire maintenant
                                            @endif
                                        </span>
                                        <span wire:loading class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Inscription en cours...
                                        </span>
                                    </button>
                                @endif
                                
                                <button wire:click="closeModal" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
