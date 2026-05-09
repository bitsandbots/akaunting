<?php

namespace Modules\Nonprofit\Tests\Feature;

use Modules\Nonprofit\Tests\TestCase;

/**
 * Phase 1 acceptance gate (spec lines 653-660).
 *
 * Six scenarios that must all be green before Phase 1 alignment is
 * declared shippable. Each gate is scaffolded with a markTestIncomplete()
 * pointing at the fixture or upstream task that has to land first.
 *
 * The file itself exists now so the gate is one phpunit --filter away once
 * each dependency is in place; CI failure lists the gate as incomplete
 * rather than missing.
 */
class Phase1GateTest extends TestCase
{
    public function test_gate_1_create_500_usd_income_balanced_je_posted(): void
    {
        $this->markTestIncomplete(
            'Needs makeMappedTransaction() helper that bootstraps CoA, '
            . 'CategoryAccountMapping, default fund, and an open fiscal period. '
            . 'Build during Phase 2 task 1 (transaction form fixtures).'
        );
    }

    public function test_gate_2_update_amount_reverses_and_reposts(): void
    {
        $this->markTestIncomplete('Depends on gate 1 fixture.');
    }

    public function test_gate_3_delete_transaction_reverses_je(): void
    {
        $this->markTestIncomplete('Depends on gate 1 fixture.');
    }

    public function test_gate_4_eur_500_expense_records_base_and_foreign(): void
    {
        $this->markTestIncomplete(
            'Requires currency rate fixture (A6 path) plus gate 1 helper.'
        );
    }

    public function test_gate_5_post_in_closed_period_rejected(): void
    {
        $this->markTestIncomplete(
            'Requires PeriodGuard wiring through the auto-post path; verify '
            . 'after Phase 2 dimension validator lands.'
        );
    }

    public function test_gate_6_unmapped_category_posts_to_suspense_and_emits_event(): void
    {
        $this->markTestIncomplete(
            'SuspenseEventTest already covers the event emission unit-side. '
            . 'Promote that to a feature-level assertion once gate 1 fixture exists.'
        );
    }
}
