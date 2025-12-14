<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl lg:text-3xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Panel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-light text-gray-900 dark:text-white">
                            Mis Medicamentos
                        </h1>
                        <a href="{{ route('medications.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xl text-white hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Añadir
                        </a>
                    </div>

                    @if($lowStockMedications->isNotEmpty())
                        <div class="mb-6 p-4 bg-yellow-100 dark:bg-yellow-900 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-200 rounded-lg">
                            <h3 class="font-bold text-lg">¡Stock bajo!</h3>
                            <p>Los siguientes medicamentos se están agotando:</p>
                            <ul class="list-disc list-inside mt-2">
                                @foreach($lowStockMedications as $med)
                                    <li>
                                        <strong>{{ $med->name }}</strong> 
                                        (Stock actual: {{ $med->current_stock }} {{ $med->stock_unit }})
                                        <div x-data="{ stockModal: false }">
                                            <button @click="stockModal = true" class="ml-2 text-blue-600 dark:text-blue-400 hover:underline text-sm font-semibold">Añadir Stock</button>
                                            
                                            <div x-show="stockModal" 
                                                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                                 class="fixed inset-0 z-50 flex items-center justify-center p-4" 
                                                 style="background-color: rgba(0, 0, 0, 0.5); display: none;">
                                                <div @click.away="stockModal = false" 
                                                     class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-8 w-full max-w-lg mx-auto">
                                                    <h3 class="text-3xl font-light text-gray-900 dark:text-gray-100 mb-6">Añadir stock para: <strong>{{ $med->name }}</strong></h3>
                                                    <form action="{{ route('medications.addStock', $med) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div>
                                                            <label for="new_stock_quantity_{{ $med->id }}" class="block text-xl font-medium text-gray-700 dark:text-gray-300">
                                                                ¿Cuántos/as <strong class="text-blue-500">{{ $med->stock_unit }}</strong> nuevos/as vas a añadir?
                                                            </label>
                                                            <input type="number" name="new_stock_quantity" id="new_stock_quantity_{{ $med->id }}" min="1" required
                                                                   class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                                   placeholder="Ej: 30" value="1">
                                                        </div>
                                                        <div class="flex items-center justify-end space-x-6 pt-6 mt-4 border-t border-gray-200 dark:border-gray-700">
                                                            <button type="button" @click="stockModal = false" class="text-lg text-gray-600 dark:text-gray-400 hover:underline">Cancelar</button>
                                                            <button type="submit" class="inline-flex items-center px-8 py-4 bg-blue-600 border border-transparent rounded-lg font-semibold text-lg text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800">Confirmar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($medications->isEmpty())
                        <p class="text-center text-xl text-gray-500 dark:text-gray-400 py-16">
                            Aún no has añadido ningún medicamento.
                        </p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            @foreach ($medications as $med)
                                <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                                    <div>
                                        @if ($med->photo_path)
                                            <img src="{{ Storage::url($med->photo_path) }}" alt="Foto de {{ $med->name }}" 
                                                 class="w-full h-48 object-cover rounded-md mb-4 border dark:border-gray-600">
                                        @else
                                            <div class="w-full h-48 rounded-md mb-4 border dark:border-gray-700 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8M10 12h4m-2-2v4M4 8V6c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v2M4 8h16" />
                                                </svg>
                                            </div>
                                        @endif

                                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $med->name }}
                                        </h3>
                                        <p class="text-gray-600 dark:text-gray-400 text-base mt-2">
                                            Cada {{ $med->frequency_hours }} horas
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-400 text-base">
                                            Próxima toma: 
                                            <strong class="dark:text-gray-200">{{ $med->next_take_time }}</strong>
                                        </p>
                                    </div>

                                    <div class="mt-6">
                                        @php
                                            // Definir qué tipos de dosis son "contables"
                                            $contableTypes = ['unit', 'half', 'quarter'];
                                        @endphp
                        
                                        @if (in_array($med->dose_type, $contableTypes))
                                            @php
                                                $stockPercentage = ($med->current_stock > 0 && $med->total_stock > 0) ? ($med->current_stock / $med->total_stock) * 100 : 0;
                                                $stockColor = 'bg-green-600 dark:bg-green-500';
                                                if ($stockPercentage < 50) $stockColor = 'bg-yellow-500 dark:bg-yellow-400';
                                                if ($stockPercentage < 20) $stockColor = 'bg-red-600 dark:bg-red-500';
                                            @endphp
                                            <div>
                                                <div class="flex justify-between text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    <span class="font-bold">Stock:</span>
                                                    <span>{{ $med->current_stock }} / {{ $med->total_stock }} {{ $med->stock_unit }}</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-1">
                                                    <div class="{{ $stockColor }} h-2.5 rounded-full" style="width: {{ $stockPercentage }}%"></div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="font-bold text-sm text-gray-700 dark:text-gray-300">Stock:</span>
                                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                    {{ $med->current_stock }} {{ $med->stock_unit }} restantes
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('medications.show', $med) }}" 
                                       class="mt-6 block w-full text-center px-6 py-3 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-lg font-semibold text-lg text-white hover:bg-gray-700 active:bg-gray-800">
                                        Ver Tomas de Hoy
                                    </a>
                                </div>
                            @endforeach
                            </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>





