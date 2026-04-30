<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    // In production, blog posts would come from a CMS table or headless CMS.
    // For now we serve static placeholder views.

    public function index(): View
    {
        $posts = collect([
            ['slug' => 'what-is-mounjaro',       'title' => 'What is Mounjaro and how does it work?',             'category' => 'Weight Loss',  'date' => '12 April 2025', 'read_time' => '5 min read', 'excerpt' => 'Tirzepatide — the active ingredient in Mounjaro — is a dual GIP and GLP-1 receptor agonist. Here\'s what that means for weight loss.'],
            ['slug' => 'ed-treatment-options',    'title' => 'Understanding your ED treatment options',            'category' => 'Sexual Health', 'date' => '8 April 2025',  'read_time' => '4 min read', 'excerpt' => 'Sildenafil, tadalafil, and Viagra Connect — we explain the differences so you can make an informed choice.'],
            ['slug' => 'tretinoin-beginners-guide','title' => 'A beginner\'s guide to tretinoin',                 'category' => 'Skin Health',   'date' => '1 April 2025',  'read_time' => '6 min read', 'excerpt' => 'Tretinoin is one of the most studied skincare actives in existence. Here\'s everything you need to know before starting.'],
            ['slug' => 'finasteride-hair-loss',   'title' => 'Does finasteride really stop hair loss?',           'category' => 'Hair Loss',     'date' => '22 March 2025', 'read_time' => '5 min read', 'excerpt' => 'Clinical evidence, realistic expectations, and what to consider before starting finasteride for male pattern hair loss.'],
            ['slug' => 'ibs-management-tips',     'title' => '7 evidence-based tips for managing IBS',            'category' => 'Digestive Health','date' => '15 March 2025','read_time' => '7 min read', 'excerpt' => 'From dietary changes to prescription medication — a pharmacist\'s guide to living better with IBS.'],
            ['slug' => 'online-pharmacy-safety',  'title' => 'How to safely use an online pharmacy in the UK',    'category' => 'Advice',        'date' => '5 March 2025',  'read_time' => '4 min read', 'excerpt' => 'What to look for, what to avoid, and why GPhC registration matters when buying prescription medicine online.'],
        ]);

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug): View
    {
        return view('blog.show', compact('slug'));
    }
}
