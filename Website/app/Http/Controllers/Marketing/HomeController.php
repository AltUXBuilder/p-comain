<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\TreatmentCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = TreatmentCategory::active()->with('treatments.activeProducts')->get();
        return view('marketing.home', compact('categories'));
    }

    public function about(): View
    {
        return view('marketing.about');
    }

    public function howItWorks(): View
    {
        return view('marketing.how-it-works');
    }

    public function pricing(): View
    {
        $categories = TreatmentCategory::active()
            ->with(['treatments.activeProducts'])
            ->get();
        return view('marketing.pricing', compact('categories'));
    }

    public function faq(): View
    {
        return view('marketing.faq');
    }
}
