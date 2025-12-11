<?php

namespace App\Http\Requests;

use App\Rules\ValidDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Decode resource_routes if it's a JSON string from hidden input
        if ($this->has('resource_routes') && is_string($this->resource_routes)) {
            $decoded = json_decode($this->resource_routes, true);
            $this->merge([
                'resource_routes' => is_array($decoded) ? $decoded : null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenant = $this->route('tenant');
        $currentDomainId = $tenant?->domains->first()?->id;

        return [
            'name'   => ['sometimes', 'string', 'max:255'],
            'domain' => [
                'sometimes',
                'string',
                Rule::unique('domains', 'domain')->ignore($currentDomainId),
                new ValidDomain,
            ],
            'resource_routes' => ['sometimes', 'nullable', 'array'],
            'resource_routes.*' => ['string', 'in:units,activities'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Tenant Name
            'name.string'   => 'The tenant name must be valid text',
            'name.max'      => 'The tenant name cannot be longer than 255 characters',

            // Domain
            'domain.string' => 'The domain must be written in plain text without symbols or spaces',
            'domain.unique' => 'This domain is already assigned to another tenant',
            'domain.regex'  => 'The domain must be a valid domain format (e.g., example.com or subdomain.example.com)',

            // Resource Routes
            'resource_routes.array' => 'Resource routes must be a valid configuration',
            'resource_routes.*.in'  => 'Each resource route must be either "units" or "activities"',
        ];
    }
}
