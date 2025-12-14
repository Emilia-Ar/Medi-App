<?php

namespace Database\Factories;

use App\Models\Take;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TakeFactory extends Factory
{
    protected $model = Take::class;

    public function definition(): array
    {
        return [
            // Si tu tabla takes tiene user_id NOT NULL, hay que setearlo
            'user_id' => User::factory(),

            'medication_id' => Medication::factory(),
            'scheduled_at' => now()->addMinutes(10),
            'completed_at' => null,
        ];
    }
}

