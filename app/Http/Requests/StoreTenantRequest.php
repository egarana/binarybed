<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
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
            'id'     => ['required', 'string', 'max:20', 'unique:tenants,id'],
            'name'   => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'unique:domains,domain'],
        ];
    }

    public function messages(): array
    {
        return [
            // Tenant ID
            'id.required' => 'Please enter a unique Tenant ID',
            'id.string'   => 'The Tenant ID must be a valid text (letters and numbers only)',
            'id.max'      => 'The Tenant ID must not exceed 20 characters',
            'id.unique'   => 'This Tenant ID is already in use. Please choose another one',

            // Tenant Name
            'name.required' => 'Please enter the tenant’s full name',
            'name.string'   => 'The tenant name must be valid text',
            'name.max'      => 'The tenant name cannot be longer than 255 characters',

            // Domain
            'domain.required' => 'Please provide a domain name',
            'domain.string'   => 'The domain must be written in plain text without symbols or spaces',
            'domain.unique'   => 'This domain is already assigned to another tenant',
        ];
    }
}
