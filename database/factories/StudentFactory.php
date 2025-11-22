<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => \App\Models\Classroom::factory(),
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['laki-laki', 'perempuan']),
            'nis' => $this->faker->unique()->numerify('#####'),
            'active' => true,
        ];
    }
}
