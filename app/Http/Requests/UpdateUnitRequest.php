<?php

namespace App\Http\Requests;

use App\Models\Unit;
use App\ValidatesTenantResourceUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUnitRequest extends FormRequest
{
    use ValidatesTenantResourceUniqueness;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'min:8', 'max:255'],
            'slug'      => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
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
                    Unit::class,
                    $tenantId,
                    'slug',
                    $slug,
                    $this->route('unit')?->slug
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the unit name',
            'name.string'   => 'The unit name must be valid text',
            'name.min'      => 'The unit name must be at least 8 characters',
            'name.max'      => 'The unit name cannot be longer than 255 characters',

            'slug.required' => 'Please enter the slug',
            'slug.string'   => 'The slug must be valid text',
            'slug.min'      => 'The slug must be at least 3 characters',
            'slug.max'      => 'The slug cannot be longer than 255 characters',
            'slug.regex'    => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., unit-name)',
        ];
    }
}