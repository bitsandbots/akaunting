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
        return [
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'nullable|string|in:open,closed',
        ];
    }
}
