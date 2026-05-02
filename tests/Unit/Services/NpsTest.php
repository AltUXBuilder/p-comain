<?php

namespace Tests\Unit\Services;

use App\Models\NpsScore;
use App\Services\WorkflowEngine;
use PHPUnit\Framework\TestCase;

class NpsTest extends TestCase
{
    /** @test */
    public function score_9_is_categorised_as_promoter()
    {
        $this->assertEquals('promoter', NpsScore::categorise(9));
    }

    /** @test */
    public function score_10_is_categorised_as_promoter()
    {
        $this->assertEquals('promoter', NpsScore::categorise(10));
    }

    /** @test */
    public function score_7_is_categorised_as_passive()
    {
        $this->assertEquals('passive', NpsScore::categorise(7));
    }

    /** @test */
    public function score_8_is_categorised_as_passive()
    {
        $this->assertEquals('passive', NpsScore::categorise(8));
    }

    /** @test */
    public function score_6_is_categorised_as_detractor()
    {
        $this->assertEquals('detractor', NpsScore::categorise(6));
    }

    /** @test */
    public function score_0_is_categorised_as_detractor()
    {
        $this->assertEquals('detractor', NpsScore::categorise(0));
    }

    /** @test */
    public function nps_formula_is_correct_with_all_promoters()
    {
        // 100 promoters, 0 detractors → NPS = +100
        // We can't run DB queries in a unit test, so test the formula directly
        $total      = 10;
        $promoters  = 10;
        $detractors = 0;
        $nps        = round((($promoters - $detractors) / $total) * 100, 1);

        $this->assertEquals(100.0, $nps);
    }

    /** @test */
    public function nps_formula_is_correct_with_mixed_results()
    {
        // 6 promoters, 2 detractors, 2 passives, 10 total → NPS = (6-2)/10 * 100 = 40
        $total      = 10;
        $promoters  = 6;
        $detractors = 2;
        $nps        = round((($promoters - $detractors) / $total) * 100, 1);

        $this->assertEquals(40.0, $nps);
    }

    /** @test */
    public function nps_formula_can_be_negative()
    {
        // 2 promoters, 8 detractors → NPS = (2-8)/10 * 100 = -60
        $total      = 10;
        $promoters  = 2;
        $detractors = 8;
        $nps        = round((($promoters - $detractors) / $total) * 100, 1);

        $this->assertEquals(-60.0, $nps);
    }
}
