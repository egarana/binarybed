<?php

namespace App\Http\Requests;

use App\Models\Unit;
use App\ValidatesTenantResourceUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreUnitRequest extends FormRequest
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
            'subtitle'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],

            // Unit Capacity
            'max_guests'     => ['required', 'integer', 'min:1', 'max:50'],
            'bedroom_count'  => ['required', 'integer', 'min:0', 'max:20'],
            'bathroom_count' => ['required', 'integer', 'min:0', 'max:20'],
            'view'           => ['required', 'string', 'max:255'],


            // Support for immediate upload (new way)
            'uploaded_media_ids'   => ['nullable', 'array', 'max:25'],
            'uploaded_media_ids.*' => ['required', 'integer', 'exists:temporary_uploads,id'],
            // Legacy support for direct file upload
            'images'     => ['nullable', 'array', 'max:10'],
            'images.*'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            // Standard Rate fields
            'standard_rate_price'      => ['required', 'numeric', 'min:0'],
            'standard_rate_currency'   => ['required', 'string', 'size:3'],
            'standard_rate_price_type' => ['nullable', 'string', 'max:50'],

            // Selling Points
            'selling_points' => ['nullable', 'array'],
            'selling_points.*.icon' => ['nullable', 'string'],
            'selling_points.*.title' => ['required', 'string', 'max:255'],
            'selling_points.*.description' => ['nullable', 'string', 'max:500'],

            // Book Direct Benefits
            'book_direct_benefits' => ['nullable', 'array'],
            'book_direct_benefits.*.icon' => ['nullable', 'string'],
            'book_direct_benefits.*.title' => ['required', 'string', 'max:255'],
            'book_direct_benefits.*.description' => ['nullable', 'string', 'max:500'],

            // Location
            'location' => ['nullable', 'array'],
            'location.address' => ['required_with:location', 'string', 'max:255'],
            'location.subtitle' => ['nullable', 'string', 'max:255'],
            'location.map_url' => ['nullable', 'url', 'max:500'],
            'location.highlights' => ['nullable', 'array', 'max:10'],
            'location.highlights.*' => ['required', 'string', 'max:100'],

            // Rules (House Rules)
            'rules' => ['nullable', 'array'],
            'rules.*.icon' => ['nullable', 'string'],
            'rules.*.label' => ['required', 'string', 'max:255'],

            // Host Information
            'host' => ['nullable', 'array'],
            'host.name' => ['required_with:host', 'string', 'max:255'],
            'host.photo' => ['nullable', 'url', 'max:500'],
            'host.languages' => ['nullable', 'array', 'max:5'],
            'host.languages.*' => ['string', 'max:50'],
            'host.story' => ['nullable', 'string', 'max:1000'],
            'host.whatsapp' => ['nullable', 'string', 'max:20'],
            'host.instagram' => ['nullable', 'string', 'max:255'],
            'host.facebook' => ['nullable', 'string', 'max:255'],
            'host.tiktok' => ['nullable', 'string', 'max:255'],
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
                    $slug
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
            'tenant_id.required' => 'Please select a tenant',
            'tenant_id.string'   => 'Invalid tenant selection',
            'tenant_id.exists'   => 'The selected tenant does not exist',

            'name.required'      => 'Please enter the unit name',
            'name.string'        => 'The unit name must be valid text',
            'name.min'           => 'The unit name must be at least 8 characters',
            'name.max'           => 'The unit name cannot be longer than 255 characters',

            'slug.required'      => 'Please enter the slug',
            'slug.string'        => 'The slug must be valid text',
            'slug.min'           => 'The slug must be at least 3 characters',
            'slug.max'           => 'The slug cannot be longer than 255 characters',
            'slug.regex'         => 'The slug must only contain lowercase letters, numbers, and hyphens (e.g., unit-name)',

            'subtitle.string'   => 'The unit subtitle must be valid text',

            'view.required' => 'Please specify the unit view',
            'view.string'   => 'The view must be valid text',

            // Location
            'location.address.required_with' => 'Please provide an address when adding location information',
            'location.address.string' => 'The location address must be valid text',
            'location.address.max' => 'The location address cannot be longer than 255 characters',
            'location.subtitle.string' => 'The location subtitle must be valid text',
            'location.subtitle.max' => 'The location subtitle cannot be longer than 255 characters',
            'location.map_url.url' => 'Please enter a valid URL for the map link',
            'location.map_url.max' => 'The map URL cannot be longer than 500 characters',
            'location.highlights.array' => 'Location highlights must be a list',
            'location.highlights.max' => 'You can add a maximum of 10 location highlights',
            'location.highlights.*.required' => 'Please fill in the highlight or remove it',
            'location.highlights.*.string' => 'Each highlight must be valid text',
            'location.highlights.*.max' => 'Each highlight cannot be longer than 100 characters',
        ];
    }
}
