<?php

namespace Tests\Unit\Services;

use App\Models\Question;
use PHPUnit\Framework\TestCase;

class QuestionContraindicationTest extends TestCase
{
    /** @test */
    public function yes_no_question_triggers_on_yes_answer()
    {
        $question = new Question([
            'type'                 => Question::TYPE_YES_NO,
            'has_contraindication' => true,
            'contraindication_rules' => [
                ['trigger_value' => 'yes', 'action' => 'reject', 'message' => 'Contraindicated.'],
            ],
        ]);

        $result = $question->evaluateAnswer('yes');

        $this->assertNotNull($result);
        $this->assertEquals('reject', $result['action']);
    }

    /** @test */
    public function yes_no_question_does_not_trigger_on_no_answer()
    {
        $question = new Question([
            'type'                 => Question::TYPE_YES_NO,
            'has_contraindication' => true,
            'contraindication_rules' => [
                ['trigger_value' => 'yes', 'action' => 'reject', 'message' => 'Contraindicated.'],
            ],
        ]);

        $this->assertNull($question->evaluateAnswer('no'));
    }

    /** @test */
    public function numeric_question_triggers_on_greater_than_operator()
    {
        $question = new Question([
            'type'                 => Question::TYPE_NUMERIC,
            'has_contraindication' => true,
            'contraindication_rules' => [
                ['trigger_value' => '>60', 'action' => 'flag', 'message' => 'Age over 60.'],
            ],
        ]);

        $this->assertNotNull($question->evaluateAnswer('65'));
        $this->assertNull($question->evaluateAnswer('55'));
    }

    /** @test */
    public function numeric_question_triggers_on_less_than_operator()
    {
        $question = new Question([
            'type'                 => Question::TYPE_NUMERIC,
            'has_contraindication' => true,
            'contraindication_rules' => [
                ['trigger_value' => '<18', 'action' => 'reject', 'message' => 'Under 18.'],
            ],
        ]);

        $this->assertNotNull($question->evaluateAnswer('16'));
        $this->assertNull($question->evaluateAnswer('20'));
    }

    /** @test */
    public function bmi_evaluator_flags_below_minimum()
    {
        $question = new Question([
            'type'       => Question::TYPE_BMI,
            'bmi_min'    => 18.5,
            'bmi_max'    => 40.0,
            'bmi_action' => 'flag',
        ]);

        $this->assertNotNull($question->evaluateBmi(17.0));
        $this->assertNull($question->evaluateBmi(25.0));
    }

    /** @test */
    public function bmi_evaluator_flags_above_maximum()
    {
        $question = new Question([
            'type'       => Question::TYPE_BMI,
            'bmi_min'    => 18.5,
            'bmi_max'    => 40.0,
            'bmi_action' => 'reject',
        ]);

        $result = $question->evaluateBmi(42.0);

        $this->assertNotNull($result);
        $this->assertEquals('reject', $result['action']);
    }

    /** @test */
    public function bmi_evaluator_returns_null_within_range()
    {
        $question = new Question([
            'type'       => Question::TYPE_BMI,
            'bmi_min'    => 18.5,
            'bmi_max'    => 40.0,
            'bmi_action' => 'flag',
        ]);

        $this->assertNull($question->evaluateBmi(27.5));
    }

    /** @test */
    public function multi_select_question_triggers_on_matching_value()
    {
        $question = new Question([
            'type'                 => Question::TYPE_MC_MULTI,
            'has_contraindication' => true,
            'contraindication_rules' => [
                ['trigger_value' => 'heart_disease', 'action' => 'reject', 'message' => 'Cardiac history.'],
            ],
        ]);

        $this->assertNotNull($question->evaluateAnswer(['diabetes', 'heart_disease']));
        $this->assertNull($question->evaluateAnswer(['diabetes', 'hypertension']));
    }

    /** @test */
    public function question_with_no_contraindications_always_returns_null()
    {
        $question = new Question([
            'type'                 => Question::TYPE_YES_NO,
            'has_contraindication' => false,
            'contraindication_rules' => [],
        ]);

        $this->assertNull($question->evaluateAnswer('yes'));
    }

    /** @test */
    public function branching_question_shows_when_parent_answer_matches()
    {
        $question = new Question([
            'parent_question_id'    => 1,
            'parent_trigger_value'  => 'yes',
        ]);

        $this->assertTrue($question->shouldShowForAnswer('yes'));
        $this->assertFalse($question->shouldShowForAnswer('no'));
    }
}
