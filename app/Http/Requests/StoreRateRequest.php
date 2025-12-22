<?php

namespace App\Http\Requests;

use App\Models\Rate;
use App\ValidatesTenantResourceUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRateRequest extends FormRequest
{
    use ValidatesTenantResourceUniqueness;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'     => ['required', 'string', 'exists:tenants,id'],
            'rateable_type' => ['required', 'string', 'in:App\\Models\\Unit,App\\Models\\Activity'],
            'rateable_id'   => ['required', 'numeric', 'min:1'],
            'name'          => ['required', 'string', 'min:3', 'max:255'],
            'slug'          => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
            'price_type'  => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tenantId = $this->input('tenant_id');
            $slug = $this->input('slug');

            if ($tenantId && $slug && !$validator->errors()->has('tenant_id')) {
                $this->validateTenantUniqueness(
                    $validator,
                    Rate::class,
                    $tenantId,
                    'slug',
                    $slug
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            // Tenant
            'tenant_id.required' => 'Please select a product (tenant is required)',
            'tenant_id.string'   => 'Invalid tenant selection',
            'tenant_id.exists'   => 'The selected tenant does not exist',

            // Rateable
            'rateable_type.required' => 'Please select a product',
            'rateable_type.string'   => 'Invalid product type',
            'rateable_type.in'       => 'Product type must be Unit or Activity',

            'rateable_id.required' => 'Please select a product',
            'rateable_id.numeric'  => 'Invalid product selection',
            'rateable_id.min'      => 'Invalid product selection',

            // Name
            'name.required' => 'Please enter the rate name',
            'name.string'   => 'The rate name must be valid text',
            'name.min'      => 'The rate name must be at least 3 characters',
            'name.max'      => 'The rate name cannot be longer than 255 characters',

            // Slug
            'slug.required' => 'Please enter the slug',
            'slug.string'   => 'The slug must be valid text',
            'slug.min'      => 'The slug must be at least 3 characters',
            'slug.max'      => 'The slug cannot be longer than 255 characters',
            'slug.regex'    => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., rate-name)',

            // Description
            'description.string' => 'The description must be valid text',
            'description.max'    => 'The description cannot be longer than 5000 characters',

            // Price
            'price.required' => 'Please enter the price',
            'price.numeric'  => 'The price must be a valid number',
            'price.min'      => 'The price cannot be negative',

            // Currency
            'currency.required' => 'Please enter the currency code',
            'currency.string'   => 'The currency must be valid text',
            'currency.size'     => 'The currency must be exactly 3 characters (e.g., IDR, USD)',

            // Is Active
            'is_active.boolean' => 'The active status must be true or false',
        ];
    }
}
