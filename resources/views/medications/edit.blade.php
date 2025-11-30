<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl lg:text-3xl text-gray-800 dark:text-gray-100 leading-tight">
            Editando: {{ $medication->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 lg:px-8">

                <form action="{{ route('medications.update', $medication) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Nombre del Medicamento
                        </label>
                        <input type="text" name="name" id="name" required
                               class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('name', $medication->name) }}">
                        @error('name')
                            <span class="text-red-500 text-base">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Descripción (Opcional)
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $medication->description) }}</textarea>
                    </div>

                    <div>
                        <label for="photo" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Cambiar Foto (Opcional)
                        </label>
                        @if ($medication->photo_path)
                            <div class="my-3">
                                <img src="{{ Storage::url($medication->photo_path) }}" alt="Foto actual"
                                     class="w-24 h-24 rounded-lg object-cover border border-gray-300 dark:border-gray-700">
                            </div>
                        @endif
                        <input
                            type="file"
                            name="photo"
                            id="photo"
                            accept="image/*"
                            capture="environment" {{-- sugiere cámara trasera en móviles --}}
                            class="mt-2 block w-full text-lg text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none"
                        >
                        <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
                            Sube una nueva imagen solo si deseas reemplazar la actual. La foto se ajusta automáticamente para que no pese tanto.
                        </p>
                        @error('photo')
                            <span class="text-red-500 text-base">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr class="border-gray-300 dark:border-gray-700">

                    <div>
                        <label for="dose_quantity" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Dosis por Toma
                        </label>
                        <input type="number" name="dose_quantity" id="dose_quantity" min="1" required
                               class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               value="{{ old('dose_quantity', $medication->dose_quantity) }}">
                        <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
                            Ej: 1 pastilla, 2 gotas, etc.
                        </p>
                        @error('dose_quantity')
                            <span class="text-red-500 text-base">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="dose_type" class="block text-xl font-medium text-gray-700 dark:text-gray-200">Tipo de Dosis</label>
                        <select name="dose_type" id="dose_type" required
                                class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="unit" @selected(old('dose_type', $medication->dose_type) == 'unit')>Unidad(es) (Ej: 1 pastilla)</option>
                            <option value="half" @selected(old('dose_type', $medication->dose_type) == 'half')>Media(s) (Ej: 1/2 pastilla)</option>
                            <option value="quarter" @selected(old('dose_type', $medication->dose_type) == 'quarter')>Cuarto(s) (Ej: 1/4 pastilla)</option>
                            <option value="drop" @selected(old('dose_type', $medication->dose_type) == 'drop')>Gota(s)</option>
                        </select>
                        @error('dose_type') 
                            <span class="text-red-500 text-base">{{ $message }}</span> 
                        @enderror
                    </div>

                    <hr class="border-gray-300 dark:border-gray-700">

                    <div>
                        <label for="frequency_hours" class="block text-xl font-medium text-gray-700 dark:text-gray-200">
                            Frecuencia
                        </label>
                        <select name="frequency_hours" id="frequency_hours" required
                                class="mt-2 block w-full text-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="4" @selected(old('frequency_hours', $medication->frequency_hours) == '4')>Cada 4 horas</option>
                            <option value="6" @selected(old('frequency_hours', $medication->frequency_hours) == '6')>Cada 6 horas</option>
                            <option value="8" @selected(old('frequency_hours', $medication->frequency_hours) == '8')>Cada 8 horas</option>
                            <option value="12" @selected(old('frequency_hours', $medication->frequency_hours) == '12')>Cada 12 horas</option>
                            <option value="24" @selected(old('frequency_hours', $medication->frequency_hours) == '24')>Cada 24 horas (una vez al día)</option>
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
                               value="{{ old('start_time', \Carbon\Carbon::parse($medication->start_time)->format('H:i')) }}">
                        @error('start_time')
                            <span class="text-red-500 text-base">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-4 bg-yellow-100 dark:bg-yellow-900 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-200 rounded-lg">
                        <h4 class="font-bold">¡Atención al cambiar el horario!</h4>
                        <p class="text-sm mt-1">Si modificas la frecuencia o la hora de inicio:</p>
                        <ul class="list-disc list-inside text-sm mt-2">
                            <li>Tu historial de tomas <strong>completadas</strong> se mantendrá intacto.</li>
                            <li>Todas las tomas <strong>pendientes</strong> (futuras o retrasadas) se borrarán y se generará un nuevo calendario.</li>
                        </ul>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-6 pt-4">
                        <a href="{{ route('medications.show', $medication) }}"
                           class="text-lg text-gray-600 dark:text-gray-400 hover:underline">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-8 py-5 bg-blue-600 border border-transparent rounded-lg font-semibold text-xl text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Actualizar Medicamento
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
