<?php

namespace Modules\Nonprofit\Tests\Concerns;

use App\Models\Banking\Transaction;
use Modules\Nonprofit\Models\Fund;
use Modules\Nonprofit\Models\FunctionalClass;

/**
 * Bootstraps the minimum nonprofit chart needed by Phase 2+ tests:
 * one Fund (without_donor_restrictions), one FunctionalClass
 * (program_services), and a Transaction belonging to the test company.
 *
 * Transaction is built via the core factory so we inherit the company's
 * seeded category/account wiring. The PostToLedger listener fires on the
 * explicit TransactionCreated event (CreateTransaction job), not on
 * Eloquent's `created`, so factory writes do not auto-post to the
 * ledger — keeps this fixture cheap.
 */
trait CreatesNonprofitFixtures
{
    /**
     * @return array{0: \App\Models\Common\Company, 1: Transaction, 2: Fund, 3: FunctionalClass}
     */
    protected function makeTransactionFixture(float $amount = 1000.00): array
    {
        $company = $this->company;

        $fund = Fund::create([
            'company_id' => $company->id,
            'code'       => 'GEN',
            'name'       => 'General Fund',
            'type'       => 'without_donor_restrictions',
            'enabled'    => true,
        ]);

        $fc = FunctionalClass::create([
            'company_id'   => $company->id,
            'code'         => 'PROG',
            'name'         => 'Program Services',
            'parent_class' => 'program_services',
            'is_system'    => false,
            'enabled'      => true,
        ]);

        $tx = Transaction::factory()->income()->create([
            'company_id' => $company->id,
            'amount'     => $amount,
        ]);

        return [$company, $tx, $fund, $fc];
    }
}
