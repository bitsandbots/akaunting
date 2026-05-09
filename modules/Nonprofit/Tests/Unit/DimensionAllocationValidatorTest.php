<?php

namespace Modules\Nonprofit\Tests\Unit;

use Modules\Nonprofit\Exceptions\DimensionValidationException;
use Modules\Nonprofit\Services\DimensionAllocationValidator;
use Modules\Nonprofit\Tests\TestCase;

class DimensionAllocationValidatorTest extends TestCase
{
    private DimensionAllocationValidator $v;

    protected function setUp(): void
    {
        parent::setUp();
        $this->v = new DimensionAllocationValidator;
    }

    public function test_single_row_with_no_allocation_passes(): void
    {
        $rows = [['fund_id' => 1, 'program_id' => null, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => null]];
        $this->v->validate($rows, txAmount: 1000.00);
        $this->assertTrue(true);
    }

    public function test_two_rows_summing_to_100_percent_passes(): void
    {
        $rows = [
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 60.0, 'allocation_amount' => null],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 40.0, 'allocation_amount' => null],
        ];
        $this->v->validate($rows, 1000.00);
        $this->assertTrue(true);
    }

    public function test_two_rows_summing_to_99_percent_fails_R2(): void
    {
        $rows = [
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 60.0, 'allocation_amount' => null],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 39.0, 'allocation_amount' => null],
        ];
        $this->expectException(DimensionValidationException::class);
        $this->expectExceptionMessageMatches('/percent.*100/i');
        $this->v->validate($rows, 1000.00);
    }

    public function test_mixed_percent_and_amount_fails_R4(): void
    {
        $rows = [
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 60.0, 'allocation_amount' => null],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => 400.00],
        ];
        $this->expectException(DimensionValidationException::class);
        $this->expectExceptionMessageMatches('/cannot mix percent and amount/i');
        $this->v->validate($rows, 1000.00);
    }

    public function test_amount_rows_summing_to_tx_total_passes(): void
    {
        $rows = [
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => 600.00],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => 400.00],
        ];
        $this->v->validate($rows, 1000.00);
        $this->assertTrue(true);
    }

    public function test_amount_rows_off_by_a_penny_fails_R3(): void
    {
        $rows = [
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => 600.00],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => 399.98],
        ];
        $this->expectException(DimensionValidationException::class);
        $this->expectExceptionMessageMatches('/amounts.*1000/i');
        $this->v->validate($rows, 1000.00);
    }

    public function test_zero_rows_fails_R1(): void
    {
        $this->expectException(DimensionValidationException::class);
        $this->expectExceptionMessageMatches('/at least one allocation row/i');
        $this->v->validate([], 1000.00);
    }

    public function test_missing_fund_id_after_resolution_fails_R5(): void
    {
        $rows = [['fund_id' => null, 'functional_class_id' => 1, 'allocation_percent' => null, 'allocation_amount' => null]];
        $this->expectException(DimensionValidationException::class);
        $this->expectExceptionMessageMatches('/fund.*required/i');
        $this->v->validate($rows, 1000.00);
    }

    public function test_percent_tolerance_accepts_near_100(): void
    {
        $rows = [
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 33.3333, 'allocation_amount' => null],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 33.3333, 'allocation_amount' => null],
            ['fund_id' => 1, 'functional_class_id' => 1, 'allocation_percent' => 33.3334, 'allocation_amount' => null],
        ];
        $this->v->validate($rows, 1000.00);
        $this->assertTrue(true);
    }
}
