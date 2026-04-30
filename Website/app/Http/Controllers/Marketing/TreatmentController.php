<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\TreatmentCategory;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    public function index(): View
    {
        $categories = TreatmentCategory::active()
            ->with(['treatments' => fn($q) => $q->active()->with('activeProducts')])
            ->get();
        return view('treatments.index', compact('categories'));
    }

    public function category(TreatmentCategory $category): View
    {
        $category->load(['treatments' => fn($q) => $q->active()->with('activeProducts')]);

        // Map slug to blade view prefix
        $viewMap = [
            'weight-loss'          => 'weight-loss',
            'erectile-dysfunction' => 'erectile-dysfunction',
            'skin-health'          => 'skin-health',
            'hair-loss'            => 'hair-loss',
            'digestive-health'     => 'digestive-health',
        ];

        $viewPrefix = $viewMap[$category->slug] ?? $category->slug;

        return view("treatments.{$viewPrefix}.index", compact('category'));
    }
}
