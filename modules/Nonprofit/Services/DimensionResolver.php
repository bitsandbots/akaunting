<?php

namespace Modules\Nonprofit\Services;

use Modules\Nonprofit\Enums\FunctionalClassParentClass;
use Modules\Nonprofit\Models\Fund;
use Modules\Nonprofit\Models\FunctionalClass;

/**
 * Resolves missing dimension references on journal entry lines.
 *
 * Every line MUST have fund_id and functional_class_id. If the caller
 * does not provide them, this resolver fills in sensible defaults:
 *
 * | Dimension        | Source                          | Default                  |
 * |------------------|---------------------------------|--------------------------|
 * | Fund             | Provided fund_id                | Unrestricted Operating   |
 * | Program          | Provided program_id             | null                     |
 * | Functional class | Provided functional_class_id    | management_general       |
 */
class DimensionResolver
{
    /**
     * Resolve dimensions for a single journal entry line.
     *
     * Accepts a raw line array and returns a normalized version
     * with fund_id and functional_class_id guaranteed present.
     *
     * @param array $line Raw line data from the caller.
     * @param int $companyId
     *
     * @return array Normalized line with dimensions filled in.
     */
    public function resolve(array $line, int $companyId): array
    {
        // Fund resolution
        if (empty($line['fund_id'])) {
            $line['fund_id'] = $this->getDefaultFundId($companyId);
        }

        // Program is always optional — keep whatever was provided (or null).
        if (! array_key_exists('program_id', $line)) {
            $line['program_id'] = null;
        }

        // Functional class resolution
        if (empty($line['functional_class_id'])) {
            $line['functional_class_id'] = $this->getDefaultFunctionalClassId($companyId);
        }

        return $line;
    }

    /**
     * Find the default unrestricted fund for a company.
     *
     * Falls back to the first enabled fund if the well-known code is absent.
     *
     * @param int $companyId
     *
     * @return int Fund ID.
     *
     * @throws \RuntimeException When no enabled fund exists for the company.
     */
    protected function getDefaultFundId(int $companyId): int
    {
        $fund = Fund::where('company_id', $companyId)
            ->where('code', 'UNRESTRICTED')
            ->where('enabled', true)
            ->first();

        if ($fund) {
            return $fund->id;
        }

        // Fallback: any enabled fund.
        $fallback = Fund::where('company_id', $companyId)
            ->where('enabled', true)
            ->first();

        if (! $fallback) {
            throw new \RuntimeException(
                "No enabled fund exists for company {$companyId}. " .
                'Seed the SystemFundSeeder first.'
            );
        }

        return $fallback->id;
    }

    /**
     * Find the default functional class (management_general) for a company.
     *
     * Falls back to the first enabled functional class if the well-known code is absent.
     *
     * @param int $companyId
     *
     * @return int Functional class ID.
     *
     * @throws \RuntimeException When no enabled functional class exists for the company.
     */
    protected function getDefaultFunctionalClassId(int $companyId): int
    {
        $funcClass = FunctionalClass::where('company_id', $companyId)
            ->where('code', 'MG')
            ->where('enabled', true)
            ->first();

        if ($funcClass) {
            return $funcClass->id;
        }

        // Fallback: any enabled functional class.
        $fallback = FunctionalClass::where('company_id', $companyId)
            ->where('enabled', true)
            ->first();

        if (! $fallback) {
            throw new \RuntimeException(
                "No enabled functional class exists for company {$companyId}. " .
                'Seed the FunctionalClassSeeder first.'
            );
        }

        return $fallback->id;
    }
}
