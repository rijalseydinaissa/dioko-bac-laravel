<?php

namespace Database\Factories;

use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payment_type_id' => PaymentType::factory(),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed', 'cancelled']),
            'payment_reference' => 'PAY-' . strtoupper(Str::random(10)) . '-' . now()->format('YmdHis'),
            'external_reference' => fake()->optional()->regexify('EXT-[A-Z0-9]{8}'),
            'payment_details' => fake()->optional()->randomElements([
                'transaction_id' => fake()->uuid(),
                'fees' => fake()->randomFloat(2, 10, 1000),
                'gateway' => fake()->randomElement(['stripe', 'paypal', 'wave'])
            ]),
            'attachment_path' => fake()->optional()->filePath(),
            'attachment_type' => fake()->optional()->randomElement(['pdf', 'jpg', 'png']),
            'processed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'failure_reason' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
            'failure_reason' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'failure_reason' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'processed_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'failure_reason' => fake()->sentence(),
        ]);
    }

    public function withAttachment(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachment_path' => 'payments/' . fake()->uuid() . '.pdf',
            'attachment_type' => 'pdf',
        ]);
    }
}