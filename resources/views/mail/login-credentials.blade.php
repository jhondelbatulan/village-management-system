<x-mail::message>
# Your Login Credentials

Hello {{ $user->full_name }},

A new password has been generated for your account as requested. You can sign in using the credentials below:

**Username:** {{ $user->username }}

**Password:** {{ $plainPassword }}

<x-mail::button :url="route('login')">
Open Login Page
</x-mail::button>

For your security, please keep this email private. If you did not request this change, contact your administrator immediately.

Thanks,<br>
{{ config('app_settings.name', config('app.name', 'Multi-Village Management System')) }}
</x-mail::message>
