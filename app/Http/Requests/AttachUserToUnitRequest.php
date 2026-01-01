<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttachUserToUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'string',
            ],
            'role' => [
                'required',
                'string',
                'in:partner,referrer',
            ],
            'commission_split' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $newCommissionSplit = (float) $this->input('commission_split', 0);
            if ($newCommissionSplit <= 0) {
                return; // Skip validation if no commission split provided
            }

            $tenantId = $this->route('tenant');
            $slug = $this->route('slug');

            if (!$tenantId || !$slug) {
                return;
            }

            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                return;
            }

            $currentTotal = $tenant->run(function () use ($slug) {
                // Try Unit first, then Activity
                $resource = Unit::where('slug', $slug)->first()
                    ?? Activity::where('slug', $slug)->first();

                if (!$resource) {
                    return 0;
                }

                // Sum all existing commission splits
                return $resource->users()->sum('commission_split') ?? 0;
            });

            $total = $currentTotal + $newCommissionSplit;
            if ($total > 100) {
                $remaining = max(0, 100 - $currentTotal);
                $validator->errors()->add(
                    'commission_split',
                    "Total commission split cannot exceed 100%. Current total: {$currentTotal}%. Maximum allowed: {$remaining}%."
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user to assign.',
            'user_id.integer' => 'The user ID must be a valid number.',
            'user_id.exists' => 'The selected user does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
        ];
    }
}
