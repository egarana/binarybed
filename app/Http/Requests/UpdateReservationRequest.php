<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use App\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name'          => ['required', 'string', 'min:3', 'max:255'],
            'guest_email'         => ['required', 'email', 'max:255'],
            'guest_phone'         => ['required', new PhoneNumberRule()],
            'status'              => ['required', 'string', 'in:' . implode(',', Reservation::STATUSES)],
            'notes'               => ['nullable', 'string', 'max:5000'],
            'cancellation_reason' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required' => 'Please enter the guest name',
            'guest_name.string'   => 'The guest name must be valid text',
            'guest_name.min'      => 'The guest name must be at least 3 characters',
            'guest_name.max'      => 'The guest name cannot be longer than 255 characters',

            'guest_email.required' => 'Please enter the guest email',
            'guest_email.email'   => 'Please enter a valid email address',

            'guest_phone.required' => 'Please enter the guest phone number',

            'status.required'     => 'Please select a status',
            'status.in'           => 'Invalid reservation status',

            'notes.max'               => 'Notes cannot be longer than 5000 characters',
            'cancellation_reason.max' => 'Cancellation reason cannot be longer than 5000 characters',
        ];
    }

    /**
     * Get the validated data from the request.
     * Decode guest_phone from JSON string to array.
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        // If getting all validated data (no specific key)
        if ($key === null && isset($validated['guest_phone']) && is_string($validated['guest_phone'])) {
            $validated['guest_phone'] = json_decode($validated['guest_phone'], true);
        }

        return $validated;
    }
}
