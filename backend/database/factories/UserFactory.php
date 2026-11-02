<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     * Users authenticate with phone + PIN (stored in pin_hash). Default PIN: 1234.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // Fictitious numbers in the +2250000xxxxxx range (not an allocated Ivorian prefix), never real subscribers
            'phone' => '+2250000' . fake()->unique()->numerify('######'),
            'email' => null,
            'role' => 'client',
            'pin_hash' => static::$password ??= Hash::make('1234'),
            'is_active' => true,
            'phone_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function merchant(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'merchant']);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => null,
        ]);
    }
}
