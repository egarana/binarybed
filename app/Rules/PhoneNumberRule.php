<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow null/empty values (use 'required' rule separately if needed)
        if (empty($value)) {
            return;
        }

        // Must be valid JSON
        $data = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        // Check required structure
        if (!isset($data['country']) || !is_array($data['country'])) {
            $fail('The :attribute must include country information.');
            return;
        }

        if (!isset($data['number'])) {
            $fail('The :attribute must include a phone number.');
            return;
        }

        // Validate country structure
        $country = $data['country'];

        if (empty($country['country'])) {
            $fail('The :attribute must include a country code.');
            return;
        }

        if (empty($country['countryName'])) {
            $fail('The :attribute must include a country name.');
            return;
        }

        if (empty($country['code'])) {
            $fail('The :attribute must include a dial code.');
            return;
        }

        // Validate country code format (ISO2, 2 uppercase letters)
        if (!preg_match('/^[A-Z]{2}$/', $country['country'])) {
            $fail('The :attribute has an invalid country code format.');
            return;
        }

        // Validate dial code format (must start with +)
        if (!preg_match('/^\+\d{1,4}$/', $country['code'])) {
            $fail('The :attribute has an invalid dial code format.');
            return;
        }

        // Validate phone number (numeric only, 8-15 digits)
        $number = $data['number'];

        if (!preg_match('/^\d{8,15}$/', $number)) {
            $fail('The :attribute must be between 8 and 15 digits.');
            return;
        }
    }
}
