<?php

namespace Modules\Nonprofit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $company_id = (int) $this->request->get('company_id', company_id());

        return [
            'code'        => 'required|string|max:50',
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|in:asset,liability,equity,revenue,expense',
            'parent_id'   => 'nullable|exists:chart_of_accounts,id,company_id,' . $company_id,
            'description' => 'nullable|string',
            'enabled'     => 'boolean',
        ];
    }
}
