<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\ValidatesTenantResourceUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreActivityRequest extends FormRequest
{
    use ValidatesTenantResourceUniqueness;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'name'      => ['required', 'string', 'min:8', 'max:255'],
            'slug'      => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'features'   => ['nullable', 'array'],
            'features.*' => ['required', 'integer', 'exists:features,id'],
            // Support for immediate upload (new way)
            'uploaded_media_ids'   => ['nullable', 'array', 'max:25'],
            'uploaded_media_ids.*' => ['required', 'integer', 'exists:temporary_uploads,id'],
            // Legacy support for direct file upload
            'images'     => ['nullable', 'array', 'max:10'],
            'images.*'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
                    Activity::class,
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
            'tenant_id.required' => 'Please select a tenant',
            'tenant_id.string'   => 'Invalid tenant selection',
            'tenant_id.exists'   => 'The selected tenant does not exist',

            'name.required'      => 'Please enter the activity name',
            'name.string'        => 'The activity name must be valid text',
            'name.min'           => 'The activity name must be at least 8 characters',
            'name.max'           => 'The activity name cannot be longer than 255 characters',

            'slug.required'      => 'Please enter the slug',
            'slug.string'        => 'The slug must be valid text',
            'slug.min'           => 'The slug must be at least 3 characters',
            'slug.max'           => 'The slug cannot be longer than 255 characters',
            'slug.regex'         => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., activity-name)',
        ];
    }
}
