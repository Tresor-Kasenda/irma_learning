<div>
    <div class="max-w-md mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Accéder à la formation</h2>
        
        @if($error)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="validateCode" class="space-y-4">
            <div>
                <label for="accessCode" class="block text-sm font-medium text-gray-700">
                    Code d'accès
                </label>
                <input
                    type="text"
                    id="accessCode"
                    wire:model="accessCode"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Entrez votre code d'accès"
                >
                @error('accessCode')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <span wire:loading.remove>Valider</span>
                <span wire:loading>
                    Validation en cours...
                </span>
            </button>
        </form>
    </div>
</div>
