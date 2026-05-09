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
        return [
            'code'        => 'required|string|max:50',
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|in:asset,liability,equity,revenue,expense',
            'parent_id'   => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'enabled'     => 'boolean',
        ];
    }
}
