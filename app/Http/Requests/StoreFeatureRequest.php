<?php

namespace App\Http\Requests;

use App\Models\Feature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeatureRequest extends FormRequest
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
            'value' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:features,value'
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string'],
            'category' => ['required', 'string', Rule::in(array_keys(Feature::getCategories()))],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'feature name',
            'value' => 'feature value',
            'description' => 'feature description',
            'icon' => 'feature icon',
            'category' => 'feature category',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the feature name',
            'name.string'   => 'The feature name must be valid text',
            'name.max'      => 'The feature name cannot be longer than 255 characters',

            'value.required' => 'Please enter the value',
            'value.string'   => 'The value must be valid text',
            'value.min'      => 'The value must be at least 3 characters',
            'value.max'      => 'The value cannot be longer than 255 characters',
            'value.regex'    => 'The value must only contain lowercase letters, numbers, and hyphens (e.g., wifi-access)',
            'value.unique'   => 'This value is already in use. Please choose a different value',

            'category.required' => 'Please select a category',
            'category.string'   => 'The category must be valid text',
            'category.in'       => 'Please select a valid category',
        ];
    }
}
