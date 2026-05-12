<?php

namespace Modules\Nonprofit\Tests\Feature;

use Modules\Nonprofit\Exceptions\DimensionValidationException;
use Modules\Nonprofit\Models\TransactionDimension;
use Modules\Nonprofit\Tests\Concerns\CreatesNonprofitFixtures;
use Modules\Nonprofit\Tests\TestCase;

class TransactionDimensionSavingTest extends TestCase
{
    use CreatesNonprofitFixtures;

    public function test_unwrapped_partial_save_is_rejected(): void
    {
        [$company, $tx, $fund, $fc] = $this->makeTransactionFixture(amount: 1000.00);

        $this->expectException(DimensionValidationException::class);
        $this->expectExceptionMessageMatches('/percent.*100/i');

        TransactionDimension::create([
            'company_id'          => $company->id,
            'transaction_id'      => $tx->id,
            'fund_id'             => $fund->id,
            'functional_class_id' => $fc->id,
            'allocation_percent'  => 60.0,
        ]);
    }

    public function test_orchestrated_multi_row_save_balances_to_100(): void
    {
        [$company, $tx, $fund, $fc] = $this->makeTransactionFixture(amount: 1000.00);

        TransactionDimension::$skipValidation = true;
        try {
            TransactionDimension::create([
                'company_id' => $company->id, 'transaction_id' => $tx->id,
                'fund_id'    => $fund->id, 'functional_class_id' => $fc->id,
                'allocation_percent' => 60.0,
            ]);
            $r2 = TransactionDimension::create([
                'company_id' => $company->id, 'transaction_id' => $tx->id,
                'fund_id'    => $fund->id, 'functional_class_id' => $fc->id,
                'allocation_percent' => 40.0,
            ]);
        } finally {
            TransactionDimension::$skipValidation = false;
        }

        // Re-touch the last row outside skipValidation to confirm the
        // final state validates green: full set is now 60 + 40 = 100.
        $r2->touch();

        $this->assertSame(2, TransactionDimension::where('transaction_id', $tx->id)->count());
    }

    public function test_unbalanced_set_rejected_when_final_row_validates(): void
    {
        [$company, $tx, $fund, $fc] = $this->makeTransactionFixture(amount: 1000.00);

        TransactionDimension::$skipValidation = true;
        TransactionDimension::create([
            'company_id' => $company->id, 'transaction_id' => $tx->id,
            'fund_id'    => $fund->id, 'functional_class_id' => $fc->id,
            'allocation_percent' => 60.0,
        ]);
        TransactionDimension::$skipValidation = false;

        $this->expectException(DimensionValidationException::class);

        TransactionDimension::create([
            'company_id' => $company->id, 'transaction_id' => $tx->id,
            'fund_id'    => $fund->id, 'functional_class_id' => $fc->id,
            'allocation_percent' => 39.0,
        ]);
    }

    public function test_single_row_with_no_allocation_passes(): void
    {
        [$company, $tx, $fund, $fc] = $this->makeTransactionFixture(amount: 1000.00);

        $row = TransactionDimension::create([
            'company_id'          => $company->id,
            'transaction_id'      => $tx->id,
            'fund_id'             => $fund->id,
            'functional_class_id' => $fc->id,
        ]);

        $this->assertNotNull($row->id);
    }
}
