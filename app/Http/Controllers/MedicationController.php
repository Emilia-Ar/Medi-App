<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver; // 👈 añadido para posible uso de Imagick
use Intervention\Image\Encoders\JpegEncoder;


class MedicationController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Muestra la lista de medicamentos del usuario.
     */
    public function index()
    {
        return redirect()->route('dashboard');
    }

    /**
     * Muestra el formulario para crear un nuevo medicamento.
     */
    public function create()
    {
        return view('medications.create');
    }

    /**
     * Guarda el nuevo medicamento en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // ampliamos a 8 MB, porque la cámara del celu genera fotos grandes
            'photo' => 'nullable|image|max:8192',
            'total_stock' => 'required|integer|min:1',
            'stock_unit' => 'required|string|max:50',
            'dose_quantity' => 'required|integer|min:1',
            'dose_type' => 'required|string|in:unit,half,quarter,drop',
            'frequency_hours' => 'required|integer|in:4,6,8,12,24',
            'start_time' => 'required|date_format:H:i',
        ]);

        // 2. MANEJAR LA FOTO (redimensionar / comprimir)
        $path = null;
        if ($request->hasFile('photo')) {
            $path = $this->processPhoto($request->file('photo'));
        }

        // 3. PREPARAR DATOS ADICIONALES
        $data['user_id'] = auth()->id();
        $data['current_stock'] = $data['total_stock']; // El stock actual es igual al total al crearlo
        $data['photo_path'] = $path; // Null si no se subió foto

        // 4. CREAR EL MODELO
        $medication = Medication::create($data);

        // 5. GENERAR TOMAS
        $this->scheduleService->generateTakes($medication);

        // 6. RESPONDER
        return redirect()->route('dashboard')->with('status', '¡Medicamento añadido con éxito!');
    }

    /**
     * Muestra la vista detallada (checklist de tomas diarias + resumen).
     */
    public function show(Request $request, Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        // Aliases para que la vista funcione sin cambiar nombres
        $medication->notes = $medication->description;
        $medication->dose_value = $medication->dose_quantity;
        $medication->dose_label = $medication->stock_unit;

        // ✅ Tomas de hoy
        $todaysTakes = $medication->takes()
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $pastPendingTakes = $medication->takes()
            ->where('scheduled_at', '<', today()->startOfDay())
            ->whereNull('completed_at')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        // Plan de hoy para la sección "Plan de hoy"
        $todaySchedule = $todaysTakes;

        // Historial con filtros (from / to)
        $takesQuery = $medication->takes()
            ->orderBy('scheduled_at', 'desc');

        if ($request->filled('from')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $takesQuery->where('scheduled_at', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $takesQuery->where('scheduled_at', '<=', $to);
        }

        $takes = $takesQuery->paginate(10)->withQueryString();

        // Resumen de stock
        $stockActual = $medication->current_stock ?? 0;

        // Tomas por día según frecuencia (4, 6, 8, 12, 24)
        $tomasPorDia = $medication->frequency_hours > 0
            ? intdiv(24, $medication->frequency_hours)
            : 0;

        // Consumo diario aproximado (tomas por día * cantidad por toma)
        $consumoDiario = $tomasPorDia * ($medication->dose_quantity ?? 1);

        // Días de cobertura estimados
        $diasCobertura = $consumoDiario > 0
            ? (int) floor($stockActual / $consumoDiario)
            : 0;

        $stockSummary = [
            'stock_actual' => $stockActual,
            'tomas_por_dia' => $tomasPorDia,
            'dias_cobertura' => $diasCobertura,
        ];

        // Adherencia últimos 7 días
        $fromAdherence = now()->subDays(7)->startOfDay();
        $toAdherence = now()->endOfDay();

        $takesForAdherence = $medication->takes()
            ->whereBetween('scheduled_at', [$fromAdherence, $toAdherence])
            ->get();

        $total = $takesForAdherence->count();
        $taken = $takesForAdherence->whereNotNull('completed_at')->count();

        $adherence = [
            'taken' => $taken,
            'total' => $total,
            'percentage' => $total > 0 ? round(($taken / $total) * 100) : 0,
        ];

        return view('medications.show', [
            'medication' => $medication,
            'todaysTakes' => $todaysTakes,
            'pastPendingTakes' => $pastPendingTakes,
            'todaySchedule' => $todaySchedule,
            'takes' => $takes,
            'stockSummary' => $stockSummary,
            'adherence' => $adherence,
        ]);
    }

    /**
     * Muestra el formulario para editar un medicamento.
     */
    public function edit(Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        return view('medications.edit', [
            'medication' => $medication,
        ]);
    }

    /**
     * Actualiza el medicamento en la base de datos.
     */
    public function update(Request $request, Medication $medication)
    {
        // 1. Política de seguridad
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        // 2. Validación
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // ampliamos a 8 MB también aquí
            'photo' => 'nullable|image|max:8192',
            'dose_quantity' => 'required|integer|min:1',
            'dose_type' => 'required|string|in:unit,half,quarter,drop',
            'frequency_hours' => 'required|integer|in:4,6,8,12,24',
            'start_time' => 'required|date_format:H:i',
        ]);

        // 3. Comprobar si el horario cambió
        $newStartTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $oldStartTime = Carbon::parse($medication->start_time)->format('H:i:s');

        $scheduleChanged = (
            $medication->frequency_hours !== (int) $data['frequency_hours'] ||
            $oldStartTime !== $newStartTime
        );

        // 4. Manejar la subida de la nueva foto
        if ($request->hasFile('photo')) {
            // Borrar la anterior si existe
            if ($medication->photo_path) {
                Storage::disk('public')->delete($medication->photo_path);
            }

            // Guardar la nueva procesada
            $data['photo_path'] = $this->processPhoto($request->file('photo'));
        }

        // 5. Actualizar el modelo
        $medication->update($data);

        // 6. Recalcular calendario si cambió la frecuencia u horario
        if ($scheduleChanged) {
            $medication->takes()
                ->whereNull('completed_at')
                ->delete();

            $this->scheduleService->generateTakes($medication);
        }

        // 7. Redirigir
        return redirect()
            ->route('medications.show', $medication)
            ->with('status', '¡Medicamento actualizado con éxito!');
    }

    /**
     * Genera y descarga un reporte PDF de las tomas de un medicamento.
     * - En local (con GD instalada) genera el PDF normalmente.
     * - En Railway (sin GD) muestra un mensaje y no rompe la app.
     */
    public function downloadReport(Request $request, Medication $medication)
    {
        // 1. Seguridad
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación de fechas
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // 3. Obtener las tomas
        $takes = $medication->takes()
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // 4. Calcular estadísticas base
        $stats = [
            'total' => $takes->count(),
            'completed' => $takes->whereNotNull('completed_at')->count(),
            'missed' => $takes->whereNull('completed_at')->count(),
        ];

        // 5. Calcular Tasa de Cumplimiento
        $complianceRate = 0;
        if ($stats['total'] > 0) {
            $complianceRate = round(($stats['completed'] / $stats['total']) * 100);
        }

        // 6. Cargar el logo en Base64
        $logoPath = public_path('images/logo-medicina.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = base64_encode($logoData);
        }

        // 7. Preparar todos los datos para la vista
        $data = [
            'medication' => $medication,
            'takes' => $takes,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'logoBase64' => $logoBase64,
            'user' => auth()->user(),
            'complianceRate' => $complianceRate,
        ];

        // 7bis. Si el servidor NO tiene la extensión GD, evitamos romper la app en producción
        if (!extension_loaded('gd')) {
            return back()->with(
                'status',
                'No se pudo generar el PDF porque el servidor no tiene instalada la extensión GD. ' .
                'Podés generar este reporte en PDF desde tu entorno local, donde sí está disponible.'
            );
        }

        // 8. Generar y devolver el PDF (funciona en entornos con GD instalada)
        $pdf = Pdf::loadView('reports.medication', $data);

        $fileName = 'reporte-' . Str::slug($medication->name) . '-' .
            $startDate->format('Y-m-d') . '-al-' . $endDate->format('Y-m-d') . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Aumenta el stock de un medicamento existente.
     */
    public function addStock(Request $request, Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'new_stock_quantity' => 'required|integer|min:1',
        ]);

        $newStock = $data['new_stock_quantity'];

        $medication->update([
            'current_stock' => $medication->current_stock + $newStock,
            'total_stock' => $medication->total_stock + $newStock,
        ]);

        return back()->with('status', "¡Stock de {$medication->name} actualizado con éxito!");
    }

    /**
     * Elimina un medicamento.
     */
    public function destroy(Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        if ($medication->photo_path) {
            Storage::disk('public')->delete($medication->photo_path);
        }

        $medication->delete();

        return redirect()->route('dashboard')->with('status', "¡Medicamento '{$medication->name}' eliminado con éxito!");
    }

    /**
     * Marca una unidad de stock (ej. un gotero) como agotada.
     */
    public function useStock(Request $request, Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        // Solo descontamos si el stock actual es mayor que 0
        if ($medication->current_stock > 0) {
            $medication->update([
                'current_stock' => $medication->current_stock - 1,
            ]);
        }

        return back()->with('status', "¡Se ha registrado el uso de 1 {$medication->stock_unit}!");
    }

    /**
     * Procesa la foto del medicamento:
     * - Respeta la orientación del celular según EXIF.
     * - Redimensiona hasta 1024x1024 manteniendo proporciones.
     * - Convierte a JPG comprimido para reducir el peso.
     * - Si no hay GD/Imagick, guarda el archivo original (fallback) para no romper en producción.
     */
    private function processPhoto(UploadedFile $photo): string
    {
        // Nombre destino por defecto
        $filename = (string) Str::uuid() . '.jpg';
        $path = 'photos/' . $filename;

        try {
            $manager = null;

            // 1) Preferir GD si la extensión está disponible
            if (extension_loaded('gd')) {
                $manager = new ImageManager(new GdDriver());
            }
            // 2) Si no hay GD, probar Imagick si está disponible
            elseif (extension_loaded('imagick') && class_exists(ImagickDriver::class)) {
                $manager = new ImageManager(new ImagickDriver());
            }

            if ($manager) {
                // Escalamos manteniendo proporción hasta 1024x1024
                $image = $manager
                    ->read($photo->getPathname())
                    ->orient()
                    ->scaleDown(1024, 1024);

                // 👇 encode con la API nueva de Intervention v3
                $encoded = $image->encode(new JpegEncoder(quality: 80));

                // Guardar como JPG comprimido en el disco public
                Storage::disk('public')->put($path, (string) $encoded);

                return $path;
            }

            // 3) Fallback: sin drivers → guardar original
            return $photo->store('photos', 'public');
        } catch (\Throwable $e) {
            // Cualquier fallo en procesamiento → fallback seguro
            return $photo->store('photos', 'public');
        }
    }
}



