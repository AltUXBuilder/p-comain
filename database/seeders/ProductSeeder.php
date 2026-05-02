<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ── Weight Loss ──────────────────────────────────────────────────
            [
                'treatment_slug'        => 'mounjaro',
                'name'                  => 'Mounjaro 2.5mg Injection Pen',
                'slug'                  => 'mounjaro-2-5mg',
                'generic_name'          => 'Tirzepatide',
                'brand_name'            => 'Mounjaro',
                'strength'              => '2.5mg',
                'form'                  => 'injection pen',
                'dosage_instructions'   => 'Inject 2.5mg subcutaneously once weekly for the first 4 weeks.',
                'product_type'          => 'POM',
                'price_one_off'         => null,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly',    'interval' => 'month', 'interval_count' => 1,  'price' => 149.00],
                    ['label' => 'Quarterly',  'interval' => 'month', 'interval_count' => 3,  'price' => 420.00],
                ]),
                'requires_cold_chain'   => true,
                'requires_age_verification' => false,
                'has_questionnaire'     => true,
                'active'                => true,
                'sort_order'            => 1,
            ],
            [
                'treatment_slug'        => 'mounjaro',
                'name'                  => 'Mounjaro 5mg Injection Pen',
                'slug'                  => 'mounjaro-5mg',
                'generic_name'          => 'Tirzepatide',
                'brand_name'            => 'Mounjaro',
                'strength'              => '5mg',
                'form'                  => 'injection pen',
                'dosage_instructions'   => 'Inject 5mg subcutaneously once weekly.',
                'product_type'          => 'POM',
                'price_one_off'         => null,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly',   'interval' => 'month', 'interval_count' => 1, 'price' => 169.00],
                    ['label' => 'Quarterly', 'interval' => 'month', 'interval_count' => 3, 'price' => 480.00],
                ]),
                'requires_cold_chain'   => true,
                'requires_age_verification' => false,
                'has_questionnaire'     => true,
                'active'                => true,
                'sort_order'            => 2,
            ],
            [
                'treatment_slug'        => 'orlistat',
                'name'                  => 'Orlistat 120mg Capsules',
                'slug'                  => 'orlistat-120mg',
                'generic_name'          => 'Orlistat',
                'brand_name'            => null,
                'strength'              => '120mg',
                'form'                  => 'capsule',
                'dosage_instructions'   => 'Take one 120mg capsule three times daily with each main meal containing fat.',
                'product_type'          => 'POM',
                'price_one_off'         => 39.99,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly', 'interval' => 'month', 'interval_count' => 1, 'price' => 35.99],
                ]),
                'requires_cold_chain'   => false,
                'requires_age_verification' => false,
                'has_questionnaire'     => false,
                'active'                => true,
                'sort_order'            => 3,
            ],
            // ── ED ───────────────────────────────────────────────────────────
            [
                'treatment_slug'        => 'sildenafil',
                'name'                  => 'Sildenafil 50mg Tablets',
                'slug'                  => 'sildenafil-50mg',
                'generic_name'          => 'Sildenafil Citrate',
                'brand_name'            => null,
                'strength'              => '50mg',
                'form'                  => 'tablet',
                'dosage_instructions'   => 'Take one tablet 30–60 minutes before sexual activity. Do not take more than one tablet in 24 hours.',
                'product_type'          => 'POM',
                'price_one_off'         => 24.99,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly (4 tablets)',  'interval' => 'month', 'interval_count' => 1, 'price' => 19.99],
                    ['label' => 'Monthly (8 tablets)',  'interval' => 'month', 'interval_count' => 1, 'price' => 34.99],
                ]),
                'requires_cold_chain'   => false,
                'requires_age_verification' => true,
                'has_questionnaire'     => true,
                'active'                => true,
                'sort_order'            => 1,
            ],
            [
                'treatment_slug'        => 'viagra-connect',
                'name'                  => 'Viagra Connect 50mg Tablets',
                'slug'                  => 'viagra-connect-50mg',
                'generic_name'          => 'Sildenafil',
                'brand_name'            => 'Viagra Connect',
                'strength'              => '50mg',
                'form'                  => 'tablet',
                'dosage_instructions'   => 'Take one tablet approximately 1 hour before sexual activity.',
                'product_type'          => 'P',
                'price_one_off'         => 19.99,
                'subscription_tiers'    => null,
                'requires_cold_chain'   => false,
                'requires_age_verification' => true,
                'has_questionnaire'     => false,
                'active'                => true,
                'sort_order'            => 2,
            ],
            // ── Skin Health ───────────────────────────────────────────────────
            [
                'treatment_slug'        => 'tretinoin',
                'name'                  => 'Tretinoin 0.025% Cream',
                'slug'                  => 'tretinoin-0-025',
                'generic_name'          => 'Tretinoin',
                'brand_name'            => null,
                'strength'              => '0.025%',
                'form'                  => 'cream',
                'dosage_instructions'   => 'Apply a thin layer to affected areas of the face once daily at night.',
                'product_type'          => 'POM',
                'price_one_off'         => 34.99,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly', 'interval' => 'month', 'interval_count' => 1, 'price' => 29.99],
                ]),
                'requires_cold_chain'   => false,
                'requires_age_verification' => false,
                'has_questionnaire'     => true,
                'active'                => true,
                'sort_order'            => 1,
            ],
            // ── Hair Loss ─────────────────────────────────────────────────────
            [
                'treatment_slug'        => 'finasteride',
                'name'                  => 'Finasteride 1mg Tablets',
                'slug'                  => 'finasteride-1mg',
                'generic_name'          => 'Finasteride',
                'brand_name'            => null,
                'strength'              => '1mg',
                'form'                  => 'tablet',
                'dosage_instructions'   => 'Take one tablet once daily. Results may take 3–6 months.',
                'product_type'          => 'POM',
                'price_one_off'         => null,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly',   'interval' => 'month', 'interval_count' => 1, 'price' => 24.99],
                    ['label' => 'Quarterly', 'interval' => 'month', 'interval_count' => 3, 'price' => 64.99],
                ]),
                'requires_cold_chain'   => false,
                'requires_age_verification' => false,
                'has_questionnaire'     => true,
                'active'                => true,
                'sort_order'            => 1,
            ],
            // ── Digestive Health ──────────────────────────────────────────────
            [
                'treatment_slug'        => 'omeprazole',
                'name'                  => 'Omeprazole 20mg Capsules',
                'slug'                  => 'omeprazole-20mg',
                'generic_name'          => 'Omeprazole',
                'brand_name'            => null,
                'strength'              => '20mg',
                'form'                  => 'capsule',
                'dosage_instructions'   => 'Take one capsule daily, 30 minutes before food.',
                'product_type'          => 'POM',
                'price_one_off'         => 14.99,
                'subscription_tiers'    => json_encode([
                    ['label' => 'Monthly', 'interval' => 'month', 'interval_count' => 1, 'price' => 12.99],
                ]),
                'requires_cold_chain'   => false,
                'requires_age_verification' => false,
                'has_questionnaire'     => false,
                'active'                => true,
                'sort_order'            => 1,
            ],
        ];

        foreach ($products as $p) {
            $treatment = DB::table('treatments')->where('slug', $p['treatment_slug'])->first();
            if (!$treatment) continue;

            unset($p['treatment_slug']);

            DB::table('products')->updateOrInsert(
                ['slug' => $p['slug']],
                array_merge($p, [
                    'treatment_id' => $treatment->id,
                    'stock_quantity' => 0,
                    'minimum_stock_level' => 10,
                    'in_stock' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
