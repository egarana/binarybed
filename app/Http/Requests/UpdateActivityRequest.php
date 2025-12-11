<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\ValidatesTenantResourceUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateActivityRequest extends FormRequest
{
    use ValidatesTenantResourceUniqueness;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If _features_cleared flag is set, explicitly set features to empty array
        if ($this->has('_features_cleared') && $this->input('_features_cleared') === '1') {
            $this->merge(['features' => []]);
            return;
        }

        // Filter out empty values from features array
        if ($this->has('features')) {
            $features = $this->input('features');
            if (is_array($features)) {
                // Filter out empty strings and null values
                $features = array_values(array_filter($features, function ($value) {
                    return $value !== '' && $value !== null;
                }));
            }
            $this->merge(['features' => $features]);
        }
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
            'features.*' => ['integer', 'exists:features,id'],
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
                    Activity::class,
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
            'name.required' => 'Please enter the activity name',
            'name.string'   => 'The activity name must be valid text',
            'name.min'      => 'The activity name must be at least 8 characters',
            'name.max'      => 'The activity name cannot be longer than 255 characters',

            'slug.required' => 'Please enter the slug',
            'slug.string'   => 'The slug must be valid text',
            'slug.min'      => 'The slug must be at least 3 characters',
            'slug.max'      => 'The slug cannot be longer than 255 characters',
            'slug.regex'    => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., activity-name)',
        ];
    }
}
