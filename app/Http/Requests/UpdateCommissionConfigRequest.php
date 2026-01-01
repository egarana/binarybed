<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Only validate if commission type is fixed
            if ($this->input('commission_type') !== 'fixed') {
                return;
            }

            $commissionFixed = (int) $this->input('commission_fixed', 0);
            if ($commissionFixed <= 0) {
                return;
            }

            // Get tenant and slug from route
            $tenantId = $this->route('tenant');
            $slug = $this->route('slug');

            if (!$tenantId || !$slug) {
                return;
            }

            // Run in tenant context to get lowest rate
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                return;
            }

            $lowestRate = $tenant->run(function () use ($slug) {
                // Try to find as Unit first, then Activity
                $resource = Unit::where('slug', $slug)->first()
                    ?? Activity::where('slug', $slug)->first();

                if (!$resource) {
                    return null;
                }

                // Get the lowest active rate
                return $resource->rates()
                    ->where('is_active', true)
                    ->orderBy('price', 'asc')
                    ->first();
            });

            if (!$lowestRate) {
                return; // No rates found, skip validation
            }

            // Validate that fixed commission doesn't exceed lowest rate
            if ($commissionFixed > $lowestRate->price) {
                $validator->errors()->add(
                    'commission_fixed',
                    "Fixed commission cannot exceed the lowest rate ({$lowestRate->currency} " . number_format($lowestRate->price, 0, ',', '.') . ")"
                );
            }
        });
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
