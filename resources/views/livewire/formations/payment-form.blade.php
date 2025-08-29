<div>
    <form wire:submit="processPayment" class="space-y-4">
        <div>
            <label for="cardNumber" class="block text-sm font-medium text-gray-700">
                Numéro de carte
            </label>
            <input type="text"
                   id="cardNumber"
                   wire:model="cardNumber"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="1234 5678 9012 3456">
            @error('cardNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="expiryDate" class="block text-sm font-medium text-gray-700">
                    Date d'expiration
                </label>
                <input type="text"
                       id="expiryDate"
                       wire:model="expiryDate"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="MM/YY">
                @error('expiryDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="cvv" class="block text-sm font-medium text-gray-700">
                    CVV
                </label>
                <input type="text"
                       id="cvv"
                       wire:model="cvv"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="123">
                @error('cvv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <button type="button"
                    wire:click="$set('showPaymentModal', false)"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Annuler
            </button>
            <button type="submit"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span wire:loading.remove wire:target="processPayment">
                    Payer {{ $formation->price }}€
                </span>
                <span wire:loading wire:target="processPayment">
                    Traitement...
                </span>
            </button>
        </div>
    </form>
</div>
