<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidDomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Validasi panjang domain (max 253 karakter untuk FQDN)
        if (strlen($value) > 253) {
            $fail('The domain name is too long (maximum 253 characters).');
            return;
        }

        // Validasi format domain dengan regex yang lebih ketat
        $pattern = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i';
        
        if (!preg_match($pattern, $value)) {
            $fail('The domain format is invalid.');
            return;
        }

        // Validasi setiap label domain (bagian yang dipisahkan oleh titik)
        $labels = explode('.', $value);
        
        foreach ($labels as $label) {
            // Setiap label maksimal 63 karakter
            if (strlen($label) > 63) {
                $fail('Each part of the domain must not exceed 63 characters.');
                return;
            }
            
            // Label tidak boleh dimulai atau diakhiri dengan dash
            if (str_starts_with($label, '-') || str_ends_with($label, '-')) {
                $fail('Domain labels cannot start or end with a hyphen.');
                return;
            }
        }

        // Validasi TLD (Top Level Domain) minimal 2 karakter
        $tld = end($labels);
        if (strlen($tld) < 2) {
            $fail('The domain must have a valid top-level domain (e.g., .com, .id).');
            return;
        }

        // Opsional: Cek apakah domain benar-benar ada (DNS check)
        // if (!checkdnsrr($value, 'A') && !checkdnsrr($value, 'AAAA')) {
        //     $fail('The domain does not exist or cannot be resolved.');
        // }
    }
}