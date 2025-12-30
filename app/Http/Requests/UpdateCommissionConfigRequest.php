<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commission_type'       => ['nullable', 'string', 'in:percentage,fixed'],
            'commission_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:commission_type,percentage'],
            'commission_fixed'      => ['nullable', 'integer', 'min:0', 'required_if:commission_type,fixed'],
            'currency'              => ['nullable', 'string', 'size:3', 'required_if:commission_type,fixed'],
        ];
    }

    public function messages(): array
    {
        return [
            'commission_type.in'                 => 'Commission type must be either percentage or fixed',
            'commission_percentage.required_if'  => 'Percentage is required when type is percentage',
            'commission_percentage.min'          => 'Percentage must be at least 0',
            'commission_percentage.max'          => 'Percentage cannot exceed 100',
            'commission_fixed.required_if'       => 'Fixed amount is required when type is fixed',
            'commission_fixed.min'               => 'Fixed amount must be at least 0',
        ];
    }
}
