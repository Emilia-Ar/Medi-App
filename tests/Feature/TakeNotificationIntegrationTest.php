<?php

use App\Models\User;
use App\Models\Medication;
use App\Models\Take;
use App\Notifications\TakeReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('envía notificación cuando existe una toma pendiente', function () {

    Notification::fake();

    $user = User::factory()->create();

    $medication = Medication::factory()->create([
        'user_id' => $user->id,
        'name' => 'Aspirina',
    ]);

    $take = Take::factory()->create([
        'user_id' => $user->id,                 // ✅ clave
        'medication_id' => $medication->id,     // ✅ clave
        'scheduled_at' => now()->addMinute(),
        'completed_at' => null,
    ]);

    $user->notify(new TakeReminder($take));

    Notification::assertSentTo($user, TakeReminder::class);
});
