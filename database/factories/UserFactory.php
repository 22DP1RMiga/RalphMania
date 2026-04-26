<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username'          => fake()->unique()->userName(),
            'first_name'        => fake()->firstName(),
            'last_name'         => fake()->lastName(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'country'           => 'Latvia',
            'city'              => fake()->city(),
            'role_id'           => Role::where('name', 'user')->value('id') ?? 2,
            'is_active'         => true,
            'is_public'         => true,
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('name', 'administrator')->value('id') ?? 3,
        ]);
    }

    public function courier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('name', 'courier')->value('id') ?? 4,
        ]);
    }
}
