<?php

namespace Database\Factories;

use App\Models\TreatmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'treatment_category_id' => TreatmentCategory::factory(),
            'name'                  => fake()->words(2, true),
            'slug'                  => fake()->unique()->slug(),
            'description'           => fake()->sentence(),
            'active'                => true,
        ];
    }
}
