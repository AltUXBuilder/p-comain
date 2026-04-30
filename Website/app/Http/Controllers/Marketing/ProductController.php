<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TreatmentCategory;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(TreatmentCategory $category, Product $product): View
    {
        $product->load(['treatment.category', 'questionnaires']);

        // Related products in the same category
        $related = Product::whereHas('treatment', fn($q) => $q->where('treatment_category_id', $category->id))
            ->where('id', '!=', $product->id)
            ->where('active', true)
            ->limit(3)
            ->get();

        return view('treatments.show', compact('category', 'product', 'related'));
    }
}
