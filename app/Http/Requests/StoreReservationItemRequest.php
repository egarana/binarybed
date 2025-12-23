<?php

namespace App\Http\Requests;

use App\Models\ReservationItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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

            // Dates - validated in withValidator for unified error
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],

            // Times - validated in withValidator for unified error
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],

            // Duration
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'duration_minutes' => ['nullable', 'integer', 'min:30'],

            // Resource/Rate descriptions
            'resource_description' => ['nullable', 'string', 'max:1000'],
            'rate_description' => ['nullable', 'string', 'max:1000'],

            // Quantity
            'quantity' => ['required', 'integer', 'min:1'],

            // Pricing type - display only, not used in calculations
            'price_type' => ['nullable', 'string', 'max:50'],
            'rate_price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],

            // Optional overrides for snapshot data
            'resource_name' => ['nullable', 'string', 'max:255'],
            'resource_type_label' => ['nullable', 'string', 'max:50'],
            'rate_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Unified date range validation
            $this->validateDateRange($validator);

            // Unified time range validation
            $this->validateTimeRange($validator);
        });
    }

    /**
     * Validate date range with unified error on 'schedule' field.
     */
    protected function validateDateRange($validator): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        // Both dates required
        if (!$startDate || !$endDate) {
            $validator->errors()->add('schedule', 'Please select both start and end dates.');
            return;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $today = Carbon::today();

        // Start date cannot be in the past
        if ($start->lt($today)) {
            $validator->errors()->add('schedule', 'Start date cannot be in the past.');
            return;
        }

        // End date must be >= start date
        if ($end->lt($start)) {
            $validator->errors()->add('schedule', 'End date must be on or after start date.');
            return;
        }

        // Minimum duration based on type
        // For Activity: same day is valid (1 day)
        // For Unit: need at least 1 night (end > start)
        $days = $start->diffInDays($end);
        $reservableType = $this->input('reservable_type');

        if ($reservableType === 'App\\Models\\Unit' && $days < 1) {
            $validator->errors()->add('schedule', 'Minimum booking duration is 1 night.');
        }
        // Activity allows same-day booking (days = 0 is valid)
    }

    /**
     * Validate time range with unified error on 'schedule' field.
     * Only validates for Activity type - Unit type skips time validation.
     */
    protected function validateTimeRange($validator): void
    {
        $reservableType = $this->input('reservable_type');

        // Skip time validation for Unit type (time is disabled for Unit)
        if ($reservableType === 'App\\Models\\Unit') {
            return;
        }

        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');

        $hasStart = !empty($startTime);
        $hasEnd = !empty($endTime);

        // For Activity: all combinations are allowed
        // - Both flexible (null) = OK
        // - One specific + one flexible = OK (partial flexibility)
        // - Both specific = validate time range
        if ($reservableType === 'App\\Models\\Activity') {
            // Only validate when BOTH times are provided
            if (!$hasStart || !$hasEnd) {
                return; // Any flexible combination is allowed
            }
        } else {
            // For other types: both or none
            if ($hasStart !== $hasEnd) {
                $validator->errors()->add('schedule', 'Please select both start and end times, or leave both empty.');
                return;
            }

            // If both empty, no validation needed
            if (!$hasStart && !$hasEnd) {
                return;
            }
        }

        // Both times are set - validate the range
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        $minutes = $start->diffInMinutes($end, false);

        // End time must be after start time (no overnight)
        if ($minutes <= 0) {
            $validator->errors()->add('schedule', 'End time must be after start time.');
            return;
        }

        // Minimum 30 minutes duration
        if ($minutes < 30) {
            $validator->errors()->add('schedule', 'Time duration must be at least 30 minutes.');
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Resource/Rate selection
            'reservable_type.required' => 'Please select a product.',
            'reservable_type.in' => 'Invalid product type selected.',
            'reservable_id.required' => 'Please select a product.',
            'rate_id.required' => 'Please select a rate.',

            // Pricing
            'rate_price.required' => 'Price is required.',
            'rate_price.min' => 'Price must be at least 0.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
