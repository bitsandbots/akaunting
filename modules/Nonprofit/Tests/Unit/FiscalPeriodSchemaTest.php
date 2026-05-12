<?php

namespace Modules\Nonprofit\Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Modules\Nonprofit\Models\FiscalPeriod;
use Modules\Nonprofit\Tests\TestCase;

class FiscalPeriodSchemaTest extends TestCase
{
    public function test_fiscal_periods_has_no_deleted_at_column(): void
    {
        $this->assertFalse(
            Schema::hasColumn('fiscal_periods', 'deleted_at'),
            'fiscal_periods must not have soft deletes — closed periods are append-only audit objects.'
        );
    }

    public function test_fiscal_period_deletion_throws(): void
    {
        $period = FiscalPeriod::create([
            'company_id' => 1,
            'name'       => '2026-Q1',
            'start_date' => '2026-01-01',
            'end_date'   => '2026-03-31',
            'status'     => 'open',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/append-only/');
        $period->delete();
    }
}
