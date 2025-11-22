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
        ];
    }
}
