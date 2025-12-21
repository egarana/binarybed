<?php

namespace App\Http\Requests;

use App\Models\ReservationItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationItemRequest extends FormRequest
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
            // Resource selection
            'reservable_type' => ['required', 'string', Rule::in(['App\\Models\\Unit', 'App\\Models\\Activity'])],
            'reservable_id' => ['required', 'integer'],
            'rate_id' => ['required', 'integer'],

            // Dates
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],

            // Duration
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],

            // Quantity
            'quantity' => ['required', 'integer', 'min:1'],

            // Pricing (can be overridden by admin)
            'pricing_type' => ['nullable', 'string', Rule::in([
                ReservationItem::PRICING_PER_NIGHT,
                ReservationItem::PRICING_PER_PERSON,
                ReservationItem::PRICING_PER_HOUR,
                ReservationItem::PRICING_FLAT,
            ])],
            'rate_price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],

            // Optional overrides for snapshot data
            'resource_name' => ['nullable', 'string', 'max:255'],
            'rate_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reservable_type.required' => 'Please select a resource type.',
            'reservable_type.in' => 'Invalid resource type selected.',
            'reservable_id.required' => 'Please select a resource.',
            'rate_id.required' => 'Please select a rate.',
            'rate_price.required' => 'Price is required.',
            'rate_price.min' => 'Price must be at least 0.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'end_date.after_or_equal' => 'End date must be on or after start date.',
        ];
    }
}
