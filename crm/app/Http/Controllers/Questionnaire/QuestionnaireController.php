<?php

namespace App\Http\Controllers\Questionnaire;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionnaireController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $questionnaires = Questionnaire::withCount('questions')
            ->with('products')
            ->orderByDesc('updated_at')
            ->get();

        return view('questionnaire.index', compact('questionnaires'));
    }

    // ── Create / Store questionnaire ──────────────────────────────────────────

    public function create()
    {
        $products = Product::where('active', true)->orderBy('name')->get();
        return view('questionnaire.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'active'      => ['boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        $questionnaire = Questionnaire::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active'      => $validated['active'] ?? false,
        ]);

        if (! empty($validated['product_ids'])) {
            $questionnaire->products()->sync($validated['product_ids']);
        }

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'questionnaire_created',
            entityType: 'questionnaire',
            entityId:   $questionnaire->id,
            metadata:   ['name' => $questionnaire->name],
            request:    $request
        );

        return redirect()->route('questionnaire.show', $questionnaire)
            ->with('success', "Questionnaire '{$questionnaire->name}' created. Add questions below.");
    }

    // ── Show / Edit questionnaire ─────────────────────────────────────────────

    public function show(Questionnaire $questionnaire)
    {
        $questionnaire->load(['questions' => fn ($q) => $q->orderBy('sort_order'), 'products']);
        $allProducts = Product::where('active', true)->orderBy('name')->get();

        return view('questionnaire.show', compact('questionnaire', 'allProducts'));
    }

    public function update(Request $request, Questionnaire $questionnaire)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'active'      => ['boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        $questionnaire->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active'      => $validated['active'] ?? false,
        ]);

        $questionnaire->products()->sync($validated['product_ids'] ?? []);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'questionnaire_updated',
            entityType: 'questionnaire',
            entityId:   $questionnaire->id,
            request:    $request
        );

        return back()->with('success', 'Questionnaire updated.');
    }

    public function destroy(Questionnaire $questionnaire)
    {
        if ($questionnaire->products()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete a questionnaire assigned to products. Detach it from all products first.']);
        }

        $name = $questionnaire->name;
        $questionnaire->delete();

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'questionnaire_deleted',
            entityType: 'questionnaire',
            entityId:   null,
            metadata:   ['name' => $name],
            request:    request()
        );

        return redirect()->route('questionnaire.index')
            ->with('success', "Questionnaire '{$name}' deleted.");
    }

    public function duplicate(Questionnaire $questionnaire)
    {
        $clone = $questionnaire->duplicate();

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'questionnaire_duplicated',
            entityType: 'questionnaire',
            entityId:   $clone->id,
            metadata:   ['original_id' => $questionnaire->id],
            request:    request()
        );

        return redirect()->route('questionnaire.show', $clone)
            ->with('success', "Questionnaire duplicated as '{$clone->name}'.");
    }

    // ── Questions CRUD ────────────────────────────────────────────────────────

    public function storeQuestion(Request $request, Questionnaire $questionnaire)
    {
        $validated = $request->validate([
            'question_text'         => ['required', 'string', 'max:1000'],
            'type'                  => ['required', 'in:' . implode(',', array_keys(Question::TYPES))],
            'options'               => ['nullable', 'array'],
            'options.*'             => ['string', 'max:200'],
            'mandatory'             => ['boolean'],
            'sort_order'            => ['nullable', 'integer'],
            'parent_question_id'    => ['nullable', 'exists:questions,id'],
            'parent_trigger_value'  => ['nullable', 'string', 'max:200'],
            'has_contraindication'  => ['boolean'],
            'contraindication_rules' => ['nullable', 'array'],
            'numeric_min'           => ['nullable', 'numeric'],
            'numeric_max'           => ['nullable', 'numeric'],
            'bmi_min'               => ['nullable', 'numeric'],
            'bmi_max'               => ['nullable', 'numeric'],
            'bmi_action'            => ['nullable', 'in:flag,reject'],
        ]);

        // Auto sort_order if not specified
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = $questionnaire->questions()->max('sort_order') + 10;
        }

        $question = $questionnaire->questions()->create($validated);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'question_added',
            entityType: 'questionnaire',
            entityId:   $questionnaire->id,
            metadata:   ['question_id' => $question->id, 'type' => $question->type],
            request:    $request
        );

        return back()->with('success', 'Question added.');
    }

    public function updateQuestion(Request $request, Questionnaire $questionnaire, Question $question)
    {
        $validated = $request->validate([
            'question_text'          => ['required', 'string', 'max:1000'],
            'type'                   => ['required', 'in:' . implode(',', array_keys(Question::TYPES))],
            'options'                => ['nullable', 'array'],
            'mandatory'              => ['boolean'],
            'sort_order'             => ['nullable', 'integer'],
            'parent_question_id'     => ['nullable', 'exists:questions,id'],
            'parent_trigger_value'   => ['nullable', 'string', 'max:200'],
            'has_contraindication'   => ['boolean'],
            'contraindication_rules' => ['nullable', 'array'],
            'numeric_min'            => ['nullable', 'numeric'],
            'numeric_max'            => ['nullable', 'numeric'],
            'bmi_min'                => ['nullable', 'numeric'],
            'bmi_max'                => ['nullable', 'numeric'],
            'bmi_action'             => ['nullable', 'in:flag,reject'],
        ]);

        $question->update($validated);

        return back()->with('success', 'Question updated.');
    }

    public function destroyQuestion(Questionnaire $questionnaire, Question $question)
    {
        // Check for child questions that branch from this one
        $childCount = Question::where('parent_question_id', $question->id)->count();
        if ($childCount > 0) {
            return back()->withErrors(['error' => "Cannot delete — {$childCount} branching question(s) depend on this question. Delete them first."]);
        }

        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    public function reorderQuestions(Request $request, Questionnaire $questionnaire)
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:questions,id'],
        ]);

        foreach ($request->order as $sortOrder => $questionId) {
            Question::where('id', $questionId)
                ->where('questionnaire_id', $questionnaire->id)
                ->update(['sort_order' => ($sortOrder + 1) * 10]);
        }

        return response()->json(['success' => true]);
    }
}
