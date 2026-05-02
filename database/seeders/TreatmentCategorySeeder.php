<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Weight Loss',
                'slug'        => 'weight-loss',
                'description' => 'Clinically proven weight management treatments prescribed by our specialist team.',
                'icon'        => 'weight-loss',
                'sort_order'  => 1,
                'active'      => true,
            ],
            [
                'name'        => 'Erectile Dysfunction',
                'slug'        => 'erectile-dysfunction',
                'description' => 'Discreet, effective treatments for erectile dysfunction from UK-registered prescribers.',
                'icon'        => 'ed',
                'sort_order'  => 2,
                'active'      => true,
            ],
            [
                'name'        => 'Skin Health',
                'slug'        => 'skin-health',
                'description' => 'Prescription skincare treatments for acne, rosacea and more.',
                'icon'        => 'skin',
                'sort_order'  => 3,
                'active'      => true,
            ],
            [
                'name'        => 'Hair Loss',
                'slug'        => 'hair-loss',
                'description' => 'Evidence-based treatments to prevent hair loss and promote regrowth.',
                'icon'        => 'hair',
                'sort_order'  => 4,
                'active'      => true,
            ],
            [
                'name'        => 'Digestive Health',
                'slug'        => 'digestive-health',
                'description' => 'Treatments for IBS, acid reflux and other digestive conditions.',
                'icon'        => 'digestive',
                'sort_order'  => 5,
                'active'      => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('treatment_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
