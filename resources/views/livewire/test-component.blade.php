<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-lg font-bold mb-4">Livewire Test Component</h2>
    
    <div class="mb-4">
        <p class="text-gray-600">Count: <span class="font-bold text-blue-600">{{ $count }}</span></p>
        @if($message)
            <p class="text-green-600 mt-2">{{ $message }}</p>
        @endif
    </div>
    
    <button wire:click="increment" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Click Me (Livewire Test)
    </button>
    
    <div class="mt-4 text-sm text-gray-500">
        If this button increases the count, Livewire is working properly.
    </div>
</div>
