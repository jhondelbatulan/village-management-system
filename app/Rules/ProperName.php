<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProperName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        if (! preg_match(
            '/^(?=[\p{L}\s.\'-]+$)(?!.*(.)\1{2,})[\p{Lu}][\p{Ll}]*((?:[ .\'-][\p{Lu}][\p{Ll}]*))*$/u',
            $value
        )) {
            $fail('The :attribute must only contain letters (no numbers or special characters), each word must start with a capital letter (no ALL CAPS or mixed case like "JoHn"), with no double spaces and no letter repeated three or more times in a row.');
        }
    }
}
