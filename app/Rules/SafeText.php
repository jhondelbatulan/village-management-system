<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeText implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $pattern = '/^[\p{L}\p{N}][\p{L}\p{N}\s.,\'-()\/&+]*$/u';

        if (! preg_match($pattern, $value) || preg_match('/\s{2,}/u', $value) || preg_match('/(.)\1{2,}/u', $value)) {
            $fail('The :attribute contains invalid characters. Only letters, numbers, spaces and common punctuation (period, comma, hyphen, apostrophe, parentheses, slash, ampersand) are allowed, with no double spaces and no repeated characters.');
        }
    }
}
