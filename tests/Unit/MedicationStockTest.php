<?php

use App\Models\User;
use App\Models\Medication;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reduce el stock cuando se registra el uso de una unidad', function () {

    $user = User::factory()->create();

    $medication = Medication::factory()->create([
        'user_id' => $user->id,
        'current_stock' => 10,
        'total_stock' => 10,
    ]);

    $medication->update([
        'current_stock' => $medication->current_stock - 1,
    ]);

    expect($medication->fresh()->current_stock)->toBe(9);
});
