<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('units')->ignore($this->route('unit')),
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
            'name.required' => 'Please enter the agent name',
            'name.string'   => 'The agent name must be valid text',
            'name.max'      => 'The agent name cannot be longer than 255 characters',

            'slug.required' => 'Please enter the slug',
            'slug.string'   => 'The slug must be valid text',
            'slug.min'      => 'The slug must be at least 3 characters',
            'slug.max'      => 'The slug cannot be longer than 255 characters',
            'slug.regex'    => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., unit-name)',
            'slug.unique'   => 'This slug is already in use',
        ];
    }
}
