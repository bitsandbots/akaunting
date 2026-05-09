<?php

namespace Modules\Nonprofit\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Trait for models that carry nonprofit tracking dimensions
 * (fund, program, functional class).
 *
 * Apply to models like JournalEntry or Transaction that
 * have associated dimension data via relationships.
 */
trait HasDimensions
{
    /**
     * Get the funds associated with this model.
     *
     * Returns a collection of fund IDs derived from related
     * dimension data (journal entry lines or transaction dimensions).
     */
    public function getDimensionFundIds(): Collection
    {
        return $this->collectDimensionValues('fund_id');
    }

    /**
     * Get the programs associated with this model.
     */
    public function getDimensionProgramIds(): Collection
    {
        return $this->collectDimensionValues('program_id');
    }

    /**
     * Get the functional classes associated with this model.
     */
    public function getDimensionFunctionalClassIds(): Collection
    {
        return $this->collectDimensionValues('functional_class_id');
    }

    /**
     * Check if this model has any dimension data.
     */
    public function hasDimensions(): bool
    {
        return $this->collectDimensionValues('fund_id')
            ->merge($this->collectDimensionValues('program_id'))
            ->merge($this->collectDimensionValues('functional_class_id'))
            ->filter()
            ->isNotEmpty();
    }

    /**
     * Get all dimension values for this model as a keyed array.
     *
     * @return array{funds: array, programs: array, functional_classes: array}
     */
    public function getAllDimensions(): array
    {
        return [
            'funds'               => $this->getDimensionFundIds()->values()->all(),
            'programs'            => $this->getDimensionProgramIds()->values()->all(),
            'functional_classes'  => $this->getDimensionFunctionalClassIds()->values()->all(),
        ];
    }

    /**
     * Collect unique, non-null dimension values from the relevant
     * dimension source.
     *
     * Override this in the consuming model if the dimension data
     * comes from a different relationship than the default.
     */
    protected function collectDimensionValues(string $column): Collection
    {
        // Default: use 'lines' relationship (for JournalEntry)
        if (method_exists($this, 'lines')) {
            return $this->lines->pluck($column)->unique()->filter();
        }

        // Fallback: use 'dimensions' relationship (for Transaction)
        if (method_exists($this, 'dimensions')) {
            return $this->dimensions->pluck($column)->unique()->filter();
        }

        return collect();
    }
}
