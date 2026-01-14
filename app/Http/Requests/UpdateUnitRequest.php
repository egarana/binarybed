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
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'name'      => ['required', 'string', 'min:8', 'max:255'],
            'slug'      => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'description' => ['nullable', 'string', 'max:65535'],
            'subtitle'    => ['nullable', 'string', 'max:255'],
            'max_guests'     => ['required', 'integer', 'min:1', 'max:50'],
            'bedroom_count'  => ['required', 'integer', 'min:0', 'max:20'],
            'bathroom_count' => ['required', 'integer', 'min:0', 'max:20'],
            'view'           => ['required', 'string', 'max:255'],


            'existing_images'   => ['nullable', 'array'],
            'existing_images.*' => ['integer'],
            // Support for immediate upload (new way)
            'uploaded_media_ids'   => ['nullable', 'array', 'max:25'],
            'uploaded_media_ids.*' => ['integer', 'exists:temporary_uploads,id'],
            // Legacy support for direct file upload
            'new_images'        => ['nullable', 'array', 'max:10'],
            'new_images.*'      => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],

            // Selling Points
            'selling_points' => ['nullable', 'array'],
            'selling_points.*.icon' => ['nullable', 'string'],
            'selling_points.*.title' => ['required', 'string', 'max:255'],
            'selling_points.*.description' => ['nullable', 'string', 'max:500'],
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
                    Unit::class,
                    $tenantId,
                    'slug',
                    $newSlug,
                    $currentSlug
                );
            }
        });

        $validator->after(function (Validator $validator) {
            $errors = $validator->errors();
            $missing = [];

            if ($errors->has('max_guests')) {
                $missing['max_guests'] = true;
            }
            if ($errors->has('bedroom_count')) {
                $missing['bedroom_count'] = true;
            }
            if ($errors->has('bathroom_count')) {
                $missing['bathroom_count'] = true;
            }

            if (empty($missing)) {
                return;
            }

            $message = '';
            $hasGuest = isset($missing['max_guests']);
            $hasBedroom = isset($missing['bedroom_count']);
            $hasBathroom = isset($missing['bathroom_count']);

            if ($hasGuest && $hasBedroom && $hasBathroom) {
                $message = 'Please specify the max guests, total bedrooms, and total bathrooms to continue.';
            } elseif ($hasGuest && $hasBedroom) {
                $message = 'Please specify the max guests and total bedrooms to continue.';
            } elseif ($hasGuest && $hasBathroom) {
                $message = 'Please specify the max guests and total bathrooms to continue.';
            } elseif ($hasBedroom && $hasBathroom) {
                $message = 'Please specify the total number of bedrooms and bathrooms to continue.';
            } elseif ($hasGuest) {
                $message = 'Please specify the maximum number of guests to continue.';
            } elseif ($hasBedroom) {
                $message = 'Please specify the total number of bedrooms to continue.';
            } elseif ($hasBathroom) {
                $message = 'Please specify the total number of bathrooms to continue.';
            }

            if ($message) {
                $errors->add('capacity', $message);
                $errors->forget('max_guests');
                $errors->forget('bedroom_count');
                $errors->forget('bathroom_count');
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
            'slug.regex'         => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., unit-name)',

            'subtitle.string'   => 'The unit subtitle must be valid text',

            'view.required' => 'Please specify the unit view',
            'view.string' => 'The view must be valid text',
        ];
    }
}
