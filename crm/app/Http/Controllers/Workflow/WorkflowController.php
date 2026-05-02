<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\WorkflowRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function index()
    {
        $rules = WorkflowRule::with('creator')
            ->orderBy('sort_order')
            ->orderBy('trigger_event')
            ->get()
            ->groupBy('trigger_event');

        return view('workflow.index', [
            'rules'    => $rules,
            'triggers' => WorkflowRule::TRIGGERS,
        ]);
    }

    public function create()
    {
        return view('workflow.create', [
            'triggers'    => WorkflowRule::TRIGGERS,
            'fields'      => WorkflowRule::CONDITION_FIELDS,
            'operators'   => WorkflowRule::CONDITION_OPERATORS,
            'actionTypes' => WorkflowRule::ACTION_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:200'],
            'description'   => ['nullable', 'string', 'max:500'],
            'trigger_event' => ['required', 'string', 'in:' . implode(',', array_keys(WorkflowRule::TRIGGERS))],
            'conditions'    => ['nullable', 'array'],
            'actions'       => ['required', 'array', 'min:1'],
            'active'        => ['boolean'],
            'sort_order'    => ['nullable', 'integer'],
        ]);

        $rule = WorkflowRule::create([
            ...$validated,
            'is_system'  => false,
            'created_by' => Auth::guard('staff')->id(),
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'workflow_rule_created',
            entityType: 'workflow_rule',
            entityId:   $rule->id,
            metadata:   ['name' => $rule->name, 'trigger' => $rule->trigger_event],
            request:    $request
        );

        return redirect()->route('workflow.index')
            ->with('success', "Rule '{$rule->name}' created.");
    }

    public function edit(WorkflowRule $workflowRule)
    {
        if ($workflowRule->is_system) {
            // Allow editing system rules (enable/disable, description) but not trigger/actions
        }
        return view('workflow.edit', [
            'rule'        => $workflowRule,
            'triggers'    => WorkflowRule::TRIGGERS,
            'fields'      => WorkflowRule::CONDITION_FIELDS,
            'operators'   => WorkflowRule::CONDITION_OPERATORS,
            'actionTypes' => WorkflowRule::ACTION_TYPES,
        ]);
    }

    public function update(Request $request, WorkflowRule $workflowRule)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'conditions'  => ['nullable', 'array'],
            'actions'     => ['required', 'array', 'min:1'],
            'active'      => ['boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        // System rules cannot have their trigger changed
        if (! $workflowRule->is_system) {
            $validated['trigger_event'] = $request->validate([
                'trigger_event' => ['required', 'string', 'in:' . implode(',', array_keys(WorkflowRule::TRIGGERS))],
            ])['trigger_event'];
        }

        $workflowRule->update([
            ...$validated,
            'updated_by' => Auth::guard('staff')->id(),
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'workflow_rule_updated',
            entityType: 'workflow_rule',
            entityId:   $workflowRule->id,
            metadata:   ['name' => $workflowRule->name],
            request:    $request
        );

        return redirect()->route('workflow.index')
            ->with('success', "Rule '{$workflowRule->name}' updated.");
    }

    public function toggleActive(WorkflowRule $workflowRule)
    {
        $workflowRule->update(['active' => ! $workflowRule->active]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     $workflowRule->active ? 'workflow_rule_enabled' : 'workflow_rule_disabled',
            entityType: 'workflow_rule',
            entityId:   $workflowRule->id,
            request:    request()
        );

        return back()->with('success', "Rule " . ($workflowRule->active ? 'enabled' : 'disabled') . ".");
    }

    public function destroy(WorkflowRule $workflowRule)
    {
        if ($workflowRule->is_system) {
            return back()->withErrors(['error' => 'System rules cannot be deleted. You can disable them instead.']);
        }

        $name = $workflowRule->name;
        $workflowRule->delete();

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'workflow_rule_deleted',
            entityType: 'workflow_rule',
            entityId:   null,
            metadata:   ['name' => $name],
            request:    request()
        );

        return redirect()->route('workflow.index')->with('success', "Rule '{$name}' deleted.");
    }
}
