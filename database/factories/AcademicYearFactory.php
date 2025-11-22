<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->year . '/' . ($this->faker->year + 1),
            'semester' => $this->faker->randomElement(['1', '2']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'active' => true,
        ];
    }
}
