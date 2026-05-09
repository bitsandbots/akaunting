<?php

namespace Modules\Nonprofit\Services;

use Modules\Nonprofit\Exceptions\DimensionValidationException;

/**
 * Single source of truth for split-allocation rules. Both the FormRequest
 * (T4) and the TransactionDimension saving event (T3) call this so connector
 * writes and import paths cannot drift from the form-layer ruleset.
 *
 * Rules (spec line 289-293):
 *   R1: ≥1 allocation row
 *   R2: percent rows sum to 100 (within 0.01%)
 *   R3: amount rows sum to tx total (within $0.01)
 *   R4: percent and amount cannot mix in one transaction
 *   R5: every row resolves a fund_id (resolver must have run first)
 */
class DimensionAllocationValidator
{
    private const PERCENT_TOLERANCE = 0.01;  // absolute percent points
    private const AMOUNT_TOLERANCE  = 0.01;  // 1 cent

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function validate(array $rows, float $txAmount): void
    {
        if (count($rows) === 0) {
            throw new DimensionValidationException(
                'A transaction must have at least one allocation row.',
                'R1'
            );
        }

        foreach ($rows as $i => $row) {
            if (empty($row['fund_id'])) {
                throw new DimensionValidationException(
                    "Allocation row {$i}: fund is required (resolver default failed).",
                    'R5'
                );
            }
        }

        $hasPercent = false;
        $hasAmount  = false;
        foreach ($rows as $row) {
            if (! empty($row['allocation_percent'])) {
                $hasPercent = true;
            }
            if (! empty($row['allocation_amount'])) {
                $hasAmount = true;
            }
        }

        if ($hasPercent && $hasAmount) {
            throw new DimensionValidationException(
                'A transaction cannot mix percent and amount allocations. Pick one per transaction.',
                'R4'
            );
        }

        // Single row with no explicit allocation = whole-amount, trivially valid.
        if (count($rows) === 1 && ! $hasPercent && ! $hasAmount) {
            return;
        }

        if ($hasPercent) {
            $sum = 0.0;
            foreach ($rows as $row) {
                $sum += (float) ($row['allocation_percent'] ?? 0);
            }
            if (abs($sum - 100.0) > self::PERCENT_TOLERANCE) {
                throw new DimensionValidationException(
                    "Allocation percents must sum to 100. Got {$sum}.",
                    'R2'
                );
            }
        }

        if ($hasAmount) {
            $sum = 0.0;
            foreach ($rows as $row) {
                $sum += (float) ($row['allocation_amount'] ?? 0);
            }
            if (abs($sum - $txAmount) > self::AMOUNT_TOLERANCE) {
                throw new DimensionValidationException(
                    "Allocation amounts must sum to transaction total {$txAmount}. Got {$sum}.",
                    'R3'
                );
            }
        }
    }
}
