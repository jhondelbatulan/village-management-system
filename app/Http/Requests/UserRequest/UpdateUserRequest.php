<?php

namespace App\Http\Requests\UserRequest;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

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
        $targetUser = $this->route('user');
        $userId = $targetUser?->id ?? $this->user()?->id;

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
                Rule::exists('households', 'id')->where(fn ($query) => $query
                    ->where('approval_status', 'approved')
                    ->orWhere('id', $targetUser?->household_id ?? 0)),
            ],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $userId],
            'email' => ['nullable', 'email:rfc', 'max:255', 'unique:users,email,' . $userId],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive,suspended'],
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

            // An admin managing staff accounts is always locked to their own village.
            if ($this->user()?->role?->role_slug === 'admin') {
                $villageId = $this->user()->village_id;
            }

            if (! $villageId) {
                return;
            }

            $user = $this->route('user');

            $exists = User::query()
                ->where('role_id', $role->id)
                ->where('village_id', $villageId)
                ->when($user, fn ($query) => $query->where('id', '!=', $user->id))
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
            'village_id.required_if' => 'A village must be assigned for this role.',
            'household_id.required_if' => 'A household must be assigned for resident users.',
            'household_id.exists' => 'The selected household is invalid.',
            'username.unique' => 'This username is already taken.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'profile_picture.image' => 'Profile picture must be an image file.',
            'profile_picture.max' => 'Profile picture must not exceed 2MB.',
        ];
    }
}
