<?php

namespace App\Http\Requests;

use App\Models\Rate;
use App\ValidatesTenantResourceUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRateRequest extends FormRequest
{
    use ValidatesTenantResourceUniqueness;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'string', 'exists:tenants,id'],
            'name'        => ['required', 'string', 'min:3', 'max:255'],
            'slug'        => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
            'is_active'   => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tenantId = $this->route('tenant');
            $currentSlug = $this->route('slug');
            $newSlug = $this->input('slug');

            if ($tenantId && $newSlug && !$validator->errors()->has('slug')) {
                $this->validateTenantUniqueness(
                    $validator,
                    Rate::class,
                    $tenantId,
                    'slug',
                    $newSlug,
                    $currentSlug
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            // Tenant
            'tenant_id.required' => 'Tenant is required',
            'tenant_id.string'   => 'Invalid tenant',
            'tenant_id.exists'   => 'The selected tenant does not exist',

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
