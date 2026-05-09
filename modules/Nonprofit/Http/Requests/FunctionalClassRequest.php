<?php

namespace Modules\Nonprofit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FunctionalClassRequest extends FormRequest
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
            'parent_class' => 'required|string|in:program_services,management_general,fundraising',
            'is_system'   => 'boolean',
            'enabled'     => 'boolean',
        ];
    }
}
