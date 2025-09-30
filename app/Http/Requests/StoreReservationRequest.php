<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
            'status'                    => ['required', 'array'],
            'payment_status'            => ['required', 'array'],
            'source'                    => ['required', 'array'],
            'unit'                      => ['required', 'array'],
            'check_in'                  => ['required', 'date', 'after_or_equal:today'],
            'check_out'                 => ['required', 'date', 'after:check_in'],
            'qty'                       => ['required', 'integer', 'min:1'],
            'rate'                      => ['required', 'array'],
            'total_price'               => ['required', 'numeric', 'min:0'],
            'currency'                  => ['required', 'string', 'size:3'],
            'guests'                    => ['required', 'integer', 'min:1'],
            'first_name'                => ['required', 'string', 'max:255'],
            'last_name'                 => ['required', 'string', 'max:255'],
            'email'                     => ['required', 'email', 'max:255'],
            'phone'                     => ['required', 'array'],
            'phone.country'             => ['required', 'array'],
            'phone.country.country'     => ['required', 'string'], // country code like "ID"
            'phone.country.countryName' => ['required', 'string'], // country code like "ID"
            'phone.country.code'        => ['required', 'string'],            // e.g. "+62"
            'phone.number'              => ['required', 'max:15'],
            'notes'                     => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // Unit
            'unit.required'                      => 'Please select a unit for your reservation.',
            'unit.array'                         => 'The unit data must be provided in a valid format.',

            // Rate
            'rate.required'                      => 'Please select a rate for your reservation.',
            'rate.array'                         => 'The rate data must be provided in a valid format.',

            // First name
            'first_name.required'                => 'Please enter your first name.',
            'first_name.string'                  => 'The first name must be a valid text.',
            'first_name.max'                     => 'Your first name may not be longer than :max characters.',

            // Last name
            'last_name.required'                 => 'Please enter your last name.',
            'last_name.string'                   => 'The last name must be a valid text.',
            'last_name.max'                      => 'Your last name may not be longer than :max characters.',

            // Email
            'email.required'                     => 'Please provide your email address.',
            'email.email'                        => 'Please enter a valid email address.',
            'email.max'                          => 'Your email address may not be longer than :max characters.',

            // Phone
            'phone.required'                     => 'Please provide your phone details.',
            'phone.array'                        => 'The phone details must be provided in a valid format.',
            'phone.country.required'             => 'Please select your phone country.',
            'phone.country.array'                => 'The phone country details must be provided in a valid format.',
            'phone.country.country.required'     => 'The country code is required (e.g. "ID").',
            'phone.country.country.string'       => 'The country code must be a valid string.',
            'phone.country.countryName.required' => 'The country name is required.',
            'phone.country.countryName.string'   => 'The country name must be a valid string.',
            'phone.country.code.required'        => 'The dialing code is required (e.g. "+62").',
            'phone.country.code.string'          => 'The dialing code must be a valid string.',
            'phone.number.required'              => 'Please enter your phone number.',
            'phone.number.max'                   => 'Your phone number may not be longer than :max digits.',

            // Dates
            'check_in.required'                  => 'Please select a check-in date.',
            'check_in.date'                      => 'The check-in date must be a valid date.',
            'check_in.after_or_equal'            => 'The check-in date cannot be in the past.',

            'check_out.required'                 => 'Please select a check-out date.',
            'check_out.date'                     => 'The check-out date must be a valid date.',
            'check_out.after'                    => 'The check-out date must be after the check-in date.',

            // Guests
            'guests.required'                    => 'Please specify the number of guests.',
            'guests.integer'                     => 'The guests field must be a valid number.',
            'guests.min'                         => 'There must be at least :min guest.',

            // Quantity
            'qty.required'                       => 'Please specify the number of units.',
            'qty.integer'                        => 'The quantity must be a valid number.',
            'qty.min'                            => 'You must book at least :min unit.',

            // Total Price
            'total_price.required'               => 'The total price is required.',
            'total_price.numeric'                => 'The total price must be a valid number.',
            'total_price.min'                    => 'The total price cannot be negative.',

            // Currency
            'currency.required'                  => 'The currency is required.',
            'currency.string'                    => 'The currency must be a valid string.',
            'currency.size'                      => 'The currency must be a valid ISO 4217 code (3 letters).',

            // Status
            'status.required'                    => 'Please provide a reservation status.',
            'status.in'                          => 'The selected reservation status is invalid.',

            // Payment Status
            'payment_status.required'            => 'Please provide a payment status.',
            'payment_status.in'                  => 'The selected payment status is invalid.',

            // Notes
            'notes.string'                       => 'The notes must be a valid text.',

            // Source
            'source.string'                      => 'The source must be a valid text.',
            'source.max'                         => 'The source may not be longer than :max characters.',

            // Booked on
            'booked_on.date'                     => 'The booking date must be a valid date.',
        ];
    }
}
