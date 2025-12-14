<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        $total = $this->faker->numberBetween(10, 60);

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'photo_path' => null,

            'total_stock' => $total,
            'current_stock' => $total,
            'stock_unit' => $this->faker->randomElement(['unidades', 'goteros', 'inyectables', 'cajas']),

            'dose_quantity' => $this->faker->numberBetween(1, 2),
            'dose_type' => $this->faker->randomElement(['unit', 'half', 'quarter', 'drop']),

            'frequency_hours' => $this->faker->randomElement([4, 6, 8, 12, 24]),
            'start_time' => $this->faker->time('H:i'),
        ];
    }
}
