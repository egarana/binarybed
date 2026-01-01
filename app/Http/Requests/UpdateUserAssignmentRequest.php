<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUserAssignmentRequest extends FormRequest
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
            'role' => [
                'sometimes',
                'string',
                'in:partner,referrer,platform',
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
            $newCommissionSplit = $this->input('commission_split');
            if ($newCommissionSplit === null) {
                return; // Skip validation if commission split not being updated
            }

            $newCommissionSplit = (float) $newCommissionSplit;
            $tenantId = $this->route('tenant');
            $slug = $this->route('slug');
            $userGlobalId = $this->route('user');

            if (!$tenantId || !$slug || !$userGlobalId) {
                return;
            }

            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                return;
            }

            $currentTotal = $tenant->run(function () use ($slug, $userGlobalId) {
                // Try Unit first, then Activity
                $resource = Unit::where('slug', $slug)->first()
                    ?? Activity::where('slug', $slug)->first();

                if (!$resource) {
                    return 0;
                }

                // Sum all existing commission splits EXCEPT the user being edited
                return $resource->users()
                    ->where('users.global_id', '!=', $userGlobalId)
                    ->sum('commission_split') ?? 0;
            });

            $total = $currentTotal + $newCommissionSplit;
            if ($total > 100) {
                $remaining = max(0, 100 - $currentTotal);
                $validator->errors()->add(
                    'commission_split',
                    "Total commission split cannot exceed 100%. Other users total: {$currentTotal}%. Maximum for this user: {$remaining}%."
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
            'role.required' => 'Please select a role.',
            'role.in' => 'The role must be either partner, referrer, or platform.',
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
            'role' => 'role',
        ];
    }
}
