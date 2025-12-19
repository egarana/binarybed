<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'string', 'exists:tenants,id'],
            'guest_name'  => ['required', 'string', 'min:3', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:50'],
            'status'      => ['nullable', 'string', 'in:' . implode(',', Reservation::STATUSES)],
            'notes'       => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required'  => 'Please select a tenant',
            'tenant_id.string'    => 'Invalid tenant selection',
            'tenant_id.exists'    => 'The selected tenant does not exist',

            'guest_name.required' => 'Please enter the guest name',
            'guest_name.string'   => 'The guest name must be valid text',
            'guest_name.min'      => 'The guest name must be at least 3 characters',
            'guest_name.max'      => 'The guest name cannot be longer than 255 characters',

            'guest_email.email'   => 'Please enter a valid email address',
            'guest_phone.max'     => 'Phone number is too long',

            'status.in'           => 'Invalid reservation status',
            'notes.max'           => 'Notes cannot be longer than 5000 characters',
        ];
    }
}
