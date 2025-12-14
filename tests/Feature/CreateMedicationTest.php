<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('permite crear un medicamento desde el formulario', function () {

    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('medications.store'), [
        'name' => 'Aspirina',
        'description' => 'Después del desayuno',
        'photo' => UploadedFile::fake()->image('aspirina.jpg'),
        'total_stock' => 20,
        'stock_unit' => 'unidades',
        'dose_quantity' => 1,
        'dose_type' => 'unit',
        'frequency_hours' => 24,
        'start_time' => '08:00',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('medications', [
        'name' => 'Aspirina',
        'user_id' => $user->id,
    ]);
});
