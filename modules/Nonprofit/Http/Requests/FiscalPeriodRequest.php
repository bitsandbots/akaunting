<?php

namespace Modules\Nonprofit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiscalPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $company_id = (int) $this->request->get('company_id', company_id());

        return [
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($company_id) {
                    $this->validateNoOverlap($attribute, $value, $fail, $company_id);
                },
            ],
            'status'     => 'nullable|string|in:open,closed',
        ];
    }

    /**
     * Validate that the period's date range does not overlap with any existing period.
     */
    protected function validateNoOverlap(string $attribute, string $value, callable $fail, int $company_id): void
    {
        $start = $this->input('start_date');
        $period = $this->route('fiscalPeriod');

        $query = \Modules\Nonprofit\Models\FiscalPeriod::where('company_id', $company_id)
            ->where(function ($q) use ($start, $value) {
                // Overlap condition: existing.start_date <= new.end_date AND existing.end_date >= new.start_date
                $q->where('start_date', '<=', $value)
                  ->where('end_date', '>=', $start);
            });

        if ($period) {
            $query->where('id', '!=', $period->id);
        }

        if ($query->exists()) {
            $fail(trans('nonprofit::general.period_overlap_error'));
        }
    }
}
