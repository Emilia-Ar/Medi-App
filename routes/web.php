<?php

use App\Http\Controllers\MedicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TakeController;
use App\Http\Controllers\PushSubscriptionController;
use App\Models\Medication;
use App\Models\User;
use App\Models\Take;
use App\Notifications\TakeReminder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Si el usuario está logueado, llévalo al dashboard.
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Si no está logueado, llévalo a la nueva vista de bienvenida.
    return view('welcome');
});

// 🔎 Ruta de debug para ver info de la foto de un medicamento
Route::get('/debug-foto/{medication}', function (Medication $medication) {
    return [
        'id'         => $medication->id,
        'name'       => $medication->name,
        'photo_path' => $medication->photo_path,
        'exists'     => $medication->photo_path
            ? Storage::disk('public')->exists($medication->photo_path)
            : null,
        'url'        => $medication->photo_path
            ? Storage::disk('public')->url($medication->photo_path)
            : null,
    ];
})->middleware(['auth'])->name('debug.medication.photo');

// --- DASHBOARD ---
Route::get('/dashboard', function () {

    // 1. Obtenemos TODOS los medicamentos del usuario
    $medications = Medication::where('user_id', auth()->id())
        ->orderBy('name')
        ->get();

    // 2. Medicamentos con STOCK BAJO (para la alerta)
    $contableTypes = ['unit', 'half', 'quarter'];
    $lowStockMedications = $medications
        ->whereIn('dose_type', $contableTypes)
        ->where('current_stock', '<=', 2);

    // 3. Pasamos las variables correctas a la vista
    return view('dashboard', [
        'medications'          => $medications,
        'lowStockMedications'  => $lowStockMedications,
    ]);

})->middleware(['auth', 'verified'])->name('dashboard');


// ✅ RUTA DE PRUEBA PARA WEBPUSH EN PRODUCCIÓN
Route::get('/test-push', function () {
    // Usuario logueado o el primero (solo para debug)
    $user = Auth::user() ?? User::first();

    if (! $user) {
        return 'No hay usuarios en la base de datos para probar.';
    }

    // Buscamos alguna Take asociada a un medicamento de este usuario
    $take = Take::whereHas('medication', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderBy('id', 'desc') // 🔁 usamos id, que seguro existe
        ->first();

    if (! $take) {
        return 'No encontré ninguna toma (takes) asociada a tus medicamentos. Crea al menos una para probar.';
    }

    // Enviamos la notificación WebPush real SIN pasar por la cola
    // notifyNow ignora ShouldQueue y la ejecuta en el acto
    $user->notifyNow(new TakeReminder($take));

    return 'Notificación enviada con TakeReminder, si todo está bien deberías verla en unos segundos 🔔';
})->middleware(['auth'])->name('debug.test.push');


// Rutas autenticadas
Route::middleware('auth')->group(function () {

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Stock de medicamentos
    Route::patch('/medications/{medication}/add-stock', [MedicationController::class, 'addStock'])
        ->name('medications.addStock');

    Route::patch('/medications/{medication}/use-stock', [MedicationController::class, 'useStock'])
        ->name('medications.useStock');

    // Medicamentos (CRUD)
    Route::resource('medications', MedicationController::class);

    // Marcar toma como completada
    Route::patch('/takes/{take}/complete', [TakeController::class, 'complete'])
        ->name('takes.complete');

    // Reporte PDF de tomas
    Route::get('/medications/{medication}/report', [MedicationController::class, 'downloadReport'])
        ->name('medications.report');

    // ✅ RUTAS PARA SUSCRIPCIONES PUSH
    Route::post('/push-subscribe', [PushSubscriptionController::class, 'store'])
        ->name('push.subscribe');

    Route::post('/push-unsubscribe', [PushSubscriptionController::class, 'destroy'])
        ->name('push.unsubscribe');
});

require __DIR__.'/auth.php';



