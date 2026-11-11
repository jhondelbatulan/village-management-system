<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\TextNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountSetupRequest extends FormRequest
{
    /**
     * Same suffixes accepted when the account was created.
     */
    public const SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['first_name', 'last_name'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $merge[$field] = TextNormalizer::titleCase($value);
            }
        }

        $middleName = $this->input('middle_name');

        if (is_string($middleName)) {
            $middleName = trim((string) preg_replace('/\s+/u', ' ', $middleName));

            if (preg_match('/^\p{L}$/u', $middleName)) {
                $middleName = mb_strtoupper($middleName, 'UTF-8');
            }

            $merge['middle_name'] = $middleName === '' ? null : $middleName;
        }

        $suffix = $this->input('suffix');

        if (is_string($suffix)) {
            $merge['suffix'] = trim($suffix) === '' ? null : trim($suffix);
        }

        foreach (['email', 'contact_number'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $merge[$field] = trim($value) === '' ? null : trim($value);
            }
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->user();

        return [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'middle_name' => ['nullable', 'string', 'regex:/^\p{Lu}$/u'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'suffix' => ['nullable', 'string', 'max:10', Rule::in(self::SUFFIXES)],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]{7,20}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name must only contain letters — no numbers or special characters.',
            'middle_name.regex' => 'Middle name must be a single capital letter (e.g., M).',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'Last name must only contain letters — no numbers or special characters.',
            'suffix.in' => 'Suffix must only be one of: ' . implode(', ', self::SUFFIXES) . '.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Contact number must contain digits only (7–20 digits).',
            'password.required' => 'Please set your new password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
