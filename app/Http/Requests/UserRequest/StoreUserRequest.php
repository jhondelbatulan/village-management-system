<?php

namespace App\Http\Requests\UserRequest;

use App\Models\Role;
use App\Models\User;
use App\Support\TextNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Suffixes that may be stored on a user account.
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
            $middleName = $this->collapseSpaces($middleName);

            if (preg_match('/^\p{L}$/u', $middleName)) {
                $middleName = mb_strtoupper($middleName, 'UTF-8');
            }

            $merge['middle_name'] = $middleName;
        }

        $suffix = $this->input('suffix');

        if (is_string($suffix)) {
            $merge['suffix'] = $this->normalizeSuffix($suffix);
        }

        $email = $this->input('email');

        if (is_string($email)) {
            $merge['email'] = trim($email) === '' ? null : trim($email);
        }

        $contactNumber = $this->input('contact_number');

        if (is_string($contactNumber)) {
            $merge['contact_number'] = trim($contactNumber) === '' ? null : trim($contactNumber);
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'exists:roles,id'],
            'village_id' => [
                'required_if:role_id,2,3,4,5',
                'nullable',
                'exists:villages,id',
            ],
            'household_id' => [
                'required_if:role_id,5',
                'nullable',
                'exists:households,id',
            ],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'first_name' => ['nullable', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'middle_name' => ['nullable', 'string', 'regex:/^\p{Lu}$/u'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'suffix' => ['nullable', 'string', 'max:10', Rule::in(self::SUFFIXES)],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = Role::find($this->input('role_id'));

            if (! $role || ! in_array($role->role_slug, ['admin', 'secretary', 'treasurer'], true)) {
                return;
            }

            $villageId = $this->input('village_id');

            // An admin creating staff accounts is always locked to their own village.
            if ($this->user()?->role?->role_slug === 'admin') {
                $villageId = $this->user()->village_id;
            }

            if (! $villageId) {
                return;
            }

            $exists = User::query()
                ->where('role_id', $role->id)
                ->where('village_id', $villageId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('role_id', "A {$role->role_name} account already exists for this village.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Please select a role for this user.',
            'role_id.exists' => 'The selected role is invalid.',
            'village_id.required_if' => 'A village must be assigned for this role.',
            'village_id.exists' => 'The selected village is invalid.',
            'household_id.required_if' => 'A household must be assigned for resident users.',
            'household_id.exists' => 'The selected household is invalid.',
            'username.unique' => 'This username is already taken.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'first_name.regex' => 'First name must only contain letters — no numbers or special characters.',
            'last_name.regex' => 'Last name must only contain letters — no numbers or special characters.',
            'middle_name.regex' => 'Middle name must be a single capital letter (e.g., M).',
            'suffix.in' => 'Suffix must only be one of: ' . implode(', ', self::SUFFIXES) . '.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'profile_picture.image' => 'Profile picture must be an image file.',
            'profile_picture.max' => 'Profile picture must not exceed 2MB.',
        ];
    }

    private function normalizeSuffix(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $key = strtolower(rtrim($value, '.'));

        return match ($key) {
            'jr' => 'Jr.',
            'sr' => 'Sr.',
            'ii' => 'II',
            'iii' => 'III',
            'iv' => 'IV',
            'v' => 'V',
            'vi' => 'VI',
            'vii' => 'VII',
            'viii' => 'VIII',
            'ix' => 'IX',
            'x' => 'X',
            default => $value,
        };
    }

    private function collapseSpaces(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
