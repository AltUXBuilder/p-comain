<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name'          => fake()->firstName(),
            'last_name'           => fake()->lastName(),
            'email'               => fake()->unique()->safeEmail(),
            'email_verified_at'   => now(),
            'password'            => Hash::make('password'),
            'date_of_birth'       => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'address_line_1'      => fake()->streetAddress(),
            'city'                => fake()->city(),
            'postcode'            => 'SW1A1AA',
            'country'             => 'GB',
            'terms_accepted'      => true,
            'terms_accepted_at'   => now(),
            'two_factor_enabled'  => true,
            'remember_token'      => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn() => ['email_verified_at' => null]);
    }

    public function withStripe(string $stripeId = null): static
    {
        return $this->state(fn() => ['stripe_id' => $stripeId ?? 'cus_test_' . uniqid()]);
    }
}
