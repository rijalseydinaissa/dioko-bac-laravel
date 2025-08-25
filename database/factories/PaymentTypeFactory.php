<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Électricité', 'Internet', 'Eau', 'Loyer', 
            'Assurance', 'Téléphone', 'Gaz', 'Services divers'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => fake()->word(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}