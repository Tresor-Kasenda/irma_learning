<main>
    <section class="my-32 mx-auto max-w-6xl w-full px-5 sm:px-10 flex flex-col md:flex-row gap-16">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border border-border-light rounded-lg shadow-sm p-8">
                <div class="flex flex-col gap-6">
                    <div class="items-start space-y-6">
                        <div class="w-full sm:w-24 h-24 overflow-hidden rounded-lg bg-bg-light flex-shrink-0">
                            @if($formation->image)
                                <img src="{{ asset('storage/'. $formation->image) }}" alt="{{ $formation->title }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center bg-primary-100 text-primary-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-fg-title">{{ $formation->title }}</h2>
                            <p class="text-fg-subtext mt-1">{{ $formation->short_description }}</p>

                            <div class="mt-4 flex flex-wrap gap-3">
                                <span
                                    class="bg-bg-lighter px-3 py-1.5 rounded-full text-xs font-medium text-primary-700 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $formation->duration_hours }} heures
                                </span>

                                <span
                                    class="bg-bg-lighter px-3 py-1.5 rounded-full text-xs font-medium text-primary-700 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                    </svg>
                                    {{ $formation->price > 0 ? $formation->price . '$' : 'Gratuit' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border-light"></div>

                    <div>
                        <h3 class="text-lg font-semibold text-fg-title">Confirmation de l'inscription</h3>
                        <p class="mt-2 text-fg-subtext">
                            Pour finaliser votre inscription à cette formation, veuillez entrer le code de confirmation
                            qui a été envoyé à votre adresse email.
                        </p>

                        <div class="space-y-4 mt-6">
                            <form wire:submit="confirmEnrollment" class="space-y-6">
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700">Code de
                                        confirmation</label>
                                    <div class="mt-1">
                                        <input
                                            type="text"
                                            id="code"
                                            name="code"
                                            wire:model="code"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                            placeholder="Entrez votre code à 6 chiffres"
                                        />
                                        @error('code')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex items-end flex-wrap justify-end">
                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        class="btn btn-sm sm:btn-md btn-solid bg-primary-600 text-white group justify-center text-center">
                                            <span class="relative z-10"
                                                  wire:loading.remove>Confirmer l'inscription</span>
                                        <span class="relative z-10" wire:loading>Traitement...</span>
                                        <span data-btn-layer class="before:bg-primary-800"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information panel -->
            <div class="mt-8 bg-primary-50 border border-primary-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-5 h-5 text-primary-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-primary-800">Information</h4>
                        <p class="mt-2 text-sm text-primary-700">
                            Le code de confirmation est valide pendant 30 minutes. Si vous ne recevez pas le code,
                            vérifiez votre dossier spam ou cliquez sur "Renvoyer le code".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
