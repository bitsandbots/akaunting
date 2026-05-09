<?php

namespace Modules\Nonprofit\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Modules\Nonprofit\Events\LedgerSuspensePosted;
use Modules\Nonprofit\Models\ChartOfAccount;
use Modules\Nonprofit\Services\LedgerService;
use Modules\Nonprofit\Tests\TestCase;

class SuspenseEventTest extends TestCase
{
    private const COMPANY_ID = 1;

    public function test_suspense_resolution_dispatches_event(): void
    {
        Event::fake([LedgerSuspensePosted::class]);

        $this->seedSuspenseAccount(self::COMPANY_ID);

        $service  = new LedgerService();
        $resolved = $service->resolveAccountMapping(99999 /* unmapped */, self::COMPANY_ID);

        $this->assertGreaterThan(0, $resolved);

        Event::assertDispatched(
            LedgerSuspensePosted::class,
            fn (LedgerSuspensePosted $e) =>
                $e->companyId === self::COMPANY_ID
                && $e->categoryId === 99999
                && $e->suspenseAccountId === $resolved
        );
    }

    public function test_force_suspense_does_not_dispatch_event(): void
    {
        // The counterparty fallback path uses forceSuspense=true and is intentional,
        // not a mapping miss — it must NOT fire LedgerSuspensePosted.
        Event::fake([LedgerSuspensePosted::class]);

        $this->seedSuspenseAccount(self::COMPANY_ID);

        $service = new LedgerService();
        $service->resolveAccountMapping(99999, self::COMPANY_ID, forceSuspense: true);

        Event::assertNotDispatched(LedgerSuspensePosted::class);
    }

    public function test_resolved_mapping_does_not_dispatch_event(): void
    {
        // When a mapping exists, no fallback occurred — no event.
        Event::fake([LedgerSuspensePosted::class]);

        $this->seedSuspenseAccount(self::COMPANY_ID);
        $coa = ChartOfAccount::create([
            'company_id'  => self::COMPANY_ID,
            'code'        => '4000',
            'name'        => 'Test Revenue',
            'type'        => 'revenue',
            'enabled'     => 1,
        ]);
        setting(['nonprofit.account_mappings' => [42 => $coa->id]]);
        setting()->save();

        $service  = new LedgerService();
        $resolved = $service->resolveAccountMapping(42, self::COMPANY_ID);

        $this->assertSame($coa->id, $resolved);
        Event::assertNotDispatched(LedgerSuspensePosted::class);
    }

    private function seedSuspenseAccount(int $companyId): ChartOfAccount
    {
        return ChartOfAccount::firstOrCreate(
            ['company_id' => $companyId, 'code' => '9999'],
            ['name' => 'Suspense', 'type' => 'asset', 'enabled' => 1],
        );
    }
}
