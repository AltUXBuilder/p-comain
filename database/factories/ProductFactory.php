<?php

namespace Database\Factories;

use App\Models\Treatment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'treatment_id'              => Treatment::factory(),
            'name'                      => fake()->words(3, true),
            'slug'                      => fake()->unique()->slug(),
            'generic_name'              => fake()->word(),
            'strength'                  => fake()->randomElement(['10mg', '25mg', '50mg', '100mg']),
            'form'                      => fake()->randomElement(['tablet', 'capsule', 'cream']),
            'dosage_instructions'       => 'Take once daily.',
            'product_type'              => 'POM',
            'price_one_off'             => fake()->randomFloat(2, 10, 100),
            'stock_quantity'            => 100,
            'minimum_stock_level'       => 10,
            'in_stock'                  => true,
            'requires_cold_chain'       => false,
            'requires_age_verification' => false,
            'has_questionnaire'         => false,
            'active'                    => true,
        ];
    }
}
