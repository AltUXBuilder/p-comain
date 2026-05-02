<?php

namespace Tests\Unit\Services;

use App\Models\WorkflowRule;
use App\Services\WorkflowEngine;
use PHPUnit\Framework\TestCase;
use Mockery;

class WorkflowEngineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function condition_with_no_rules_always_passes()
    {
        $engine  = new WorkflowEngine();
        $context = (object) ['status' => 'approved'];

        // Use reflection to call private method
        $reflection = new \ReflectionClass($engine);
        $method     = $reflection->getMethod('evaluateConditions');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($engine, [], $context, []));
    }

    /** @test */
    public function equals_condition_matches_exact_value()
    {
        $engine  = new WorkflowEngine();
        $context = (object) ['status' => 'approved'];

        $reflection = new \ReflectionClass($engine);
        $method     = $reflection->getMethod('evaluateCondition');
        $method->setAccessible(true);

        $result = $method->invoke($engine,
            ['field' => 'status', 'operator' => 'equals', 'value' => 'approved'],
            $context,
            []
        );

        $this->assertTrue($result);
    }

    /** @test */
    public function equals_condition_fails_on_mismatch()
    {
        $engine  = new WorkflowEngine();
        $context = (object) ['status' => 'rejected'];

        $reflection = new \ReflectionClass($engine);
        $method     = $reflection->getMethod('evaluateCondition');
        $method->setAccessible(true);

        $result = $method->invoke($engine,
            ['field' => 'status', 'operator' => 'equals', 'value' => 'approved'],
            $context,
            []
        );

        $this->assertFalse($result);
    }

    /** @test */
    public function is_true_condition_evaluates_boolean()
    {
        $engine  = new WorkflowEngine();
        $context = (object) ['requires_cold_chain' => true];

        $reflection = new \ReflectionClass($engine);
        $method     = $reflection->getMethod('evaluateCondition');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($engine,
            ['field' => 'requires_cold_chain', 'operator' => 'is_true', 'value' => null],
            $context, []
        ));

        $context->requires_cold_chain = false;

        $this->assertFalse($method->invoke($engine,
            ['field' => 'requires_cold_chain', 'operator' => 'is_true', 'value' => null],
            $context, []
        ));
    }

    /** @test */
    public function all_conditions_must_pass_for_and_logic()
    {
        $engine  = new WorkflowEngine();
        $context = (object) ['status' => 'approved', 'requires_cold_chain' => false];

        $reflection = new \ReflectionClass($engine);
        $method     = $reflection->getMethod('evaluateConditions');
        $method->setAccessible(true);

        // Both match
        $this->assertTrue($method->invoke($engine, [
            ['field' => 'status',              'operator' => 'equals',  'value' => 'approved'],
            ['field' => 'requires_cold_chain', 'operator' => 'is_false', 'value' => null],
        ], $context, []));

        // One fails
        $this->assertFalse($method->invoke($engine, [
            ['field' => 'status',              'operator' => 'equals',  'value' => 'approved'],
            ['field' => 'requires_cold_chain', 'operator' => 'is_true', 'value' => null],
        ], $context, []));
    }

    /** @test */
    public function dot_notation_resolves_nested_property()
    {
        $engine  = new WorkflowEngine();
        $patient = (object) ['risk_flagged' => true];
        $context = (object) ['patient' => $patient];

        $reflection = new \ReflectionClass($engine);
        $method     = $reflection->getMethod('resolveField');
        $method->setAccessible(true);

        $result = $method->invoke($engine, 'patient.risk_flagged', $context, []);

        $this->assertTrue($result);
    }
}
