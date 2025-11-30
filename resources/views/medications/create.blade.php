<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl lg:text-3xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Añadir Nuevo Medicamento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">
                
                <form action="{{ route('medications.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <div>
                        <label for="name" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Nombre del Medicamento
                        </label>
                        <input type="text" name="name" id="name" required
                               class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('name') }}">
                        @error('name') 
                            <span class="text-red-500 text-base">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Descripción (Opcional)
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="photo" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Foto (Opcional)
                        </label>
                        <input
                            type="file"
                            name="photo"
                            id="photo"
                            accept="image/*"
                            capture="environment" {{-- 👈 sugiere usar cámara trasera en móviles --}}
                            class="mt-2 block w-full text-lg text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none"
                        >
                        <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
                            Una foto clara de la caja o la pastilla. Se ajusta automáticamente para que no pese tanto.
                        </p>
                        @error('photo') 
                            <span class="text-red-500 text-base">{{ $message }}</span> 
                        @enderror
                    </div>

                    <hr class="border-gray-300 dark:border-gray-700">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="total_stock" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                                Stock Total
                            </label>
                            <input type="number" name="total_stock" id="total_stock" min="1" required
                                   class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Ej: 30" value="{{ old('total_stock') }}">
                            @error('total_stock') 
                                <span class="text-red-500 text-base">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div>
                            <label for="stock_unit" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                                Unidad de Stock
                            </label>
                            <select name="stock_unit" id="stock_unit" required
                                    class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="unidades" @selected(old('stock_unit') == 'unidades')>Unidades (Pastillas)</option>
                                <option value="goteros" @selected(old('stock_unit') == 'goteros')>Gotero(s)</option>
                                <option value="inyectables" @selected(old('stock_unit') == 'inyectables')>Inyectable(s)</option>
                                <option value="cajas" @selected(old('stock_unit') == 'cajas')>Caja(s)</option>
                            </select>
                            @error('stock_unit') 
                                <span class="text-red-500 text-base">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="dose_quantity" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                                Dosis por Toma
                            </label>
                            <input type="number" name="dose_quantity" id="dose_quantity" min="1" required
                                   class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Ej: 1" value="{{ old('dose_quantity', 1) }}">
                            <p class="mt-1 text-base text-gray-500 dark:text-gray-400">Ej: 1 pastilla, 2 gotas, etc.</p>
                            @error('dose_quantity') 
                                <span class="text-red-500 text-base">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div>
                            <label for="dose_type" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                                Tipo de Dosis
                            </label>
                            <select name="dose_type" id="dose_type" required
                                    class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="unit" @selected(old('dose_type') == 'unit')>Unidad(es) (Ej: 1 pastilla)</option>
                                <option value="half" @selected(old('dose_type') == 'half')>Media(s) (Ej: 1/2 pastilla)</option>
                                <option value="quarter" @selected(old('dose_type') == 'quarter')>Cuarto(s) (Ej: 1/4 pastilla)</option>
                                <option value="drop" @selected(old('dose_type') == 'drop')>Gota(s)</option>
                            </select>
                            @error('dose_type') 
                                <span class="text-red-500 text-base">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    <hr class="border-gray-300 dark:border-gray-700">

                    <div>
                        <label for="frequency_hours" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Frecuencia
                        </label>
                        <select name="frequency_hours" id="frequency_hours" required
                                class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccione cada cuántas horas...</option>
                            <option value="4" @selected(old('frequency_hours') == '4')>Cada 4 horas</option>
                            <option value="6" @selected(old('frequency_hours') == '6')>Cada 6 horas</option>
                            <option value="8" @selected(old('frequency_hours') == '8')>Cada 8 horas</option>
                            <option value="12" @selected(old('frequency_hours') == '12')>Cada 12 horas</option>
                            <option value="24" @selected(old('frequency_hours') == '24')>Cada 24 horas (una vez al día)</option>
                        </select>
                        @error('frequency_hours') 
                            <span class="text-red-500 text-base">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="start_time" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Hora de la Primera Toma
                        </label>
                        <input type="time" name="start_time" id="start_time" required
                               class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('start_time', '08:00') }}">
                        <p class="mt-1 text-base text-gray-500 dark:text-gray-400">La primera toma del día (Ej: 08:00 AM).</p>
                        @error('start_time') 
                            <span class="text-red-500 text-base">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-6 pt-4">
                        <a href="{{ route('dashboard') }}" class="text-lg text-gray-600 dark:text-gray-400 hover:underline">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-8 py-5 bg-blue-600 border border-transparent rounded-lg font-semibold text-xl text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Guardar Medicamento
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

