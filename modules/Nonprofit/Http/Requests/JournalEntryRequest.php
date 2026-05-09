<?php

namespace Modules\Nonprofit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $company_id = (int) $this->request->get('company_id', company_id());

        return [
            'entry_date'   => 'required|date',
            'description'  => 'nullable|string',
            'reference'    => 'nullable|string|max:255',
            'currency_code' => 'required|string|max:3',
            'currency_rate' => 'nullable|numeric|min:0',
            'lines'        => 'required|array|min:2',
            'lines.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id,company_id,' . $company_id,
            'lines.*.debit_amount'        => 'nullable|numeric|min:0',
            'lines.*.credit_amount'       => 'nullable|numeric|min:0',
            'lines.*.fund_id'             => 'nullable|exists:funds,id,company_id,' . $company_id,
            'lines.*.program_id'          => 'nullable|exists:programs,id,company_id,' . $company_id,
            'lines.*.functional_class_id' => 'nullable|exists:functional_classes,id,company_id,' . $company_id,
            'lines.*.description'         => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required'   => trans('nonprofit::general.min_two_lines'),
            'lines.min'        => trans('nonprofit::general.min_two_lines'),
            'lines.*.chart_of_account_id.required' => trans('validation.required', ['attribute' => trans('nonprofit::general.account')]),
        ];
    }
}
