<?php

namespace Modules\Nonprofit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $company_id = (int) $this->request->get('company_id', company_id());

        return [
            'code'               => 'required|string|max:50',
            'name'               => 'required|string|max:255',
            'type'               => 'required|string|in:without_donor_restrictions,with_donor_restrictions',
            'restriction_detail'  => 'nullable|string',
            'description'        => 'nullable|string',
            'parent_id'          => 'nullable|exists:funds,id,company_id,' . $company_id,
            'enabled'            => 'boolean',
        ];
    }
}
