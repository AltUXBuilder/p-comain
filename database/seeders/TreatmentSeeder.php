<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            // Weight Loss
            ['category_slug' => 'weight-loss', 'name' => 'Mounjaro',   'slug' => 'mounjaro',   'description' => 'Tirzepatide — a dual GIP and GLP-1 receptor agonist for weight management.', 'sort_order' => 1],
            ['category_slug' => 'weight-loss', 'name' => 'Wegovy',     'slug' => 'wegovy',     'description' => 'Semaglutide injection for chronic weight management.', 'sort_order' => 2],
            ['category_slug' => 'weight-loss', 'name' => 'Ozempic',    'slug' => 'ozempic',    'description' => 'Semaglutide injection — originally for type 2 diabetes, used for weight loss.', 'sort_order' => 3],
            ['category_slug' => 'weight-loss', 'name' => 'Orlistat',   'slug' => 'orlistat',   'description' => 'Orlistat capsules — prevents absorption of dietary fat.', 'sort_order' => 4],
            // ED
            ['category_slug' => 'erectile-dysfunction', 'name' => 'Sildenafil',     'slug' => 'sildenafil',      'description' => 'Generic sildenafil — the active ingredient in Viagra.', 'sort_order' => 1],
            ['category_slug' => 'erectile-dysfunction', 'name' => 'Tadalafil',      'slug' => 'tadalafil',       'description' => 'Long-acting treatment for erectile dysfunction.', 'sort_order' => 2],
            ['category_slug' => 'erectile-dysfunction', 'name' => 'Viagra Connect', 'slug' => 'viagra-connect',  'description' => 'Over-the-counter Viagra Connect 50mg.', 'sort_order' => 3],
            // Skin Health
            ['category_slug' => 'skin-health', 'name' => 'Tretinoin',  'slug' => 'tretinoin',  'description' => 'Prescription retinoid for acne and skin renewal.', 'sort_order' => 1],
            ['category_slug' => 'skin-health', 'name' => 'Lymecycline','slug' => 'lymecycline','description' => 'Antibiotic treatment for acne vulgaris.', 'sort_order' => 2],
            // Hair Loss
            ['category_slug' => 'hair-loss', 'name' => 'Finasteride',  'slug' => 'finasteride','description' => 'Prescription treatment for male pattern hair loss.', 'sort_order' => 1],
            ['category_slug' => 'hair-loss', 'name' => 'Minoxidil',    'slug' => 'minoxidil',  'description' => 'Topical solution to stimulate hair regrowth.', 'sort_order' => 2],
            // Digestive Health
            ['category_slug' => 'digestive-health', 'name' => 'Omeprazole',  'slug' => 'omeprazole',  'description' => 'Proton pump inhibitor for acid reflux and GERD.', 'sort_order' => 1],
            ['category_slug' => 'digestive-health', 'name' => 'Mebeverine',  'slug' => 'mebeverine',  'description' => 'Treatment for IBS symptoms.', 'sort_order' => 2],
        ];

        foreach ($treatments as $t) {
            $category = DB::table('treatment_categories')->where('slug', $t['category_slug'])->first();
            if (!$category) continue;

            DB::table('treatments')->updateOrInsert(
                ['slug' => $t['slug']],
                [
                    'treatment_category_id' => $category->id,
                    'name'                  => $t['name'],
                    'slug'                  => $t['slug'],
                    'description'           => $t['description'],
                    'sort_order'            => $t['sort_order'],
                    'active'                => true,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]
            );
        }
    }
}
