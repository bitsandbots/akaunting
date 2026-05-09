<?php

namespace Modules\Nonprofit\Services;

use Modules\Nonprofit\Enums\FiscalPeriodStatus;
use Modules\Nonprofit\Exceptions\PeriodClosedException;
use Modules\Nonprofit\Models\FiscalPeriod;

/**
 * Guards operations against closed fiscal periods.
 *
 * All double-entry postings must fall within an open fiscal period.
 */
class PeriodGuard
{
    /**
     * Assert that the given entry date falls within an open fiscal period.
     *
     * @param int $companyId
     * @param string $entryDate Y-m-d format.
     *
     * @return void
     *
     * @throws PeriodClosedException When no open period covers the given date.
     */
    public function assertOpen(int $companyId, string $entryDate): void
    {
        $found = FiscalPeriod::where('company_id', $companyId)
            ->where('status', FiscalPeriodStatus::Open->value)
            ->whereDate('start_date', '<=', $entryDate)
            ->whereDate('end_date', '>=', $entryDate)
            ->exists();

        if (! $found) {
            throw new PeriodClosedException($entryDate, $companyId);
        }
    }

    /**
     * Close a fiscal period, preventing further postings within its date range.
     *
     * @param int $periodId
     * @param int $closedByUserId
     *
     * @return void
     */
    public function close(int $periodId, int $closedByUserId): void
    {
        $period = FiscalPeriod::findOrFail($periodId);

        if ($period->status !== FiscalPeriodStatus::Open->value) {
            throw new \RuntimeException(
                sprintf('Fiscal period "%s" is already closed.', $period->name)
            );
        }

        $period->update([
            'status'    => FiscalPeriodStatus::Closed->value,
            'closed_at' => now(),
            'closed_by' => $closedByUserId,
        ]);
    }
}
