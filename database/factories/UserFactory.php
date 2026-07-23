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
     * Sesuai skema users yang sudah dimodifikasi (username, role, fungsi, is_active).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles   = ['Admin HSSE', 'Admin Function', 'Manager HSSE', 'Manager Function'];
        $fungsis = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];

        return [
            'name'              => fake()->name(),
            'username'          => fake()->unique()->userName(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => fake()->randomElement($roles),
            'fungsi'            => fake()->randomElement($fungsis),
            'is_active'         => true,
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * User tidak aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
