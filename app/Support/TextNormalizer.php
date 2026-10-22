<?php

namespace App\Support;

class TextNormalizer
{
    /**
     * Trim the value, collapse repeated whitespace and capitalize the first letter of every word.
     *
     * A value typed entirely in upper or lower case is fully normalized
     * ("JOHN MCDONALD" / "john mcdonald" -> "John Mcdonald") while a mixed-case
     * value keeps the rest of its casing ("John McDonald").
     */
    public static function titleCase(string $value): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));

        if ($value === '') {
            return '';
        }

        $isUpper = $value === mb_strtoupper($value, 'UTF-8');
        $isLower = $value === mb_strtolower($value, 'UTF-8');

        if ($isUpper || $isLower) {
            $value = mb_strtolower($value, 'UTF-8');
        }

        $words = array_map(function (string $word): string {
            return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
                . mb_substr($word, 1, null, 'UTF-8');
        }, explode(' ', $value));

        return implode(' ', $words);
    }
}
