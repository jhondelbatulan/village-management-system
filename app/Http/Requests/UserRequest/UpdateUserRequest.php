<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->user()?->id;

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
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $userId],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email,' . $userId],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Please select a role for this user.',
            'village_id.required_if' => 'A village must be assigned for this role.',
            'household_id.required_if' => 'A household must be assigned for resident users.',
            'household_id.exists' => 'The selected household is invalid.',
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'profile_picture.image' => 'Profile picture must be an image file.',
            'profile_picture.max' => 'Profile picture must not exceed 2MB.',
        ];
    }
}
